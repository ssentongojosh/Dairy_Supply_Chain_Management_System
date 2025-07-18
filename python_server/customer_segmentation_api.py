from flask import Flask, request, jsonify
import pandas as pd
import pickle
from sklearn.cluster import KMeans
from sklearn.preprocessing import StandardScaler, LabelEncoder
import os
import numpy as np

app = Flask(__name__)

# ============ Load Data & Train Model On Start =============

DATA_PATH = "../database/seeders/Dataset/customer_segmentation_data.csv"  # or use full path if needed
df = pd.read_csv(DATA_PATH)

# Encode gender (assuming it's needed)
if df['Gender'].dtype == object:
    label_encoder = LabelEncoder()
    df['Gender'] = label_encoder.fit_transform(df['Gender'])

feature_cols = ['Age', 'Gender', 'Annual Income', 'Spending Score']
features = df[feature_cols]
scaler = StandardScaler()
scaled_features = scaler.fit_transform(features)

kmeans = KMeans(n_clusters=5, random_state=42)
kmeans.fit(scaled_features)

# ============ Load Top 3 Products per Segment =============
TOP3_PATH = os.path.join(os.path.dirname(__file__), '../python_server/database/pythonfiles/graphs/segment_top3_products.csv')
if not os.path.exists(TOP3_PATH):
    # Try absolute path fallback
    TOP3_PATH = 'storage/app/public/segment_top3_products.csv'
top3_df = pd.read_csv(TOP3_PATH)
segment_to_products = {
    row['segment']: [row['top1'], row['top2'], row['top3']]
    for _, row in top3_df.iterrows()
}

# ====== API endpoint to segment a new customer ======
@app.route("/api/segment", methods=["POST"])
def get_segment():
    """
    Expects JSON with: {"age": 25, "gender": "Male", "income": 50000, "score": 67}
    Returns: {"segment": "Middle Age Spenders"}
    """
    data = request.json
    gender = data["gender"]
    # Encode gender for prediction
    gender_num = label_encoder.transform([gender])[0]
    sample = pd.DataFrame([[
        data["age"], gender_num, data["income"], data["score"]
    ]], columns=feature_cols)
    sample_scaled = scaler.transform(sample)
    cluster = kmeans.predict(sample_scaled)[0]
    # You can use your cluster_labels mapping from your script
    cluster_labels = {
        0: 'Young Savers',
        1: 'Middle Age Spenders',
        2: 'Middle Age Savers',
        3: 'Middle Age Average Income Spenders',
        4: 'Older Spenders'
    }
    segment = cluster_labels.get(cluster, f"Cluster {cluster}")
    return jsonify({"segment": segment})

# ====== API endpoint for demand prediction ======
# app.py (or ml_api.py)
import joblib
from datetime import datetime, timedelta


# --- Load the ML components ---
# Define the directory where components are saved
# Define the directory where components are saved
from scipy.sparse import hstack, csr_matrix # IMPORTANT: Import hstack and csr_matrix
from xgboost import XGBRegressor # IMPORTANT: Import XGBRegressor (even though we load, good practice)






model_dir = 'ml_models'

# Define lags and rolling windows globally so they are accessible (NEW)
lags = [1, 7, 14, 30]
rolling_windows = [7, 30]

# Global variables for wholesaler-specific model
model_wholesaler, encoder_wholesaler, feature_columns_wholesaler, recent_history_cache_wholesaler = None, None, None, None

# Global variables for general product model
model_general, encoder_general, feature_columns_general, recent_history_cache_general = None, None, None, None

def load_ml_components():
    global model_wholesaler, encoder_wholesaler, feature_columns_wholesaler, recent_history_cache_wholesaler
    global model_general, encoder_general, feature_columns_general, recent_history_cache_general

    try:
        # Load Wholesaler-Specific Model Components
        model_wholesaler = joblib.load(os.path.join(model_dir, 'demand_forecaster_model_wholesaler.joblib'))
        encoder_wholesaler = joblib.load(os.path.join(model_dir, 'one_hot_encoder_wholesaler.joblib'))
        feature_columns_wholesaler = joblib.load(os.path.join(model_dir, 'feature_columns_wholesaler.joblib'))

        history_path_wholesaler = os.path.join(model_dir, 'recent_demand_history_wholesaler.csv')
        recent_history_cache_wholesaler = pd.read_csv(history_path_wholesaler, parse_dates=['invoice_date'])
        recent_history_cache_wholesaler['prod_wholesaler_date_key'] = recent_history_cache_wholesaler['Product'] + '_' + \
                                                         recent_history_cache_wholesaler['wholesaler_id'].astype(str) + '_' + \
                                                         recent_history_cache_wholesaler['invoice_date'].dt.strftime('%Y-%m-%d')
        print("Wholesaler-Specific ML components and recent history loaded successfully!")

        # Load General Product Model Components
        model_general = joblib.load(os.path.join(model_dir, 'demand_forecaster_model_general.joblib'))
        encoder_general = joblib.load(os.path.join(model_dir, 'one_hot_encoder_general.joblib'))
        feature_columns_general = joblib.load(os.path.join(model_dir, 'feature_columns_general.joblib'))

        history_path_general = os.path.join(model_dir, 'recent_demand_history_general.csv')
        recent_history_cache_general = pd.read_csv(history_path_general, parse_dates=['invoice_date'])
        recent_history_cache_general['prod_date_key'] = recent_history_cache_general['Product'] + '_' + \
                                                         recent_history_cache_general['invoice_date'].dt.strftime('%Y-%m-%d')
        print("General Product ML components and recent history loaded successfully!")

    except Exception as e:
        print(f"Error loading ML components: {e}")
        print("Ensure 'ml_models' directory and its contents are present.")
        model_wholesaler, encoder_wholesaler, feature_columns_wholesaler, recent_history_cache_wholesaler = None, None, None, None
        model_general, encoder_general, feature_columns_general, recent_history_cache_general = None, None, None, None

load_ml_components()

# --- Feature Generation for Wholesaler-Specific Forecasts ---
def generate_features_for_wholesaler_day(current_date: datetime,
                              product: str,
                              wholesaler_id: str,
                              current_history_df: pd.DataFrame,
                              all_feature_columns: list,
                              ohe_encoder
                              ) -> csr_matrix:
    feature_values_dict = {}

    feature_values_dict['year'] = current_date.year
    feature_values_dict['month'] = current_date.month
    feature_values_dict['day'] = current_date.day
    feature_values_dict['day_of_week'] = current_date.weekday()
    feature_values_dict['day_of_year'] = current_date.timetuple().tm_yday
    feature_values_dict['week_of_year'] = current_date.isocalendar()[1]
    feature_values_dict['quarter'] = (current_date.month - 1) // 3 + 1

    specific_history = current_history_df[
        (current_history_df['Product'] == product) &
        (current_history_df['wholesaler_id'] == wholesaler_id)
    ].sort_values(by='invoice_date')

    specific_history = specific_history[specific_history['invoice_date'] < current_date]

    # Use the globally defined lags and rolling_windows
    global lags, rolling_windows # Declare intent to use global variables

    if not specific_history.empty:
        for lag in lags:
            lag_date = current_date - timedelta(days=lag)
            lag_demand_row = specific_history[specific_history['invoice_date'] <= lag_date].iloc[-1:]
            if not lag_demand_row.empty:
                feature_values_dict[f'demand_lag_{lag}d'] = lag_demand_row['daily_demand'].values[0]
            else:
                feature_values_dict[f'demand_lag_{lag}d'] = 0

        for window in rolling_windows:
            window_start_date = current_date - timedelta(days=window)
            rolling_window_data = specific_history[
                (specific_history['invoice_date'] >= window_start_date) &
                (specific_history['invoice_date'] < current_date)
            ]

            if not rolling_window_data.empty:
                feature_values_dict[f'rolling_mean_{window}d'] = rolling_window_data['daily_demand'].mean()
                feature_values_dict[f'rolling_std_{window}d'] = rolling_window_data['daily_demand'].std()
                if np.isnan(feature_values_dict[f'rolling_std_{window}d']):
                    feature_values_dict[f'rolling_std_{window}d'] = 0.0
            else:
                feature_values_dict[f'rolling_mean_{window}d'] = 0.0
                feature_values_dict[f'rolling_std_{window}d'] = 0.0
    else:
        for lag in lags:
            feature_values_dict[f'demand_lag_{lag}d'] = 0
        for window in rolling_windows:
            feature_values_dict[f'rolling_mean_{window}d'] = 0.0
            feature_values_dict[f'rolling_std_{window}d'] = 0.0

    numerical_feature_names_from_training = [col for col in all_feature_columns if not (col.startswith('Product_') or col.startswith('wholesaler_id_'))]
    numerical_features_series = pd.Series(feature_values_dict).reindex(numerical_feature_names_from_training, fill_value=0.0)

    ohe_input_df = pd.DataFrame([[product, wholesaler_id]], columns=['Product', 'wholesaler_id'])
    encoded_ohe_features_sparse = ohe_encoder.transform(ohe_input_df)

    numerical_features_array = numerical_features_series.values.reshape(1, -1)

    final_features_sparse = hstack([numerical_features_array, encoded_ohe_features_sparse])

    return final_features_sparse.tocsr()

# --- Feature Generation for General Product Forecasts ---
def generate_features_for_general_day(current_date: datetime,
                                      product: str,
                                      current_history_df: pd.DataFrame,
                                      all_feature_columns: list,
                                      ohe_encoder
                                      ) -> csr_matrix:
    feature_values_dict = {}

    feature_values_dict['year'] = current_date.year
    feature_values_dict['month'] = current_date.month
    feature_values_dict['day'] = current_date.day
    feature_values_dict['day_of_week'] = current_date.weekday()
    feature_values_dict['day_of_year'] = current_date.timetuple().tm_yday
    feature_values_dict['week_of_year'] = current_date.isocalendar()[1]
    feature_values_dict['quarter'] = (current_date.month - 1) // 3 + 1

    specific_history = current_history_df[
        (current_history_df['Product'] == product)
    ].sort_values(by='invoice_date')

    specific_history = specific_history[specific_history['invoice_date'] < current_date]

    # Use the globally defined lags and rolling_windows (NEW)
    global lags, rolling_windows # Declare intent to use global variables

    if not specific_history.empty:
        for lag in lags:
            lag_date = current_date - timedelta(days=lag)
            lag_demand_row = specific_history[specific_history['invoice_date'] <= lag_date].iloc[-1:]
            if not lag_demand_row.empty:
                feature_values_dict[f'demand_lag_{lag}d'] = lag_demand_row['daily_demand'].values[0]
            else:
                feature_values_dict[f'demand_lag_{lag}d'] = 0

        for window in rolling_windows:
            window_start_date = current_date - timedelta(days=window)
            rolling_window_data = specific_history[
                (specific_history['invoice_date'] >= window_start_date) &
                (specific_history['invoice_date'] < current_date)
            ]

            if not rolling_window_data.empty:
                feature_values_dict[f'rolling_mean_{window}d'] = rolling_window_data['daily_demand'].mean()
                feature_values_dict[f'rolling_std_{window}d'] = rolling_window_data['daily_demand'].std()
                if np.isnan(feature_values_dict[f'rolling_std_{window}d']):
                    feature_values_dict[f'rolling_std_{window}d'] = 0.0
            else:
                feature_values_dict[f'rolling_mean_{window}d'] = 0.0
                feature_values_dict[f'rolling_std_{window}d'] = 0.0
    else:
        for lag in lags:
            feature_values_dict[f'demand_lag_{lag}d'] = 0
        for window in rolling_windows:
            feature_values_dict[f'rolling_mean_{window}d'] = 0.0
            feature_values_dict[f'rolling_std_{window}d'] = 0.0

    numerical_feature_names_from_training = [col for col in all_feature_columns if not (col.startswith('Product_'))]
    numerical_features_series = pd.Series(feature_values_dict).reindex(numerical_feature_names_from_training, fill_value=0.0)

    ohe_input_df = pd.DataFrame([[product]], columns=['Product'])
    encoded_ohe_features_sparse = ohe_encoder.transform(ohe_input_df)

    numerical_features_array = numerical_features_series.values.reshape(1, -1)

    final_features_sparse = hstack([numerical_features_array, encoded_ohe_features_sparse])

    return final_features_sparse.tocsr()


# --- API Endpoint for Wholesaler-Specific Forecasts ---
@app.route('/forecast_demand_range', methods=['POST'])
def forecast_demand_range():
    if model_wholesaler is None or encoder_wholesaler is None or feature_columns_wholesaler is None or recent_history_cache_wholesaler is None:
        return jsonify({"error": "Wholesaler-specific ML components not loaded. Check server logs."}), 500

    try:
        data = request.get_json()
        product_name = data.get('product')
        wholesaler_id = data.get('wholesaler_id')
        start_date_str = data.get('start_date')
        end_date_str = data.get('end_date')

        if not all([product_name, wholesaler_id, start_date_str, end_date_str]):
            return jsonify({"error": "Missing product, wholesaler_id, start_date, or end_date"}), 400

        try:
            start_date = datetime.strptime(start_date_str, '%d/%m/%Y')
            end_date = datetime.strptime(end_date_str, '%d/%m/%Y')
            if start_date > end_date:
                return jsonify({"error": "start_date cannot be after end_date"}), 400
        except ValueError:
            return jsonify({"error": "Invalid date format. Use DD/MM/YYYY for start_date and end_date"}), 400

        forecast_results = []
        current_history_for_prediction = recent_history_cache_wholesaler.copy()

        current_forecast_date = start_date
        while current_forecast_date <= end_date:
            features_sparse = generate_features_for_wholesaler_day(
                current_forecast_date,
                product_name,
                wholesaler_id,
                current_history_for_prediction,
                feature_columns_wholesaler,
                encoder_wholesaler
            )

            predicted_demand = model_wholesaler.predict(features_sparse)[0]
            predicted_demand = max(0, predicted_demand)

            forecast_results.append({
                "date": current_forecast_date.strftime('%Y-%m-%d'),
                "predicted_demand": float(round(predicted_demand, 2))
            })

            temp_history_entry = pd.DataFrame([{
                'invoice_date': current_forecast_date,
                'Product': product_name,
                'wholesaler_id': wholesaler_id,
                'daily_demand': predicted_demand,
                'prod_wholesaler_date_key': f"{product_name}_{wholesaler_id}_{current_forecast_date.strftime('%Y-%m-%d')}"
            }])
            current_history_for_prediction = pd.concat([current_history_for_prediction, temp_history_entry], ignore_index=True)
            current_history_for_prediction.sort_values(by='invoice_date', inplace=True)

            current_forecast_date += timedelta(days=1)

        return jsonify({
            "product": product_name,
            "wholesaler_id": wholesaler_id,
            "forecast_period": {
                "start_date": start_date_str,
                "end_date": end_date_str
            },
            "forecast_data": forecast_results,
            "note": "Wholesaler-specific forecast. Lags and rolling features are dynamically calculated using recent historical data and recursive forecasting. Model uses sparse input."
        })

    except Exception as e:
        app.logger.error(f"Error during wholesaler-specific forecasting range: {e}", exc_info=True)
        return jsonify({"error": f"An internal server error occurred for wholesaler forecast: {str(e)}"}), 500

# --- API Endpoint for General Product Forecasts ---
@app.route('/forecast_general_demand_range', methods=['POST'])
def forecast_general_demand_range():
    if model_general is None or encoder_general is None or feature_columns_general is None or recent_history_cache_general is None:
        return jsonify({"error": "General product ML components not loaded. Check server logs."}), 500

    try:
        data = request.get_json()
        product_name = data.get('product')
        start_date_str = data.get('start_date')
        end_date_str = data.get('end_date')

        if not all([product_name, start_date_str, end_date_str]):
            return jsonify({"error": "Missing product, start_date, or end_date for general forecast"}), 400

        try:
            start_date = datetime.strptime(start_date_str, '%d/%m/%Y')
            end_date = datetime.strptime(end_date_str, '%d/%m/%Y')
            if start_date > end_date:
                return jsonify({"error": "start_date cannot be after end_date"}), 400
        except ValueError:
            return jsonify({"error": "Invalid date format. Use DD/MM/YYYY for start_date and end_date"}), 400

        forecast_results = []
        current_history_for_prediction = recent_history_cache_general.copy()

        current_forecast_date = start_date
        while current_forecast_date <= end_date:
            features_sparse = generate_features_for_general_day(
                current_forecast_date,
                product_name,
                current_history_for_prediction,
                feature_columns_general,
                encoder_general
            )

            predicted_demand = model_general.predict(features_sparse)[0]
            predicted_demand = max(0, predicted_demand)

            forecast_results.append({
                "date": current_forecast_date.strftime('%Y-%m-%d'),
                "predicted_demand": float(round(predicted_demand, 2))
            })

            temp_history_entry = pd.DataFrame([{
                'invoice_date': current_forecast_date,
                'Product': product_name,
                'daily_demand': predicted_demand,
                'prod_date_key': f"{product_name}_{current_forecast_date.strftime('%Y-%m-%d')}"
            }])
            current_history_for_prediction = pd.concat([current_history_for_prediction, temp_history_entry], ignore_index=True)
            current_history_for_prediction.sort_values(by='invoice_date', inplace=True)

            current_forecast_date += timedelta(days=1)

        return jsonify({
            "product": product_name,
            "forecast_period": {
                "start_date": start_date_str,
                "end_date": end_date_str
            },
            "forecast_data": forecast_results,
            "note": "General product forecast. Lags and rolling features are dynamically calculated using recent historical data and recursive forecasting. Model uses sparse input."
        })

    except Exception as e:
        app.logger.error(f"Error during general forecasting range: {e}", exc_info=True)
        return jsonify({"error": f"An internal server error occurred for general forecast: {str(e)}"}), 500


# --- API Endpoint for Single Prediction (uses wholesaler model) ---
@app.route('/predict_demand', methods=['POST'])
def predict_single_demand():
    if model_wholesaler is None or encoder_wholesaler is None or feature_columns_wholesaler is None or recent_history_cache_wholesaler is None:
        return jsonify({"error": "Wholesaler-specific ML components not loaded for single prediction. Check server logs."}), 500

    try:
        data = request.get_json()
        product_name = data.get('product')
        wholesaler_id = data.get('wholesaler_id')
        prediction_date_str = data.get('prediction_date')

        if not all([product_name, wholesaler_id, prediction_date_str]):
            return jsonify({"error": "Missing product, wholesaler_id, or prediction_date"}), 400

        try:
            date_to_predict = datetime.strptime(prediction_date_str, '%d/%m/%Y')
        except ValueError:
            return jsonify({"error": "Invalid date format. Use DD/MM/YYYY"}), 400

        features_sparse = generate_features_for_wholesaler_day(
            date_to_predict,
            product_name,
            wholesaler_id,
            recent_history_cache_wholesaler.copy(),
            feature_columns_wholesaler,
            encoder_wholesaler
        )

        predicted_demand = model_wholesaler.predict(features_sparse)[0]
        predicted_demand = max(0, predicted_demand)

        return jsonify({
            "product": product_name,
            "wholesaler_id": wholesaler_id,
            "prediction_date": prediction_date_str,
            "predicted_demand": float(round(predicted_demand, 2)),
            "note": "Wholesaler-specific single prediction. Lags and rolling features are dynamically calculated using recent historical data. Model uses sparse input."
        })

    except Exception as e:
        app.logger.error(f"Error during wholesaler-specific single prediction: {e}", exc_info=True)
        return jsonify({"error": f"An internal server error occurred for single wholesaler prediction: {str(e)}"}), 500




# ====== Run the Flask app ======

if __name__ == "__main__":
    app.run(debug=True, port=5000)  # Runs at http://127.0.0.1:5000
