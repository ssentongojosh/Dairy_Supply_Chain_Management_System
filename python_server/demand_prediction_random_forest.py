# demand_prediction_random_forest.py

import pandas as pd
import numpy as np
import matplotlib.pyplot as plt
from sklearn.model_selection import train_test_split
from sklearn.ensemble import RandomForestRegressor
from sklearn.preprocessing import LabelEncoder
from sklearn.metrics import mean_squared_error, r2_score

print("🔹 Loading retail sales data...")

# Load the dataset
file_path = '../database/seeders/Dataset/retail_sales.csv'
df = pd.read_csv(file_path)

print("🔹 Cleaning data...")

# Drop rows with missing values
df.dropna(inplace=True)

# Convert invoice_date to datetime format
df['invoice_date'] = pd.to_datetime(df['invoice_date'], dayfirst=True)

# Extract month and year for better trend learning
df['Month'] = df['invoice_date'].dt.month
df['Year'] = df['invoice_date'].dt.year

print("🔹 Encoding categorical variables...")

# Encode categorical variables
label_encoders = {}
categorical_columns = ['gender', 'category', 'payment_method', 'shopping_mall']

for column in categorical_columns:
    le = LabelEncoder()
    df[column] = le.fit_transform(df[column])
    label_encoders[column] = le  # Save for future decoding if needed

print("🔹 Preparing features and target...")

# Define Features (X) and Target (y)
X = df[['Month', 'Year', 'age', 'category', 'payment_method', 'shopping_mall']]
y = df['quantity']

print("🔹 Splitting into train and test sets...")

# Split the data into training (70%) and testing (30%) sets
X_train, X_test, y_train, y_test = train_test_split(
    X, y, test_size=0.3, random_state=42, shuffle=True
)

print("🌲 Training Random Forest Regressor...")

# Initialize and train the model
model = RandomForestRegressor(n_estimators=100, random_state=42)
model.fit(X_train, y_train)

print("🔍 Making predictions...")

# Predict using the test set
y_pred = model.predict(X_test)

# Round predictions for easier comparison
y_pred_rounded = np.round(y_pred)

print("\n📊 Model Evaluation:")
mse = mean_squared_error(y_test, y_pred_rounded)
r2 = r2_score(y_test, y_pred_rounded)
print("Mean Squared Error (MSE):", mse)
print("R² Score:", r2)

print("📊 Creating bar chart for first 20 predictions...")

# Compare first 20 predictions visually
plt.figure(figsize=(16, 6))
plt.bar(range(20), y_test.iloc[:20], label='Actual', width=0.4, align='edge')
plt.bar(range(20), y_pred_rounded[:20], label='Predicted', width=-0.4, align='edge', color='orange')
plt.xlabel("Units")
plt.ylabel("Quantity Sold")
plt.title("📊 Actual vs Predicted Quantity Sold (Random Forest)")
plt.legend()
plt.tight_layout()
plt.show()

# === New: Actual vs Predicted Quantity Sold by Month ===
print("📊 Creating bar chart for actual vs predicted quantity by month...")

# Add predictions and actuals to X_test for grouping
X_test_with_preds = X_test.copy()
X_test_with_preds['actual_quantity'] = y_test
X_test_with_preds['predicted_quantity'] = y_pred_rounded

# Group by Month and sum
monthly_comparison = X_test_with_preds.groupby('Month')[['actual_quantity', 'predicted_quantity']].sum()

# Plot actual vs predicted by month
plt.figure(figsize=(10, 6))
plt.bar(monthly_comparison.index - 0.2, monthly_comparison['actual_quantity'], width=0.4, label='Actual')
plt.bar(monthly_comparison.index + 0.2, monthly_comparison['predicted_quantity'], width=0.4, label='Predicted', color='orange')
plt.xlabel("Month")
plt.ylabel("Total Quantity Sold")
plt.title("Actual vs Predicted Quantity Sold by Month")
plt.legend()
plt.tight_layout()
plt.show()

# === New: Add predictions and actuals to X_test for grouping and time-based analysis ===
print("\n🔹 Preparing data for time-based predictions and visualizations...")

# Add predictions and actuals to X_test for grouping
X_test_with_preds = X_test.copy()
X_test_with_preds['actual_quantity'] = y_test.values
X_test_with_preds['predicted_quantity'] = y_pred_rounded

# Add back the date columns for grouping
X_test_with_preds['invoice_date'] = df.loc[X_test_with_preds.index, 'invoice_date']
X_test_with_preds['Year'] = df.loc[X_test_with_preds.index, 'Year']
X_test_with_preds['Month'] = df.loc[X_test_with_preds.index, 'Month']

# =========================
# DAILY PREDICTION SECTION
# =========================
print("\n=== Daily Prediction: Actual vs Predicted Total Quantity Sold Each Day ===")
daily_comparison = X_test_with_preds.groupby('invoice_date')[['actual_quantity', 'predicted_quantity']].sum()

# Print summary table (first 10 days for brevity)
print("\nDaily summary (first 10 days):")
print(daily_comparison.head(10))

# Plot daily prediction (Predicted only)
plt.figure(figsize=(16, 6))
plt.plot(daily_comparison.index, daily_comparison['predicted_quantity'], label='Predicted', marker='x', linestyle='--', color='orange')
plt.xlabel("Date")
plt.ylabel("Total Quantity Sold")
plt.title("Daily: Predicted Quantity Sold")
plt.legend()
plt.tight_layout()
plt.show()

# =========================
# MONTHLY PREDICTION SECTION
# =========================
print("\n=== Monthly Prediction: Actual vs Predicted Total Quantity Sold Each Month ===")
monthly_comparison = X_test_with_preds.groupby(['Year', 'Month'])[['actual_quantity', 'predicted_quantity']].sum()
print("\nMonthly summary:")
print(monthly_comparison)

# Plot monthly prediction (Predicted only)
plt.figure(figsize=(10, 6))
bar_width = 0.35
months = range(len(monthly_comparison))
plt.bar(months, monthly_comparison['predicted_quantity'], width=bar_width, label='Predicted', color='orange')
plt.xlabel("Year, Month")
plt.ylabel("Total Quantity Sold")
plt.title("Monthly: Predicted Quantity Sold")
plt.xticks(months, [f"{idx[0]}-{idx[1]:02d}" for idx in monthly_comparison.index], rotation=45)
plt.legend()
plt.tight_layout()
plt.show()

# =========================
# YEARLY PREDICTION SECTION
# =========================
print("\n=== Yearly Prediction: Actual vs Predicted Total Quantity Sold Each Year ===")
yearly_comparison = X_test_with_preds.groupby('Year')[['actual_quantity', 'predicted_quantity']].sum()
print("\nYearly summary:")
print(yearly_comparison)

# Plot yearly prediction (Predicted only)
plt.figure(figsize=(8, 5))
plt.bar(yearly_comparison.index, yearly_comparison['predicted_quantity'], width=0.3, label='Predicted', color='orange')
plt.xlabel("Year")
plt.ylabel("Total Quantity Sold")
plt.title("Yearly: Predicted Quantity Sold")
plt.legend()
plt.tight_layout()
plt.show()

print("\n 🎉🤩🤩🤗Demand Prediction Completed Successfully! 🎉🤩🤩🤗")
