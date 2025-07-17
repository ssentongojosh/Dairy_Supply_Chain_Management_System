# import pandas as pd
from io import StringIO
from datetime import datetime, timedelta
import numpy as np
import joblib
import pandas as pd
import os
from sklearn.preprocessing import OneHotEncoder
from sklearn.ensemble import HistGradientBoostingRegressor # Sticking with your preferred model
from sklearn.metrics import mean_absolute_error, mean_squared_error, r2_score

print("--- Starting Full Data Preparation and Model Training (wholesaler_id as segment) ---")

# --- Step 1: Load Data and Initial Date Conversion ---
# Assuming your full dataset is in a CSV file, e.g., 'retail_sales.csv'
try:
    with open("../database/seeders/Dataset/retail_sales.csv",'r') as file:
        data = file.read()
    df = pd.read_csv(StringIO(data))
except FileNotFoundError:
    print("Error: retail_sales.csv not found. Please ensure the path is correct.")
    # Fallback to in-memory data for demonstration if file not found

    df = pd.read_csv(StringIO(data))


# Convert 'invoice_date' to datetime format
df['invoice_date'] = pd.to_datetime(df['invoice_date'], format='%d/%m/%Y')

# --- NEW: Drop 'shopping_mall' as it's no longer the segment for forecasting ---
# This assumes 'customer_id' column is now correctly named 'wholesaler_id' in the CSV
df.drop(columns=['shopping_mall'], inplace=True)

print("DataFrame after initial load, date conversion, and dropping shopping_mall:")
print(df.head())
print("\nDataFrame Info (check data types and columns):")
print(df.info())

# --- Step 2: Aggregate Quantity by Date, Product, and WHOLESALER_ID ---
# Group by 'invoice_date', 'Product', and the new 'wholesaler_id'
daily_demand = df.groupby(['invoice_date', 'Product', 'wholesaler_id'])['quantity'].sum().reset_index() # MODIFIED
daily_demand.rename(columns={'quantity': 'daily_demand'}, inplace=True)
daily_demand.sort_values(by=['invoice_date', 'Product', 'wholesaler_id'], inplace=True) # MODIFIED

print("\nAggregated Daily Demand Data (by wholesaler_id):")
print(daily_demand.head())
print("\nDataFrame Info (check new structure):")
print(daily_demand.info())
print("\nNumber of unique Product-Wholesaler combinations (our individual time series):")
print(daily_demand[['Product', 'wholesaler_id']].drop_duplicates().shape[0]) # MODIFIED


# --- Step 3: Fill Missing Dates with Zero Demand (Ensuring Continuous Series) ---
min_date = daily_demand['invoice_date'].min()
max_date = daily_demand['invoice_date'].max()
full_date_range = pd.date_range(start=min_date, end=max_date, freq='D')

# Create a complete DataFrame structure for all Product-Wholesaler combinations
all_combinations = pd.MultiIndex.from_product(
    [full_date_range, daily_demand['Product'].unique(), daily_demand['wholesaler_id'].unique()], # MODIFIED
    names=['invoice_date', 'Product', 'wholesaler_id'] # MODIFIED
).to_frame(index=False)

# Merge our existing daily_demand with the full combinations.
full_time_series = pd.merge(
    all_combinations,
    daily_demand,
    on=['invoice_date', 'Product', 'wholesaler_id'], # MODIFIED
    how='left'
)

full_time_series['daily_demand'].fillna(0, inplace=True)
full_time_series.sort_values(by=['Product', 'wholesaler_id', 'invoice_date'], inplace=True) # MODIFIED
full_time_series.reset_index(drop=True, inplace=True)


print("Full Time Series Data (with zeros for missing dates and wholesaler_id):")
example_series = full_time_series[
    (full_time_series['Product'] == 'Butter') &
    (full_time_series['wholesaler_id'] == 'C241288') # MODIFIED to use a sample wholesaler_id
].head(10)
print(example_series)
print("\nFull Time Series Info (check new size):")
print(full_time_series.info())
print(f"\nNumber of entries before filling NaNs: {daily_demand.shape[0]}")
print(f"Number of entries after filling NaNs: {full_time_series.shape[0]}")
print(f"Any NaN values in daily_demand column after fillna: {full_time_series['daily_demand'].isnull().any()}")

# --- Step 4: Feature Engineering (Time-Based and Lag Features) ---
processed_df = full_time_series.copy() # Keep .copy() here for safety before modification
processed_df['year'] = processed_df['invoice_date'].dt.year
processed_df['month'] = processed_df['invoice_date'].dt.month
processed_df['day'] = processed_df['invoice_date'].dt.day
processed_df['day_of_week'] = processed_df['invoice_date'].dt.dayofweek
processed_df['day_of_year'] = processed_df['invoice_date'].dt.dayofyear
processed_df['week_of_year'] = processed_df['invoice_date'].dt.isocalendar().week.astype(int)
processed_df['quarter'] = processed_df['invoice_date'].dt.quarter

lags = [1, 7, 14, 30]
for lag in lags:
    processed_df[f'demand_lag_{lag}d'] = processed_df.groupby(['Product', 'wholesaler_id'])['daily_demand'].shift(lag) # MODIFIED
    processed_df[f'demand_lag_{lag}d'].fillna(0, inplace=True)


print("DataFrame after Feature Engineering (Time-Based & Lag Features):")
print(processed_df.head(15))
print("\nDataFrame Info (check new columns):")
print(processed_df.info())
print("\nDescription of new lag features (should show min=0):")
print(processed_df[[f'demand_lag_{lag}d' for lag in lags]].describe())

# --- 5.1 Rolling Window Features ---
rolling_windows = [7, 30]

for window in rolling_windows:
    processed_df[f'rolling_mean_{window}d'] = processed_df.groupby(['Product', 'wholesaler_id'])['daily_demand'].transform( # MODIFIED
        lambda x: x.rolling(window=window, min_periods=1).mean()
    )
    processed_df[f'rolling_std_{window}d'] = processed_df.groupby(['Product', 'wholesaler_id'])['daily_demand'].transform( # MODIFIED
        lambda x: x.rolling(window=window, min_periods=1).std()
    )
    processed_df[f'rolling_std_{window}d'].fillna(0, inplace=True)

print("\nDataFrame after Rolling Window Features:")
print(processed_df.head(15))
print("\nDataFrame Info (check new rolling columns):")
print(processed_df.info())
print("\nDescription of new rolling features:")
print(processed_df[[f'rolling_mean_{window}d' for window in rolling_windows] + [f'rolling_std_{window}d' for window in rolling_windows]].describe())

# --- 6.1 Identify Categorical Columns to Encode ---
categorical_cols = ['Product', 'wholesaler_id'] # MODIFIED

# --- 6.2 Initialize OneHotEncoder ---
encoder = OneHotEncoder(handle_unknown='ignore', sparse_output=False) # Keep sparse_output=False as per your request

# --- 6.3 Fit and Transform the Categorical Data ---
encoded_features = encoder.fit_transform(processed_df[categorical_cols])

# --- 6.4 Create a DataFrame from the Encoded Features ---
encoded_feature_names = encoder.get_feature_names_out(categorical_cols)
encoded_df = pd.DataFrame(encoded_features, columns=encoded_feature_names, index=processed_df.index)

# --- 6.5 Concatenate Encoded Features with the Original DataFrame ---
processed_df = processed_df.drop(columns=categorical_cols)
processed_df = pd.concat([processed_df, encoded_df], axis=1)

print("\nDataFrame after One-Hot Encoding:")
print(processed_df.head())
print("\nDataFrame Info (check new encoded columns and dropped original categoricals):")
print(processed_df.info())
print(f"\nNumber of columns after encoding: {processed_df.shape[1]}")

# Define features (X) and target (y)
X = processed_df.drop(columns=['invoice_date', 'daily_demand'])
y = processed_df['daily_demand']

print("Features (X) - first 5 rows:")
print(X.head())
print("\nTarget (y) - first 5 values:")
print(y.head())
print(f"\nShape of X: {X.shape}")
print(f"Shape of y: {y.shape}")

# Determine the split point (e.g., use 80% of data for training, 20% for testing)
processed_df.sort_values(by='invoice_date', inplace=True)
split_index = int(len(processed_df) * 0.8)

X_train = X.iloc[:split_index]
X_test = X.iloc[split_index:]
y_train = y.iloc[:split_index]
y_test = y.iloc[split_index:]

split_date = processed_df['invoice_date'].iloc[split_index]

print(f"\nData Split Information:")
print(f"Total data points: {len(processed_df)}")
print(f"Training data points: {len(X_train)} (up to date: {processed_df['invoice_date'].iloc[split_index-1].strftime('%Y-%m-%d')})")
print(f"Testing data points: {len(X_test)} (starting from date: {split_date.strftime('%Y-%m-%d')})")

print("\nX_train shape:", X_train.shape)
print("y_train shape:", y_train.shape)
print("X_test shape:", X_test.shape)
print("y_test shape:", y_test.shape)


# --- 9.1 Model Selection (HistGradientBoostingRegressor) ---
model = HistGradientBoostingRegressor(random_state=42, max_iter=100, learning_rate=0.1)

print("\nSelected Model: HistGradientBoostingRegressor")

# --- 9.2 Model Training ---
print("\nTraining the model...")
model.fit(X_train, y_train)
print("Model training complete!")

# --- 9.3 Prediction ---
print("Making predictions on the test set...")
y_pred = model.predict(X_test)
print("Predictions complete!")

# --- 9.4 Evaluation ---
print("\n--- Model Evaluation ---")
y_pred[y_pred < 0] = 0
mae = mean_absolute_error(y_test, y_pred)
rmse = np.sqrt(mean_squared_error(y_test, y_pred))
r2 = r2_score(y_test, y_pred)

print(f"Mean Absolute Error (MAE): {mae:.2f}")
print(f"Root Mean Squared Error (RMSE): {rmse:.2f}")
print(f"R-squared (R2 Score): {r2:.2f}")
print("\n--- Model Training and Evaluation Complete ---")


# --- Step 10: Save the Model and Preprocessing Components ---
model_dir = 'ml_models'
os.makedirs(model_dir, exist_ok=True)

joblib.dump(model, os.path.join(model_dir, 'demand_forecaster_model.joblib'))
joblib.dump(encoder, os.path.join(model_dir, 'one_hot_encoder.joblib'))
joblib.dump(X.columns.tolist(), os.path.join(model_dir, 'feature_columns.joblib'))
print(f"Model saved to: {os.path.join(model_dir, 'demand_forecaster_model.joblib')}")
print(f"OneHotEncoder saved to: {os.path.join(model_dir, 'one_hot_encoder.joblib')}")
print(f"Feature column names saved to: {os.path.join(model_dir, 'feature_columns.joblib')}")
print("\nAll necessary ML components saved for deployment.")


# --- Step 12: Prepare Recent Historical Data for the API ---
# This part still needs the original 'Product' and 'wholesaler_id' columns
# So we use `full_time_series` which still has these columns
end_date_for_history = full_time_series['invoice_date'].max()
start_date_for_history = end_date_for_history - pd.Timedelta(days=60)

recent_history_df = full_time_series[
    (full_time_series['invoice_date'] >= start_date_for_history) &
    (full_time_series['invoice_date'] <= end_date_for_history)
][['invoice_date', 'Product', 'wholesaler_id', 'daily_demand']].copy() # MODIFIED

recent_history_df.sort_values(by=['invoice_date', 'Product', 'wholesaler_id'], inplace=True) # MODIFIED
history_path = os.path.join(model_dir, 'recent_demand_history.csv')
recent_history_df.to_csv(history_path, index=False)
print(f"\nRecent historical demand data saved to: {history_path}")

print("--- Full Data Preparation and Model Training Complete ---")
