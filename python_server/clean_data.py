
# import pandas as pd
# from io import StringIO
# from datetime import datetime, timedelta
# import numpy as np
# import joblib
# import os
# from sklearn.preprocessing import OneHotEncoder
# from xgboost import XGBRegressor # IMPORTANT: Import XGBRegressor
# from sklearn.metrics import mean_absolute_error, mean_squared_error, r2_score
# from scipy.sparse import hstack, csr_matrix

# print("--- Starting Full Data Preparation and Model Training (wholesaler_id, XGBoost, Sparse Input) ---")

# # --- Step 1: Load Data and Initial Date Conversion ---
# try:
#     with open("../database/seeders/Dataset/retail_sales.csv",'r') as file:
#         data = file.read()
#     df = pd.read_csv(StringIO(data))
# except FileNotFoundError:
#     print("Error: retail_sales.csv not found. Please ensure the path is correct.")
#     # Fallback to in-memory data for demonstration if file not found
#     data = """invoice_no,wholesaler_id,age,gender,Product,quantity,price,payment_method,invoice_date,shopping_mall
# I138884,241288,28,Female,chocolate milk,5,1500.4,Credit Card,05/08/2022,Kanyon
# I317333,111565,21,Male,Pasteurized milk 3.0% mg,3,1800.51,Debit Card,12/12/2021,Forum Istanbul
# I127801,266599,20,Male,Pasteurized milk 2.5% mg,1,300.08,Cash,09/11/2021,Metrocity
# I173702,988172,66,Female,Skimmed pasteurized milk,5,3000.85,Credit Card,16/05/2021,Metropol AVM
# I337046,189076,53,Female,Sterilized whole milk,4,60.6,Cash,24/10/2021,Kanyon
# I227836,657758,28,Female,Sterilized semi-skimmed milk,5,1500.4,Credit Card,24/05/2022,Forum Istanbul
# I121056,151197,49,Female,Sterilized skimmed milk,1,40.66,Cash,13/03/2022,Istinye Park
# I293112,176086,32,Female,Flavored sterilized milk,2,600.16,Credit Card,13/01/2021,Mall of Istanbul
# I293455,159642,69,Male,Powdered milk 28% mg,3,900.24,Credit Card,04/11/2021,Metrocity
# I326945,283361,60,Female,Powdered milk 26% mg,2,600.16,Credit Card,22/08/2021,Kanyon
# I306368,240286,36,Female,Powdered milk 18% mg,2,10.46,Cash,25/12/2022,Metrocity
# """
#     df = pd.read_csv(StringIO(data))


# # convert 'invoice_date' to datetime format
# df['invoice_date'] = pd.to_datetime(df['invoice_date'], format='%d/%m/%Y')

# # --- MODIFICATION 1: Limit the date range for training data (Reverted to 2 years) ---
# max_date_in_df = df['invoice_date'].max()
# training_start_date = max_date_in_df - pd.DateOffset(years=2) # Reverted to 2 years

# df = df[df['invoice_date'] >= training_start_date].copy()
# print(f"\nFiltered DataFrame to dates from {training_start_date.strftime('%Y-%m-%d')} to {max_date_in_df.strftime('%Y-%m-%d')}")
# print(f"New DataFrame size after date filtering: {df.shape[0]} rows")


# # --- MODIFICATION 2: Drop 'shopping_mall' column ---
# df.drop(columns=['shopping_mall'], inplace=True)

# print("DataFrame after initial load, date conversion, date filtering, and dropping shopping_mall:")
# print(df.head())
# print("\nDataFrame Info (check data types and columns):")
# print(df.info())

# # --- Step 2: Aggregate Quantity by Date, Product, and WHOLESALER_ID ---
# daily_demand = df.groupby(['invoice_date', 'Product', 'wholesaler_id'])['quantity'].sum().reset_index()
# daily_demand.rename(columns={'quantity': 'daily_demand'}, inplace=True)
# daily_demand.sort_values(by=['invoice_date', 'Product', 'wholesaler_id'], inplace=True)

# print("\nAggregated Daily Demand Data (by wholesaler_id):")
# print(daily_demand.head())
# print("\nDataFrame Info (check new structure):")
# print(daily_demand.info())
# print("\nNumber of unique Product-Wholesaler combinations (our individual time series):")
# print(daily_demand[['Product', 'wholesaler_id']].drop_duplicates().shape[0])


# # --- Step 3: Fill Missing Dates with Zero Demand (Ensuring Continuous Series) ---
# min_date = daily_demand['invoice_date'].min()
# max_date = daily_demand['invoice_date'].max()
# full_date_range = pd.date_range(start=min_date, end=max_date, freq='D')

# all_combinations = pd.MultiIndex.from_product(
#     [full_date_range, daily_demand['Product'].unique(), daily_demand['wholesaler_id'].unique()],
#     names=['invoice_date', 'Product', 'wholesaler_id']
# ).to_frame(index=False)

# full_time_series = pd.merge(
#     all_combinations,
#     daily_demand,
#     on=['invoice_date', 'Product', 'wholesaler_id'],
#     how='left'
# )

# full_time_series['daily_demand'].fillna(0, inplace=True)
# full_time_series.sort_values(by=['Product', 'wholesaler_id', 'invoice_date'], inplace=True)
# full_time_series.reset_index(drop=True, inplace=True)


# print("Full Time Series Data (with zeros for missing dates and wholesaler_id):")
# example_series = full_time_series[
#     (full_time_series['Product'] == 'Butter') &
#     (full_time_series['wholesaler_id'] == 241288)
# ].head(10)
# print(example_series)
# print("\nFull Time Series Info (check new size):")
# print(full_time_series.info())
# print(f"\nNumber of entries before filling NaNs: {daily_demand.shape[0]}")
# print(f"Number of entries after filling NaNs: {full_time_series.shape[0]}")
# print(f"Any NaN values in daily_demand column after fillna: {full_time_series['daily_demand'].isnull().any()}")

# # --- Step 4: Feature Engineering (Time-Based and Lag Features) ---
# processed_df = full_time_series.copy()
# processed_df['year'] = processed_df['invoice_date'].dt.year
# processed_df['month'] = processed_df['invoice_date'].dt.month
# processed_df['day'] = processed_df['invoice_date'].dt.day
# processed_df['day_of_week'] = processed_df['invoice_date'].dt.dayofweek
# processed_df['day_of_year'] = processed_df['invoice_date'].dt.dayofyear
# processed_df['week_of_year'] = processed_df['invoice_date'].dt.isocalendar().week.astype(int)
# processed_df['quarter'] = processed_df['invoice_date'].dt.quarter

# lags = [1, 7, 14, 30]
# for lag in lags:
#     processed_df[f'demand_lag_{lag}d'] = processed_df.groupby(['Product', 'wholesaler_id'])['daily_demand'].shift(lag)
#     processed_df[f'demand_lag_{lag}d'].fillna(0, inplace=True)


# print("DataFrame after Time-Based & Lag Features:")
# print(processed_df.head(15))
# print("\nDataFrame Info (check new columns):")
# print(processed_df.info())
# print("\nDescription of new lag features (should show min=0):")
# print(processed_df[[f'demand_lag_{lag}d' for lag in lags]].describe())

# # --- 5.1 Rolling Window Features ---
# rolling_windows = [7, 30]

# for window in rolling_windows:
#     processed_df[f'rolling_mean_{window}d'] = processed_df.groupby(['Product', 'wholesaler_id'])['daily_demand'].transform(
#         lambda x: x.rolling(window=window, min_periods=1).mean()
#     )
#     processed_df[f'rolling_std_{window}d'] = processed_df.groupby(['Product', 'wholesaler_id'])['daily_demand'].transform(
#         lambda x: x.rolling(window=window, min_periods=1).std()
#     )
#     processed_df[f'rolling_std_{window}d'].fillna(0, inplace=True)

# print("\nDataFrame after Rolling Window Features:")
# print(processed_df.head(15))
# print("\nDataFrame Info (check new rolling columns):")
# print(processed_df.info())
# print("\nDescription of new rolling features:")
# print(processed_df[[f'rolling_mean_{window}d' for window in rolling_windows] + [f'rolling_std_{window}d' for window in rolling_windows]].describe())

# # --- Step 6: Encoding Categorical Variables (Product and WHOLESALER_ID) ---
# categorical_cols = ['Product', 'wholesaler_id']

# encoder = OneHotEncoder(handle_unknown='ignore', sparse_output=True)
# encoded_features_sparse = encoder.fit_transform(processed_df[categorical_cols])

# numerical_features_df = processed_df.drop(columns=categorical_cols + ['invoice_date', 'daily_demand'])

# X_combined_sparse = hstack([numerical_features_df.values, encoded_features_sparse])

# # Convert to CSR matrix for efficient slicing (XGBoost can also take COO but CSR is generally better for row-wise ops)
# X_combined_sparse = X_combined_sparse.tocsr()

# numerical_feature_names = numerical_features_df.columns.tolist()
# encoded_feature_names = encoder.get_feature_names_out(categorical_cols)
# all_feature_names = numerical_feature_names + encoded_feature_names.tolist()

# y = processed_df['daily_demand']

# print("\nFeatures (X) and Target (y) prepared. X is now a sparse matrix.")
# print(f"Shape of X (sparse matrix): {X_combined_sparse.shape}")
# print(f"Shape of y: {y.shape}")

# # --- Step 7 & 8: Train-Test Split (on sparse matrix) ---
# processed_df.sort_values(by='invoice_date', inplace=True)

# split_index = int(len(processed_df) * 0.8)

# X_train_sparse = X_combined_sparse[:split_index]
# X_test_sparse = X_combined_sparse[split_index:]
# y_train = y.iloc[:split_index]
# y_test = y.iloc[split_index:]

# split_date = processed_df['invoice_date'].iloc[split_index]

# print(f"\nData Split Information:")
# print(f"Total data points: {processed_df.shape[0]}")
# print(f"Training data points: {X_train_sparse.shape[0]} (up to date: {processed_df['invoice_date'].iloc[split_index-1].strftime('%Y-%m-%d')})")
# print(f"Testing data points: {X_test_sparse.shape[0]} (starting from date: {split_date.strftime('%Y-%m-%d')})")

# print("\nX_train_sparse shape:", X_train_sparse.shape)
# print("y_train shape:", y_train.shape)
# print("X_test_sparse shape:", X_test_sparse.shape)
# print("y_test shape:", y_test.shape)


# # --- 9.1 Model Selection (XGBoostRegressor) ---
# # IMPORTANT: Initialize XGBRegressor
# model = XGBRegressor(
#     objective='reg:squarederror', # Objective for regression tasks
#     n_estimators=100,             # Number of boosting rounds (trees)
#     learning_rate=0.1,            # Step size shrinkage
#     random_state=42,              # For reproducibility
#     n_jobs=-1,                    # Use all available CPU cores
#     tree_method='hist'            # Use histogram-based tree for speed with large data
# )

# print("\nSelected Model: XGBRegressor")

# # --- 9.2 Model Training ---
# print("\nTraining the model (on sparse data with XGBoost)...")
# # IMPORTANT: No .toarray() needed here for XGBoost
# model.fit(X_train_sparse, y_train)
# print("Model training complete!")

# # --- 9.3 Prediction ---
# print("Making predictions on the test set (using sparse data with XGBoost)...")
# # IMPORTANT: No .toarray() needed here for XGBoost
# y_pred = model.predict(X_test_sparse)
# y_pred[y_pred < 0] = 0

# mae = mean_absolute_error(y_test, y_pred)
# rmse = np.sqrt(mean_squared_error(y_test, y_pred))
# r2 = r2_score(y_test, y_pred)

# print("\n--- Model Evaluation ---")
# print(f"Mean Absolute Error (MAE): {mae:.2f}")
# print(f"Root Mean Squared Error (RMSE): {rmse:.2f}")
# print(f"R-squared (R2 Score): {r2:.2f}")
# print("\n--- Model Training and Evaluation Complete ---")


# # --- Step 10: Save the Model and Preprocessing Components ---
# model_dir = 'ml_models'
# os.makedirs(model_dir, exist_ok=True)

# joblib.dump(model, os.path.join(model_dir, 'demand_forecaster_model.joblib'))
# joblib.dump(encoder, os.path.join(model_dir, 'one_hot_encoder.joblib'))
# joblib.dump(all_feature_names, os.path.join(model_dir, 'feature_columns.joblib'))
# print(f"Model saved to: {os.path.join(model_dir, 'demand_forecaster_model.joblib')}")
# print(f"OneHotEncoder saved to: {os.path.join(model_dir, 'one_hot_encoder.joblib')}")
# print(f"Feature column names saved to: {os.path.join(model_dir, 'feature_columns.joblib')}")
# print("\nAll necessary ML components saved for deployment.")


# # --- Step 12: Prepare Recent Historical Data for the API ---
# end_date_for_history = full_time_series['invoice_date'].max()
# start_date_for_history = end_date_for_history - pd.Timedelta(days=60)

# recent_history_df = full_time_series[
#     (full_time_series['invoice_date'] >= start_date_for_history) &
#     (full_time_series['invoice_date'] <= end_date_for_history)
# ][['invoice_date', 'Product', 'wholesaler_id', 'daily_demand']].copy()

# recent_history_df.sort_values(by=['invoice_date', 'Product', 'wholesaler_id'], inplace=True)
# history_path = os.path.join(model_dir, 'recent_demand_history.csv')
# recent_history_df.to_csv(history_path, index=False)
# print(f"\nRecent historical demand data saved to: {history_path}")

# print("--- Full Data Preparation and Model Training Complete ---")

import pandas as pd
from io import StringIO
from datetime import datetime, timedelta
import numpy as np
import joblib
import os
from sklearn.preprocessing import OneHotEncoder
from xgboost import XGBRegressor
from sklearn.metrics import mean_absolute_error, mean_squared_error, r2_score
from scipy.sparse import hstack, csr_matrix

print("--- Starting Full Data Preparation and Model Training (wholesaler_id and General Product Forecasts) ---")

# --- Initial Data Load and Preprocessing (Common to both models) ---
try:
    with open("../database/seeders/Dataset/retail_sales.csv",'r') as file:
        data = file.read()
    df = pd.read_csv(StringIO(data))
except FileNotFoundError:
    print("Error: retail_sales.csv not found. Please ensure the path is correct.")
    # Fallback to in-memory data for demonstration if file not found
    data = """invoice_no,wholesaler_id,age,gender,Product,quantity,price,payment_method,invoice_date,shopping_mall
I138884,241288,28,Female,chocolate milk,5,1500.4,Credit Card,05/08/2022,Kanyon
I317333,111565,21,Male,Pasteurized milk 3.0% mg,3,1800.51,Debit Card,12/12/2021,Forum Istanbul
I127801,266599,20,Male,Pasteurized milk 2.5% mg,1,300.08,Cash,09/11/2021,Metrocity
I173702,988172,66,Female,Skimmed pasteurized milk,5,3000.85,Credit Card,16/05/2021,Metropol AVM
I337046,189076,53,Female,Sterilized whole milk,4,60.6,Cash,24/10/2021,Kanyon
I227836,657758,28,Female,Sterilized semi-skimmed milk,5,1500.4,Credit Card,24/05/2022,Forum Istanbul
I121056,151197,49,Female,Sterilized skimmed milk,1,40.66,Cash,13/03/2022,Istinye Park
I293112,176086,32,Female,Flavored sterilized milk,2,600.16,Credit Card,13/01/2021,Mall of Istanbul
I293455,159642,69,Male,Powdered milk 28% mg,3,900.24,Credit Card,04/11/2021,Metrocity
I326945,283361,60,Female,Powdered milk 26% mg,2,600.16,Credit Card,22/08/2021,Kanyon
I306368,240286,36,Female,Powdered milk 18% mg,2,10.46,Cash,25/12/2022,Metrocity
"""
    df = pd.read_csv(StringIO(data))

df['invoice_date'] = pd.to_datetime(df['invoice_date'], format='%d/%m/%Y')

# Limit the date range for training data (e.g., last 2 years)
max_date_in_df = df['invoice_date'].max()
training_start_date = max_date_in_df - pd.DateOffset(years=2)
df = df[df['invoice_date'] >= training_start_date].copy()
print(f"\nFiltered DataFrame to dates from {training_start_date.strftime('%Y-%m-%d')} to {max_date_in_df.strftime('%Y-%m-%d')}")
print(f"New DataFrame size after date filtering: {df.shape[0]} rows")

# Drop 'shopping_mall' column as it's not used for either forecast type
df.drop(columns=['shopping_mall'], inplace=True)

print("DataFrame after initial load, date conversion, date filtering, and dropping shopping_mall:")
print(df.head())
print("\nDataFrame Info (check data types and columns):")
print(df.info())

# Define a directory to save the models and preprocessors
model_dir = 'ml_models'
os.makedirs(model_dir, exist_ok=True)

# --- FORECASTING FOR SPECIFIC WHOLESALER_ID (Existing Logic) ---
print("\n--- Processing Data and Training Model for Wholesaler-Specific Forecasts ---")

# Aggregate Quantity by Date, Product, and Wholesaler ID
daily_demand_wholesaler = df.groupby(['invoice_date', 'Product', 'wholesaler_id'])['quantity'].sum().reset_index()
daily_demand_wholesaler.rename(columns={'quantity': 'daily_demand'}, inplace=True)
daily_demand_wholesaler.sort_values(by=['invoice_date', 'Product', 'wholesaler_id'], inplace=True)

print("Aggregated Daily Demand Data (by wholesaler_id):")
print(daily_demand_wholesaler.head())
print("\nNumber of unique Product-Wholesaler combinations:")
print(daily_demand_wholesaler[['Product', 'wholesaler_id']].drop_duplicates().shape[0])

# Fill Missing Dates with Zero Demand (Ensuring Continuous Series)
min_date_wholesaler = daily_demand_wholesaler['invoice_date'].min()
max_date_wholesaler = daily_demand_wholesaler['invoice_date'].max()
full_date_range_wholesaler = pd.date_range(start=min_date_wholesaler, end=max_date_wholesaler, freq='D')

all_combinations_wholesaler = pd.MultiIndex.from_product(
    [full_date_range_wholesaler, daily_demand_wholesaler['Product'].unique(), daily_demand_wholesaler['wholesaler_id'].unique()],
    names=['invoice_date', 'Product', 'wholesaler_id']
).to_frame(index=False)

full_time_series_wholesaler = pd.merge(
    all_combinations_wholesaler,
    daily_demand_wholesaler,
    on=['invoice_date', 'Product', 'wholesaler_id'],
    how='left'
)
full_time_series_wholesaler['daily_demand'].fillna(0, inplace=True)
full_time_series_wholesaler.sort_values(by=['Product', 'wholesaler_id', 'invoice_date'], inplace=True)
full_time_series_wholesaler.reset_index(drop=True, inplace=True)

print("Full Time Series Data (wholesaler-specific, with zeros for missing dates):")
print(full_time_series_wholesaler.head())

# Feature Engineering (Time-Based, Lag, Rolling Features)
processed_df_wholesaler = full_time_series_wholesaler.copy()
processed_df_wholesaler['year'] = processed_df_wholesaler['invoice_date'].dt.year
processed_df_wholesaler['month'] = processed_df_wholesaler['invoice_date'].dt.month
processed_df_wholesaler['day'] = processed_df_wholesaler['invoice_date'].dt.day
processed_df_wholesaler['day_of_week'] = processed_df_wholesaler['invoice_date'].dt.dayofweek
processed_df_wholesaler['day_of_year'] = processed_df_wholesaler['invoice_date'].dt.dayofyear
processed_df_wholesaler['week_of_year'] = processed_df_wholesaler['invoice_date'].dt.isocalendar().week.astype(int)
processed_df_wholesaler['quarter'] = processed_df_wholesaler['invoice_date'].dt.quarter

lags = [1, 7, 14, 30]
for lag in lags:
    processed_df_wholesaler[f'demand_lag_{lag}d'] = processed_df_wholesaler.groupby(['Product', 'wholesaler_id'])['daily_demand'].shift(lag)
    processed_df_wholesaler[f'demand_lag_{lag}d'].fillna(0, inplace=True)

rolling_windows = [7, 30]
for window in rolling_windows:
    processed_df_wholesaler[f'rolling_mean_{window}d'] = processed_df_wholesaler.groupby(['Product', 'wholesaler_id'])['daily_demand'].transform(
        lambda x: x.rolling(window=window, min_periods=1).mean()
    )
    processed_df_wholesaler[f'rolling_std_{window}d'] = processed_df_wholesaler.groupby(['Product', 'wholesaler_id'])['daily_demand'].transform(
        lambda x: x.rolling(window=window, min_periods=1).std()
    )
    processed_df_wholesaler[f'rolling_std_{window}d'].fillna(0, inplace=True)

# Encoding Categorical Variables (Product and WHOLESALER_ID)
categorical_cols_wholesaler = ['Product', 'wholesaler_id']
encoder_wholesaler = OneHotEncoder(handle_unknown='ignore', sparse_output=True)
encoded_features_sparse_wholesaler = encoder_wholesaler.fit_transform(processed_df_wholesaler[categorical_cols_wholesaler])

numerical_features_df_wholesaler = processed_df_wholesaler.drop(columns=categorical_cols_wholesaler + ['invoice_date', 'daily_demand'])
X_combined_sparse_wholesaler = hstack([numerical_features_df_wholesaler.values, encoded_features_sparse_wholesaler]).tocsr()

numerical_feature_names_wholesaler = numerical_features_df_wholesaler.columns.tolist()
encoded_feature_names_wholesaler = encoder_wholesaler.get_feature_names_out(categorical_cols_wholesaler)
all_feature_names_wholesaler = numerical_feature_names_wholesaler + encoded_feature_names_wholesaler.tolist()

y_wholesaler = processed_df_wholesaler['daily_demand']

print(f"\nWholesaler-Specific Features (X) shape: {X_combined_sparse_wholesaler.shape}")
print(f"Wholesaler-Specific Target (y) shape: {y_wholesaler.shape}")

# Train-Test Split
processed_df_wholesaler.sort_values(by='invoice_date', inplace=True)
split_index_wholesaler = int(len(processed_df_wholesaler) * 0.8)

X_train_wholesaler = X_combined_sparse_wholesaler[:split_index_wholesaler]
X_test_wholesaler = X_combined_sparse_wholesaler[split_index_wholesaler:]
y_train_wholesaler = y_wholesaler.iloc[:split_index_wholesaler]
y_test_wholesaler = y_wholesaler.iloc[split_index_wholesaler:]

print(f"Wholesaler-Specific Training data points: {X_train_wholesaler.shape[0]}")
print(f"Wholesaler-Specific Testing data points: {X_test_wholesaler.shape[0]}")

# Model Training (XGBoostRegressor for Wholesaler)
model_wholesaler = XGBRegressor(
    objective='reg:squarederror',
    n_estimators=100,
    learning_rate=0.1,
    random_state=42,
    n_jobs=-1,
    tree_method='hist'
)
print("\nTraining Wholesaler-Specific XGBoost model...")
model_wholesaler.fit(X_train_wholesaler, y_train_wholesaler)
print("Wholesaler-Specific Model training complete!")

# Evaluation
y_pred_wholesaler = model_wholesaler.predict(X_test_wholesaler)
y_pred_wholesaler[y_pred_wholesaler < 0] = 0
mae_wholesaler = mean_absolute_error(y_test_wholesaler, y_pred_wholesaler)
rmse_wholesaler = np.sqrt(mean_squared_error(y_test_wholesaler, y_pred_wholesaler))
r2_wholesaler = r2_score(y_test_wholesaler, y_pred_wholesaler)
print("\n--- Wholesaler-Specific Model Evaluation ---")
print(f"MAE: {mae_wholesaler:.2f}, RMSE: {rmse_wholesaler:.2f}, R2: {r2_wholesaler:.2f}")

# Save Wholesaler-Specific Model Components
joblib.dump(model_wholesaler, os.path.join(model_dir, 'demand_forecaster_model_wholesaler.joblib'))
joblib.dump(encoder_wholesaler, os.path.join(model_dir, 'one_hot_encoder_wholesaler.joblib'))
joblib.dump(all_feature_names_wholesaler, os.path.join(model_dir, 'feature_columns_wholesaler.joblib'))
print("Wholesaler-Specific ML components saved.")

# Prepare Recent Historical Data for Wholesaler API
end_date_for_history_wholesaler = full_time_series_wholesaler['invoice_date'].max()
start_date_for_history_wholesaler = end_date_for_history_wholesaler - pd.Timedelta(days=60)
recent_history_df_wholesaler = full_time_series_wholesaler[
    (full_time_series_wholesaler['invoice_date'] >= start_date_for_history_wholesaler) &
    (full_time_series_wholesaler['invoice_date'] <= end_date_for_history_wholesaler)
][['invoice_date', 'Product', 'wholesaler_id', 'daily_demand']].copy()
recent_history_df_wholesaler.sort_values(by=['invoice_date', 'Product', 'wholesaler_id'], inplace=True)
recent_history_df_wholesaler.to_csv(os.path.join(model_dir, 'recent_demand_history_wholesaler.csv'), index=False)
print("Wholesaler-Specific recent historical demand data saved.")


# --- FORECASTING FOR GENERAL PRODUCT DEMAND (NEW LOGIC) ---
print("\n--- Processing Data and Training Model for General Product Forecasts ---")

# Aggregate Quantity by Date and Product (General Demand)
daily_demand_general = df.groupby(['invoice_date', 'Product'])['quantity'].sum().reset_index()
daily_demand_general.rename(columns={'quantity': 'daily_demand'}, inplace=True)
daily_demand_general.sort_values(by=['invoice_date', 'Product'], inplace=True)

print("Aggregated Daily Demand Data (General Product):")
print(daily_demand_general.head())
print("\nNumber of unique Product combinations (General):")
print(daily_demand_general['Product'].drop_duplicates().shape[0])

# Fill Missing Dates with Zero Demand (Ensuring Continuous Series)
min_date_general = daily_demand_general['invoice_date'].min()
max_date_general = daily_demand_general['invoice_date'].max()
full_date_range_general = pd.date_range(start=min_date_general, end=max_date_general, freq='D')

all_combinations_general = pd.MultiIndex.from_product(
    [full_date_range_general, daily_demand_general['Product'].unique()],
    names=['invoice_date', 'Product']
).to_frame(index=False)

full_time_series_general = pd.merge(
    all_combinations_general,
    daily_demand_general,
    on=['invoice_date', 'Product'],
    how='left'
)
full_time_series_general['daily_demand'].fillna(0, inplace=True)
full_time_series_general.sort_values(by=['Product', 'invoice_date'], inplace=True)
full_time_series_general.reset_index(drop=True, inplace=True)

print("Full Time Series Data (General Product, with zeros for missing dates):")
print(full_time_series_general.head())

# Feature Engineering (Time-Based, Lag, Rolling Features) - NO WHOLESALER_ID
processed_df_general = full_time_series_general.copy()
processed_df_general['year'] = processed_df_general['invoice_date'].dt.year
processed_df_general['month'] = processed_df_general['invoice_date'].dt.month
processed_df_general['day'] = processed_df_general['invoice_date'].dt.day
processed_df_general['day_of_week'] = processed_df_general['invoice_date'].dt.dayofweek
processed_df_general['day_of_year'] = processed_df_general['invoice_date'].dt.dayofyear
processed_df_general['week_of_year'] = processed_df_general['invoice_date'].dt.isocalendar().week.astype(int)
processed_df_general['quarter'] = processed_df_general['invoice_date'].dt.quarter

for lag in lags: # Use same lags as wholesaler model
    processed_df_general[f'demand_lag_{lag}d'] = processed_df_general.groupby(['Product'])['daily_demand'].shift(lag)
    processed_df_general[f'demand_lag_{lag}d'].fillna(0, inplace=True)

for window in rolling_windows: # Use same rolling windows as wholesaler model
    processed_df_general[f'rolling_mean_{window}d'] = processed_df_general.groupby(['Product'])['daily_demand'].transform(
        lambda x: x.rolling(window=window, min_periods=1).mean()
    )
    processed_df_general[f'rolling_std_{window}d'] = processed_df_general.groupby(['Product'])['daily_demand'].transform(
        lambda x: x.rolling(window=window, min_periods=1).std()
    )
    processed_df_general[f'rolling_std_{window}d'].fillna(0, inplace=True)

# Encoding Categorical Variables (Product ONLY)
categorical_cols_general = ['Product'] # Only Product for general forecast
encoder_general = OneHotEncoder(handle_unknown='ignore', sparse_output=True)
encoded_features_sparse_general = encoder_general.fit_transform(processed_df_general[categorical_cols_general])

numerical_features_df_general = processed_df_general.drop(columns=categorical_cols_general + ['invoice_date', 'daily_demand'])
X_combined_sparse_general = hstack([numerical_features_df_general.values, encoded_features_sparse_general]).tocsr()

numerical_feature_names_general = numerical_features_df_general.columns.tolist()
encoded_feature_names_general = encoder_general.get_feature_names_out(categorical_cols_general)
all_feature_names_general = numerical_feature_names_general + encoded_feature_names_general.tolist()

y_general = processed_df_general['daily_demand']

print(f"\nGeneral Features (X) shape: {X_combined_sparse_general.shape}")
print(f"General Target (y) shape: {y_general.shape}")

# Train-Test Split
processed_df_general.sort_values(by='invoice_date', inplace=True)
split_index_general = int(len(processed_df_general) * 0.8)

X_train_general = X_combined_sparse_general[:split_index_general]
X_test_general = X_combined_sparse_general[split_index_general:]
y_train_general = y_general.iloc[:split_index_general]
y_test_general = y_general.iloc[split_index_general:]

print(f"General Training data points: {X_train_general.shape[0]}")
print(f"General Testing data points: {X_test_general.shape[0]}")

# Model Training (XGBoostRegressor for General)
model_general = XGBRegressor(
    objective='reg:squarederror',
    n_estimators=100,
    learning_rate=0.1,
    random_state=42,
    n_jobs=-1,
    tree_method='hist'
)
print("\nTraining General Product XGBoost model...")
model_general.fit(X_train_general, y_train_general)
print("General Product Model training complete!")

# Evaluation
y_pred_general = model_general.predict(X_test_general)
y_pred_general[y_pred_general < 0] = 0
mae_general = mean_absolute_error(y_test_general, y_pred_general)
rmse_general = np.sqrt(mean_squared_error(y_test_general, y_pred_general))
r2_general = r2_score(y_test_general, y_pred_general)
print("\n--- General Product Model Evaluation ---")
print(f"MAE: {mae_general:.2f}, RMSE: {rmse_general:.2f}, R2: {r2_general:.2f}")

# Save General Model Components
joblib.dump(model_general, os.path.join(model_dir, 'demand_forecaster_model_general.joblib'))
joblib.dump(encoder_general, os.path.join(model_dir, 'one_hot_encoder_general.joblib'))
joblib.dump(all_feature_names_general, os.path.join(model_dir, 'feature_columns_general.joblib'))
print("General Product ML components saved.")

# Prepare Recent Historical Data for General API
end_date_for_history_general = full_time_series_general['invoice_date'].max()
start_date_for_history_general = end_date_for_history_general - pd.Timedelta(days=60)
recent_history_df_general = full_time_series_general[
    (full_time_series_general['invoice_date'] >= start_date_for_history_general) &
    (full_time_series_general['invoice_date'] <= end_date_for_history_general)
][['invoice_date', 'Product', 'daily_demand']].copy() # No wholesaler_id here
recent_history_df_general.sort_values(by=['invoice_date', 'Product'], inplace=True)
recent_history_df_general.to_csv(os.path.join(model_dir, 'recent_demand_history_general.csv'), index=False)
print("General Product recent historical demand data saved.")

print("\n--- All Data Preparation and Model Training Complete ---")
