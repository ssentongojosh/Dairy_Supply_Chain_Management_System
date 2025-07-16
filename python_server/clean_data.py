import pandas as pd,pandas
from io import StringIO

with open("../database/seeders/Dataset/retail_sales.csv",'r') as file:
    data = file.read()

df = pandas.read_csv(StringIO(data))

# convert 'invoice_date' to datetime format
df['invoice_date'] = pandas.to_datetime(df['invoice_date'], format='%d/%m/%Y')
print("DataFrame after initial load and date conversion:")
print(df.head())
print("\nDataFrame Info (check data types):")
print(df.info())

# Aggregate Quantity by Date, Product, and Shopping Mall
daily_demand = df.groupby(['invoice_date', 'Product', 'shopping_mall'])['quantity'].sum().reset_index()

# Rename the 'quantity' column to something more descriptive like 'daily_demand'
daily_demand.rename(columns={'quantity': 'daily_demand'}, inplace=True)

# Sort by date to ensure the time series is in order
daily_demand.sort_values(by=['invoice_date', 'Product', 'shopping_mall'], inplace=True)

print("Aggregated Daily Demand Data:")
print(daily_demand.head())
print("\nDataFrame Info (check new structure):")
print(daily_demand.info())
print("\nNumber of unique Product-Mall combinations (our individual time series):")
print(daily_demand[['Product', 'shopping_mall']].drop_duplicates().shape[0])

# Fill Missing Dates with Zero Demand (Ensuring Continuous Series)

unique_series = daily_demand[['Product', 'shopping_mall']].drop_duplicates().set_index(['Product', 'shopping_mall'])

# Get the full date range across the entire dataset
min_date = daily_demand['invoice_date'].min()
max_date = daily_demand['invoice_date'].max()
full_date_range = pd.date_range(start=min_date, end=max_date, freq='D') # 'D' for Daily frequency

# Create a complete DataFrame structure for all Product-Mall combinations across the full date range
# This will create a grid of all possible (date, product, mall) combinations
all_combinations = pd.MultiIndex.from_product(
    [full_date_range, daily_demand['Product'].unique(), daily_demand['shopping_mall'].unique()],
    names=['invoice_date', 'Product', 'shopping_mall']
).to_frame(index=False)

# Merge our existing daily_demand with the full combinations.
# This will align the actual demand values with the complete date range,
# introducing NaNs where there were no sales on a given day for a given product/mall.
full_time_series = pd.merge(
    all_combinations,
    daily_demand,
    on=['invoice_date', 'Product', 'shopping_mall'],
    how='left'
)

# Fill NaN values in 'daily_demand' with 0
full_time_series['daily_demand'].fillna(0, inplace=True)

# Ensure the DataFrame is sorted by Product, shopping_mall, and then invoice_date for proper time series order
full_time_series.sort_values(by=['Product', 'shopping_mall', 'invoice_date'], inplace=True)

# Reset index for a clean DataFrame
full_time_series.reset_index(drop=True, inplace=True)


print("Full Time Series Data (with zeros for missing dates):")
# Show a slice to demonstrate the effect, e.g., for 'Butter' in 'Cevahir AVM'
# It's hard to demonstrate with a simple head() unless that combination has missing dates early on.
# Let's show a specific product-mall combo
example_series = full_time_series[
    (full_time_series['Product'] == 'Butter') &
    (full_time_series['shopping_mall'] == 'Cevahir AVM')
].head(10) # Show first 10 entries for this specific series

print(example_series)
print("\nFull Time Series Info (check new size):")
print(full_time_series.info())

# To verify if zeros were filled
print(f"\nNumber of entries before filling NaNs: {daily_demand.shape[0]}")
print(f"Number of entries after filling NaNs: {full_time_series.shape[0]}")
print(f"Any NaN values in daily_demand column after fillna: {full_time_series['daily_demand'].isnull().any()}")

# Make a copy to ensure we're working on a mutable DataFrame
processed_df = full_time_series.copy()

# --- 4.1 Time-Based Features ---
processed_df['year'] = processed_df['invoice_date'].dt.year
processed_df['month'] = processed_df['invoice_date'].dt.month
processed_df['day'] = processed_df['invoice_date'].dt.day
processed_df['day_of_week'] = processed_df['invoice_date'].dt.dayofweek # Monday=0, Sunday=6
processed_df['day_of_year'] = processed_df['invoice_date'].dt.dayofyear
processed_df['week_of_year'] = processed_df['invoice_date'].dt.isocalendar().week.astype(int)
processed_df['quarter'] = processed_df['invoice_date'].dt.quarter

# --- 4.2 Lag Features (Autocorrelation) ---
# We need to create lags for each individual time series (Product-shopping_mall combination)
# Group by 'Product' and 'shopping_mall' and then apply shift()
# Common lags: 1 day, 7 days (weekly seasonality), 30 days (monthly pattern)
# You can add more lags as needed
lags = [1, 7, 14, 30] # Lag for 1 day, 1 week, 2 weeks, 1 month

for lag in lags:
    processed_df[f'demand_lag_{lag}d'] = processed_df.groupby(['Product', 'shopping_mall'])['daily_demand'].shift(lag)

# --- Handling NaNs created by lag features ---
# Lag features will have NaN values at the beginning of each series (e.g., demand_lag_7d will be NaN for the first 7 days)
# For forecasting, it's common to fill these with 0 or the mean/median, or just drop rows (if enough data)
# For simplicity and to retain more data, we'll fill with 0 for now.
# This assumes that before the first recorded sale, demand was effectively zero.
for lag in lags:
    processed_df[f'demand_lag_{lag}d'].fillna(0, inplace=True) # Using inplace=True for simplicity, noting the FutureWarnings.


print("DataFrame after Feature Engineering (Time-Based & Lag Features):")
print(processed_df.head(15)) # Showing more rows to see some lags fill in
print("\nDataFrame Info (check new columns):")
print(processed_df.info())
print("\nDescription of new lag features (should show min=0):")
print(processed_df[[f'demand_lag_{lag}d' for lag in lags]].describe())

# Make a copy if you haven't recently, to ensure modifications
# processed_df = full_time_series.copy() # Only if you restart from full_time_series

# --- 5.1 Rolling Window Features ---
# Common rolling window sizes: 7 days (weekly average), 30 days (monthly average)
rolling_windows = [7, 30]

for window in rolling_windows:
    # Calculate rolling mean for daily_demand within each Product-Mall group
    processed_df[f'rolling_mean_{window}d'] = processed_df.groupby(['Product', 'shopping_mall'])['daily_demand'].transform(
        lambda x: x.rolling(window=window, min_periods=1).mean() # min_periods=1 allows calculation with fewer than 'window' points at the start
    )
    # Calculate rolling standard deviation (optional, but good for volatility)
    processed_df[f'rolling_std_{window}d'] = processed_df.groupby(['Product', 'shopping_mall'])['daily_demand'].transform(
        lambda x: x.rolling(window=window, min_periods=1).std()
    )
    # Fill NaNs created by rolling_std (if min_periods=1 and only one value, std is NaN)
    processed_df[f'rolling_std_{window}d'].fillna(0, inplace=True) # Assuming no variance if only one data point

print("\nDataFrame after Rolling Window Features:")
print(processed_df.head(15))
print("\nDataFrame Info (check new rolling columns):")
print(processed_df.info())
print("\nDescription of new rolling features:")
print(processed_df[[f'rolling_mean_{window}d' for window in rolling_windows] + [f'rolling_std_{window}d' for window in rolling_windows]].describe())

from sklearn.preprocessing import OneHotEncoder

# --- 6.1 Identify Categorical Columns to Encode ---
categorical_cols = ['Product', 'shopping_mall']

# --- 6.2 Initialize OneHotEncoder ---
# handle_unknown='ignore' is good practice: if a new category appears during prediction, it won't throw an error.
encoder = OneHotEncoder(handle_unknown='ignore', sparse_output=False) # sparse_output=False returns a dense NumPy array

# --- 6.3 Fit and Transform the Categorical Data ---
# Fit the encoder on the selected categorical columns from your processed_df
encoded_features = encoder.fit_transform(processed_df[categorical_cols])

# --- 6.4 Create a DataFrame from the Encoded Features ---
# Get the new column names for the encoded features
encoded_feature_names = encoder.get_feature_names_out(categorical_cols)
encoded_df = pd.DataFrame(encoded_features, columns=encoded_feature_names, index=processed_df.index)

# --- 6.5 Concatenate Encoded Features with the Original DataFrame ---
# Drop the original categorical columns from processed_df
processed_df = processed_df.drop(columns=categorical_cols)
# Concatenate the new encoded DataFrame
processed_df = pd.concat([processed_df, encoded_df], axis=1)

print("\nDataFrame after One-Hot Encoding:")
print(processed_df.head())
print("\nDataFrame Info (check new encoded columns and dropped original categoricals):")
print(processed_df.info())
print(f"\nNumber of columns after encoding: {processed_df.shape[1]}")

# Define features (X) and target (y)
# Drop invoice_date as it's been used to create time-based features
# daily_demand is our target variable
X = processed_df.drop(columns=['invoice_date', 'daily_demand'])
y = processed_df['daily_demand']

print("Features (X) - first 5 rows:")
print(X.head())
print("\nTarget (y) - first 5 values:")
print(y.head())
print(f"\nShape of X: {X.shape}")
print(f"Shape of y: {y.shape}")

# Determine the split point (e.g., use 80% of data for training, 20% for testing)
# A common approach is to pick a date. Let's say, everything before a certain date for training.
# Or, based on the total number of entries, calculate a split index.

# Sort by invoice_date to ensure chronological order for splitting
# (although we sorted before, good to re-confirm before splitting)
processed_df.sort_values(by='invoice_date', inplace=True)

# Calculate the split point - e.g., 80% for training
# Ensure X and y are ordered by invoice_date before splitting
split_index = int(len(processed_df) * 0.8)

# Split the data
X_train = X.iloc[:split_index]
X_test = X.iloc[split_index:]
y_train = y.iloc[:split_index]
y_test = y.iloc[split_index:]

# Get the actual split date for clarity
split_date = processed_df['invoice_date'].iloc[split_index]

print(f"\nData Split Information:")
print(f"Total data points: {len(processed_df)}")
print(f"Training data points: {len(X_train)} (up to date: {processed_df['invoice_date'].iloc[split_index-1].strftime('%Y-%m-%d')})")
print(f"Testing data points: {len(X_test)} (starting from date: {split_date.strftime('%Y-%m-%d')})")

print("\nX_train shape:", X_train.shape)
print("y_train shape:", y_train.shape)
print("X_test shape:", X_test.shape)
print("y_test shape:", y_test.shape)


from sklearn.ensemble import HistGradientBoostingRegressor
from sklearn.metrics import mean_absolute_error, mean_squared_error, r2_score
import numpy as np # For sqrt in RMSE

print("--- Starting Model Training and Evaluation ---")

# --- 9.1 Model Selection ---
# Initialize the HistGradientBoostingRegressor
# You can tune parameters later, but let's start with sensible defaults
model = HistGradientBoostingRegressor(random_state=42,  # for reproducibility
                                      max_iter=100,      # Number of boosting stages
                                      learning_rate=0.1) # Step size shrinkage

print(f"\nSelected Model: {type(model).__name__}")

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

# Ensure predictions are non-negative, as demand cannot be negative
y_pred[y_pred < 0] = 0

mae = mean_absolute_error(y_test, y_pred)
rmse = np.sqrt(mean_squared_error(y_test, y_pred))
r2 = r2_score(y_test, y_pred)

print(f"Mean Absolute Error (MAE): {mae:.2f}")
print(f"Root Mean Squared Error (RMSE): {rmse:.2f}")
print(f"R-squared (R2 Score): {r2:.2f}")

print("\n--- Model Training and Evaluation Complete ---")

import joblib
import os # For creating directories

# Define a directory to save the model and preprocessors
model_dir = 'ml_models'
os.makedirs(model_dir, exist_ok=True) # Create the directory if it doesn't exist

# --- Save the trained model ---
model_path = os.path.join(model_dir, 'demand_forecaster_model.joblib')
joblib.dump(model, model_path)
print(f"Model saved to: {model_path}")

# --- Save the OneHotEncoder ---
encoder_path = os.path.join(model_dir, 'one_hot_encoder.joblib')
joblib.dump(encoder, encoder_path)
print(f"OneHotEncoder saved to: {encoder_path}")

# --- Save the list of feature column names ---
# This is crucial for ensuring new input data is correctly ordered
feature_names_path = os.path.join(model_dir, 'feature_columns.joblib')
joblib.dump(X.columns.tolist(), feature_names_path)
print(f"Feature column names saved to: {feature_names_path}")

print("\nAll necessary ML components saved for deployment.")

# In your Jupyter notebook or script where you did the data processing:
# Make sure processed_df is still available and sorted by invoice_date

# Determine how much recent history you need.
# If your longest lag/rolling window is 30 days, you might want ~60 days of recent history
# to ensure you have enough data points.
# Let's get the last 60 days of unique daily_demand records for each Product/Mall combination
# (or simply the last N rows of processed_df if it's already grouped by unique product/mall for each day)

# To ensure we capture the *actual* daily_demand for each Product/Mall combination
# for calculating lags correctly, let's filter processed_df for recent dates
# and only keep the essential columns for history lookup.

# Get the last 60 days of data from your full processed_df
# Assuming processed_df is sorted by 'invoice_date'
# Check what columns are available in processed_df
print("Available columns in processed_df:")
print(processed_df.columns.tolist())

# Get the last 60 days of data from your full processed_df
# Assuming processed_df is sorted by 'invoice_date'
end_date_for_history = processed_df['invoice_date'].max()
start_date_for_history = end_date_for_history - pd.Timedelta(days=60)

# Since Product and shopping_mall were one-hot encoded, we need to reconstruct them
# or use the original full_time_series DataFrame that still has these columns
print("\nUsing full_time_series DataFrame to extract recent history...")

# Filter for the relevant period and columns for our history cache using the original data
recent_history_df = full_time_series[
    (full_time_series['invoice_date'] >= start_date_for_history) &
    (full_time_series['invoice_date'] <= end_date_for_history)
][['invoice_date', 'Product', 'shopping_mall', 'daily_demand']].copy()

# Ensure it's sorted by date for easy lookup
recent_history_df.sort_values(by=['invoice_date', 'Product', 'shopping_mall'], inplace=True)

# Save this small DataFrame
history_path = os.path.join(model_dir, 'recent_demand_history.csv')
recent_history_df.to_csv(history_path, index=False)
print(f"\nRecent historical demand data saved to: {history_path}")

print("Proceeding to modify the Flask application...")
