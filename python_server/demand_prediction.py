import pandas as pd,pandas
from io import StringIO
from datetime import datetime, timedelta
import numpy as np
import joblib
import os
from sklearn.preprocessing import OneHotEncoder
from sklearn.ensemble import RandomForestRegressor
from sklearn.metrics import mean_absolute_error, mean_squared_error, r2_score

print("--- Starting Full Data Preparation and Model Training (wholesaler_id) ---")

# --- Step 1: Load Data and Initial Date Conversion ---
# Your data as a string (replace with pd.read_csv('your_full_data.csv') for actual file)

with open("../database/seeders/Dataset/retail_sales.csv",'r') as file:
    data = file.read()

df = pandas.read_csv(StringIO(data))

# Convert 'invoice_date' to datetime objects
df['invoice_date'] = pd.to_datetime(df['invoice_date'], format='%d/%m/%Y')

# --- NEW: Drop 'shopping_mall' as it's no longer a relevant segment ---
df.drop(columns=['shopping_mall'], inplace=True)

# --- NEW: Rename 'customer_id' to 'wholesaler_id' ---
df.rename(columns={'customer_id': 'wholesaler_id'}, inplace=True)

print("DataFrame after initial load, date conversion, dropping shopping_mall, and renaming customer_id:")
print(df.head())
print("\nDataFrame Info (check data types and columns):")
print(df.info())

# --- Step 2: Aggregate Quantity by Date, Product, and WHOLESALER_ID ---
daily_demand = df.groupby(['invoice_date', 'Product', 'wholesaler_id'])['quantity'].sum().reset_index()
daily_demand.rename(columns={'quantity': 'daily_demand'}, inplace=True)
daily_demand.sort_values(by=['invoice_date', 'Product', 'wholesaler_id'], inplace=True)

print("\nAggregated Daily Demand Data (by wholesaler_id):")
print(daily_demand.head())

# --- Step 3: Fill Missing Dates with Zero Demand (Ensuring Continuous Series) ---
all_combinations = pd.MultiIndex.from_product(
    [daily_demand['invoice_date'].unique(), daily_demand['Product'].unique(), daily_demand['wholesaler_id'].unique()],
    names=['invoice_date', 'Product', 'wholesaler_id']
).to_frame(index=False)

full_time_series = pd.merge(
    all_combinations,
    daily_demand,
    on=['invoice_date', 'Product', 'wholesaler_id'],
    how='left'
)
full_time_series['daily_demand'].fillna(0, inplace=True)
full_time_series.sort_values(by=['Product', 'wholesaler_id', 'invoice_date'], inplace=True)
full_time_series.reset_index(drop=True, inplace=True)

print("\nFull Time Series Data (with zeros for missing dates and wholesaler_id):")
# Show a specific product-wholesaler combo to demonstrate the effect
example_series = full_time_series[
    (full_time_series['Product'] == 'Butter') &
    (full_time_series['wholesaler_id'] == 'C241288') # Use a wholesaler_id from your sample
].head(10)
print(example_series)
print(f"\nNumber of entries after filling NaNs: {full_time_series.shape[0]}")

# --- Step 4: Feature Engineering (Time-Based and Lag Features) ---
processed_df = full_time_series.copy()
processed_df['year'] = processed_df['invoice_date'].dt.year
processed_df['month'] = processed_df['invoice_date'].dt.month
processed_df['day'] = processed_df['invoice_date'].dt.day
processed_df['day_of_week'] = processed_df['invoice_date'].dt.dayofweek
processed_df['day_of_year'] = processed_df['invoice_date'].dt.dayofyear
processed_df['week_of_year'] = processed_df['invoice_date'].dt.isocalendar().week.astype(int)
processed_df['quarter'] = processed_df['invoice_date'].dt.quarter

lags = [1, 7, 14, 30]
for lag in lags:
    processed_df[f'demand_lag_{lag}d'] = processed_df.groupby(['Product', 'wholesaler_id'])['daily_demand'].shift(lag)
    processed_df[f'demand_lag_{lag}d'].fillna(0, inplace=True)

print("\nDataFrame after Time-Based & Lag Features:")
print(processed_df.head())

# --- Step 5: Rolling Window Features ---
rolling_windows = [7, 30]
for window in rolling_windows:
    processed_df[f'rolling_mean_{window}d'] = processed_df.groupby(['Product', 'wholesaler_id'])['daily_demand'].transform(
        lambda x: x.rolling(window=window, min_periods=1).mean()
    )
    processed_df[f'rolling_std_{window}d'] = processed_df.groupby(['Product', 'wholesaler_id'])['daily_demand'].transform(
        lambda x: x.rolling(window=window, min_periods=1).std()
    )
    processed_df[f'rolling_std_{window}d'].fillna(0, inplace=True)

print("\nDataFrame after Rolling Window Features:")
print(processed_df.head())

# --- Step 6: Encoding Categorical Variables (Product and WHOLESALER_ID) ---
categorical_cols = ['Product', 'wholesaler_id'] # Use wholesaler_id here
encoder = OneHotEncoder(handle_unknown='ignore', sparse_output=False)
encoded_features = encoder.fit_transform(processed_df[categorical_cols])
encoded_feature_names = encoder.get_feature_names_out(categorical_cols)
encoded_df = pd.DataFrame(encoded_features, columns=encoded_feature_names, index=processed_df.index)
processed_df = processed_df.drop(columns=categorical_cols)
processed_df = pd.concat([processed_df, encoded_df], axis=1)

print("\nDataFrame after One-Hot Encoding:")
print(processed_df.head())
print(f"\nNumber of columns after encoding: {processed_df.shape[1]}")

# --- Step 7 & 8: Define Features (X) and Target (y) & Train-Test Split ---
X = processed_df.drop(columns=['invoice_date', 'daily_demand'])
y = processed_df['daily_demand']
processed_df.sort_values(by='invoice_date', inplace=True) # Ensure sorted before split
split_index = int(len(processed_df) * 0.8)
X_train = X.iloc[:split_index]
X_test = X.iloc[split_index:]
y_train = y.iloc[:split_index]
y_test = y.iloc[split_index:]

print(f"\nData Split Information:")
print(f"X_train shape: {X_train.shape}, y_train shape: {y_train.shape}")
print(f"X_test shape: {X_test.shape}, y_test shape: {y_test.shape}")

# --- Step 9: Model Selection, Training, and Evaluation with RandomForestRegressor ---
model = RandomForestRegressor(n_estimators=100, random_state=42, n_jobs=-1)

print("\nTraining RandomForestRegressor model...")
model.fit(X_train, y_train)
print("Model training complete!")

y_pred = model.predict(X_test)
y_pred[y_pred < 0] = 0

mae = mean_absolute_error(y_test, y_pred)
rmse = np.sqrt(mean_squared_error(y_test, y_pred))
r2 = r2_score(y_test, y_pred)

print("\n--- Model Evaluation ---")
print(f"Mean Absolute Error (MAE): {mae:.2f}")
print(f"Root Mean Squared Error (RMSE): {rmse:.2f}")
print(f"R-squared (R2 Score): {r2:.2f}")

# --- Step 10: Save the Model and Preprocessing Components ---
model_dir = 'ml_models'
os.makedirs(model_dir, exist_ok=True)

joblib.dump(model, os.path.join(model_dir, 'demand_forecaster_model.joblib'))
joblib.dump(encoder, os.path.join(model_dir, 'one_hot_encoder.joblib'))
joblib.dump(X.columns.tolist(), os.path.join(model_dir, 'feature_columns.joblib'))

# --- Step 12: Prepare Recent Historical Data for the API ---
end_date_for_history = processed_df['invoice_date'].max()
start_date_for_history = end_date_for_history - pd.Timedelta(days=60)

recent_history_df = processed_df[
    (processed_df['invoice_date'] >= start_date_for_history) &
    (processed_df['invoice_date'] <= end_date_for_history)
][['invoice_date', 'Product', 'wholesaler_id', 'daily_demand']].copy() # Use wholesaler_id

recent_history_df.sort_values(by=['invoice_date', 'Product', 'wholesaler_id'], inplace=True)
history_path = os.path.join(model_dir, 'recent_demand_history.csv')
recent_history_df.to_csv(history_path, index=False)

print(f"\nRecent historical demand data saved to: {history_path}")
print("\n--- Full Data Preparation and Model Training Complete ---")
