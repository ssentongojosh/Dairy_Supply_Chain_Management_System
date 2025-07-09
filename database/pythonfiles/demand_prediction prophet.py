# === Step 0: Import Libraries ===
import pandas as pd
from prophet import Prophet
from sklearn.preprocessing import LabelEncoder
import matplotlib.pyplot as plt

# === Step 1: Load Data ===
df = pd.read_csv('retail_sales.csv')

# === Step 2: Preprocess Data ===
# Ensure date column is datetime
if not pd.api.types.is_datetime64_any_dtype(df['invoice_date']):
    df['invoice_date'] = pd.to_datetime(df['invoice_date'])

# Encode categorical regressors
for col in ['shopping_mall', 'category']:
    le = LabelEncoder()
    df[col] = le.fit_transform(df[col].astype(str))

# Prepare DataFrame for Prophet
prophet_df = df.rename(columns={'invoice_date': 'ds', 'quantity': 'y'})[['ds', 'y', 'shopping_mall', 'category']]

# === Step 3: Initialize Prophet Model ===
model = Prophet()
model.add_regressor('shopping_mall')
model.add_regressor('category')

# === Step 4: Fit Model ===
model.fit(prophet_df)

# === Step 5: Make Future DataFrame ===
# Forecast 30 periods ahead (adjust as needed)
future = model.make_future_dataframe(periods=30, freq='D')

# For regressors, fill with most common value (or adjust as needed)
for col in ['shopping_mall', 'category']:
    # Ensure we use pandas Series for mode()
    mode_val = pd.Series(prophet_df[col]).mode()[0]
    future[col] = int(mode_val)

# === Step 6: Predict ===
forecast = model.predict(future)

# === Step 7: Plot Forecast ===
fig1 = model.plot(forecast)
plt.title('Prophet Forecast: Quantity')
plt.show()

fig2 = model.plot_components(forecast)
plt.show()

print('Forecasting complete. See plots for results.')
