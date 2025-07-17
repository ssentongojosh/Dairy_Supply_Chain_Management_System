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

# Encode categorical variables
label_encoders = {}
categorical_columns = ['location', 'business_type']

for column in categorical_columns:
    le = LabelEncoder()
    df[column] = le.fit_transform(df[column])
    label_encoders[column] = le

# Business-focused features
feature_cols = ['annual_revenue', 'order_frequency', 'total_quantity_purchased', 'location', 'business_type']
features = df[feature_cols]
scaler = StandardScaler()
scaled_features = scaler.fit_transform(features)

kmeans = KMeans(n_clusters=5, random_state=42)
kmeans.fit(scaled_features)

# ============ Load Top 3 Products per Segment =============
TOP3_PATH = os.path.join(os.path.dirname(__file__), '../storage/app/public/business_segment_top3_products.csv')
if not os.path.exists(TOP3_PATH):
    # Try absolute path fallback
    TOP3_PATH = 'storage/app/public/business_segment_top3_products.csv'
top3_df = pd.read_csv(TOP3_PATH)
segment_to_products = {
    row['segment']: [row['top1'], row['top2'], row['top3']]
    for _, row in top3_df.iterrows()
}

# ====== API endpoint to segment a new customer ======
@app.route("/api/segment", methods=["POST"])
def get_segment():
    """
    Expects JSON with: {"annual_revenue": 500000, "order_frequency": 12, "total_quantity_purchased": 1000, "location": "New York", "business_type": "Restaurant"}
    Returns: {"segment": "Medium Business"}
    """
    data = request.json
    if not data:
        return jsonify({"error": "No data provided"}), 400

    # Encode categorical variables for prediction
    location = data.get("location")
    if location is None:
        return jsonify({"error": "Missing 'location' in request data"}), 400
    try:
        location_num = label_encoders['location'].transform([location])[0]
    except ValueError:
        return jsonify({"error": f"Unknown location: {location}. Allowed: {list(label_encoders['location'].classes_)}"}), 400

    try:
        business_type_num = label_encoders['business_type'].transform([data["business_type"]])[0]
    except ValueError:
        return jsonify({"error": f"Unknown business_type: {data['business_type']}. Allowed: {list(label_encoders['business_type'].classes_)}"}), 400

    sample = pd.DataFrame([[
        data["annual_revenue"],
        data["order_frequency"],
        data["total_quantity_purchased"],
        location_num,
        business_type_num
    ]], columns=feature_cols)

    sample_scaled = scaler.transform(sample)
    cluster = int(kmeans.predict(sample_scaled)[0])

    # Business segment labels
    cluster_labels = {
        0: 'Small Business',
        1: 'Medium Business',
        2: 'Large Business',
        3: 'High Frequency Business',
        4: 'Premium Business'
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
model_dir = 'ml_models'

# --- Global variables to store loaded components ---
model = None
encoder = None
feature_columns = None
recent_history_cache = None # New: For storing recent demand history

# --- Function to load all ML components and history ---
def load_ml_components():
    global model, encoder, feature_columns, recent_history_cache
    try:
        model = joblib.load(os.path.join(model_dir, 'demand_forecaster_model.joblib'))
        encoder = joblib.load(os.path.join(model_dir, 'one_hot_encoder.joblib'))
        feature_columns = joblib.load(os.path.join(model_dir, 'feature_columns.joblib'))

        # Load recent historical demand data
        history_path = os.path.join(model_dir, 'recent_demand_history.csv')
        recent_history_cache = pd.read_csv(history_path, parse_dates=['invoice_date'])
        # Add a combined key for easier lookup (Product_Mall_Date)
        recent_history_cache['prod_mall_date_key'] = recent_history_cache['Product'] + '_' + \
                                                    recent_history_cache['shopping_mall'] + '_' + \
                                                    recent_history_cache['invoice_date'].dt.strftime('%Y-%m-%d')
        print("ML components and recent history loaded successfully!")
    except Exception as e:
        print(f"Error loading ML components or recent history: {e}")
        print("Ensure 'ml_models' directory and its contents are present.")
        model, encoder, feature_columns, recent_history_cache = None, None, None, None

# Load components when the app starts
load_ml_components()

# --- Helper Function to Generate Features for a Single Day's Prediction ---
def generate_features_for_day(current_date: datetime,
                              product: str,
                              shopping_mall: str,
                              current_history_df: pd.DataFrame,
                              all_feature_columns: list,
                              ohe_encoder # <--- Corrected line!
                              ) -> pd.DataFrame:
    """
    Generates a single row DataFrame of features for prediction for a given date, product, and mall,
    using provided historical context.
    """
    # Create an empty Series with all expected feature columns, initialized to 0
    feature_series = pd.Series(0.0, index=all_feature_columns)

    # 1. Add Time-based features for current_date
    feature_series['year'] = current_date.year
    feature_series['month'] = current_date.month
    feature_series['day'] = current_date.day
    feature_series['day_of_week'] = current_date.weekday()
    feature_series['day_of_year'] = current_date.timetuple().tm_yday
    feature_series['week_of_year'] = current_date.isocalendar()[1]
    feature_series['quarter'] = (current_date.month - 1) // 3 + 1

    # 2. Add One-Hot Encoded Product and Shopping Mall
    ohe_input_df = pd.DataFrame([[product, shopping_mall]], columns=['Product', 'shopping_mall'])
    encoded_ohe_features = ohe_encoder.transform(ohe_input_df)
    ohe_feature_names = ohe_encoder.get_feature_names_out(['Product', 'shopping_mall'])

    for i, col_name in enumerate(ohe_feature_names):
        if col_name in feature_series.index:
            feature_series[col_name] = encoded_ohe_features[0, i]

    # 3. Calculate Lag and Rolling Features using current_history_df
    # Filter history for the specific product and mall
    specific_history = current_history_df[
        (current_history_df['Product'] == product) &
        (current_history_df['shopping_mall'] == shopping_mall)
    ].sort_values(by='invoice_date')

    # Ensure current_date is not in history (we are predicting for it)
    specific_history = specific_history[specific_history['invoice_date'] < current_date]

    if not specific_history.empty:
        # Calculate lags
        for lag in [1, 7, 14, 30]: # Ensure these match what was used in training
            lag_date = current_date - timedelta(days=lag)
            # Find the most recent demand on or before lag_date
            lag_demand_row = specific_history[specific_history['invoice_date'] <= lag_date].iloc[-1:]
            if not lag_demand_row.empty:
                feature_series[f'demand_lag_{lag}d'] = lag_demand_row['daily_demand'].values[0]
            else:
                feature_series[f'demand_lag_{lag}d'] = 0 # Default if no history exists

        # Calculate rolling means/stds
        for window in [7, 30]: # Ensure these match what was used in training
            # Get data for the rolling window ending the day before current_date
            window_start_date = current_date - timedelta(days=window)
            rolling_window_data = specific_history[
                (specific_history['invoice_date'] >= window_start_date) &
                (specific_history['invoice_date'] < current_date)
            ]

            if not rolling_window_data.empty:
                feature_series[f'rolling_mean_{window}d'] = rolling_window_data['daily_demand'].mean()
                feature_series[f'rolling_std_{window}d'] = rolling_window_data['daily_demand'].std()
                if np.isnan(feature_series[f'rolling_std_{window}d']): # Handle cases with single value in window
                    feature_series[f'rolling_std_{window}d'] = 0.0
            else:
                feature_series[f'rolling_mean_{window}d'] = 0.0
                feature_series[f'rolling_std_{window}d'] = 0.0

    return pd.DataFrame([feature_series])[all_feature_columns] # Return as DataFrame, ensuring column order

# --- New Endpoint for Multi-Step Forecasting ---
@app.route('/forecast_demand_range', methods=['POST'])
def forecast_demand_range():
    if model is None or encoder is None or feature_columns is None or recent_history_cache is None:
        return jsonify({"error": "ML components or recent history not loaded. Check server logs."}), 500

    try:
        data = request.get_json()

        product_name = data.get('product')
        shopping_mall_name = data.get('shopping_mall')
        start_date_str = data.get('start_date') # e.g., "01/01/2023"
        end_date_str = data.get('end_date')   # e.g., "07/01/2023"

        if not all([product_name, shopping_mall_name, start_date_str, end_date_str]):
            return jsonify({"error": "Missing product, shopping_mall, start_date, or end_date"}), 400

        try:
            start_date = datetime.strptime(start_date_str, '%d/%m/%Y')
            end_date = datetime.strptime(end_date_str, '%d/%m/%Y')
            if start_date > end_date:
                return jsonify({"error": "start_date cannot be after end_date"}), 400
        except ValueError:
            return jsonify({"error": "Invalid date format. Use DD/MM/YYYY for start_date and end_date"}), 400

        forecast_results = []
        # Create a mutable copy of the recent history for recursive updates
        # This is for a single request. If multiple requests come in parallel,
        # this might become a bottleneck. In production, consider a thread-safe approach or
        # passing deep copies. For now, this is fine.
        current_history_for_prediction = recent_history_cache.copy()

        current_forecast_date = start_date
        while current_forecast_date <= end_date:
            # Generate features for the current day using the updated history
            features_df = generate_features_for_day(
                current_forecast_date,
                product_name,
                shopping_mall_name,
                current_history_for_prediction,
                feature_columns, # Ensure it's a list for generate_features_for_day
                encoder
            )

            # Make prediction
            predicted_demand = model.predict(features_df)[0]
            predicted_demand = max(0, predicted_demand) # Ensure non-negative

            forecast_results.append({
                "date": current_forecast_date.strftime('%Y-%m-%d'),
                "predicted_demand": round(predicted_demand, 2)
            })

            # Append the current prediction to the history for the next iteration (recursive forecasting)
            # Create a temporary entry for the predicted day
            temp_history_entry = pd.DataFrame([{
                'invoice_date': current_forecast_date,
                'Product': product_name,
                'shopping_mall': shopping_mall_name,
                'daily_demand': predicted_demand,
                'prod_mall_date_key': f"{product_name}_{shopping_mall_name}_{current_forecast_date.strftime('%Y-%m-%d')}"
            }])
            current_history_for_prediction = pd.concat([current_history_for_prediction, temp_history_entry], ignore_index=True)
            current_history_for_prediction.sort_values(by='invoice_date', inplace=True) # Re-sort for correct lag calculation

            # Move to the next day
            current_forecast_date += timedelta(days=1)

        return jsonify({
            "product": product_name,
            "shopping_mall": shopping_mall_name,
            "forecast_period": {
                "start_date": start_date_str,
                "end_date": end_date_str
            },
            "forecast_data": forecast_results,
            "note": "Lags and rolling features are now dynamically calculated using recent historical data and recursive forecasting."
        })

    except Exception as e:
        app.logger.error(f"Error during forecasting range: {e}", exc_info=True)
        return jsonify({"error": f"An internal server error occurred: {str(e)}"}), 500

# Original /predict_demand endpoint (can be kept or removed if /forecast_demand_range covers it)
@app.route('/predict_demand', methods=['POST'])
def predict_single_demand():
    if model is None or encoder is None or feature_columns is None or recent_history_cache is None:
        return jsonify({"error": "ML components or recent history not loaded. Check server logs."}), 500

    try:
        data = request.get_json()
        product_name = data.get('product')
        shopping_mall_name = data.get('shopping_mall')
        prediction_date_str = data.get('prediction_date') # e.g., "01/01/2023"

        if not all([product_name, shopping_mall_name, prediction_date_str]):
            return jsonify({"error": "Missing product, shopping_mall, or prediction_date"}), 400

        try:
            date_to_predict = datetime.strptime(prediction_date_str, '%d/%m/%Y')
        except ValueError:
            return jsonify({"error": "Invalid date format. Use DD/MM/YYYY"}), 400

        # Generate features for prediction using the more robust function
        features_df = generate_features_for_day(
            date_to_predict,
            product_name,
            shopping_mall_name,
            recent_history_cache.copy(), # Pass a copy to avoid modifying global cache
            feature_columns.tolist(),
            encoder
        )

        prediction = model.predict(features_df)[0]
        prediction = max(0, prediction) # Ensure non-negative prediction

        return jsonify({
            "product": product_name,
            "shopping_mall": shopping_mall_name,
            "prediction_date": prediction_date_str,
            "predicted_demand": round(prediction, 2),
            "note": "Lags and rolling features are dynamically calculated using recent historical data."
        })

    except Exception as e:
        app.logger.error(f"Error during single prediction: {e}", exc_info=True)
        return jsonify({"error": f"An internal server error occurred: {str(e)}"}), 500



# ====== Run the Flask app ======

if __name__ == "__main__":
    app.run(debug=True, port=5000)  # Runs at http://127.0.0.1:5000
