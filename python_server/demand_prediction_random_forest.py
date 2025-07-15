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
file_path = 'C:/xampp/htdocs/Dairy_Supply_Chain_Management_System/database/seeders/Dataset/retail_sales.csv'
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

# Encode only 'Product' and 'payment_method'
label_encoders = {}
categorical_columns = ['Product', 'payment_method']

for column in categorical_columns:
    le = LabelEncoder()
    df[column] = le.fit_transform(df[column])
    label_encoders[column] = le  # Save for future decoding if needed

print("🔹 Preparing features and target...")

# Define Features (X) and Target (y)
X = df[['Product', 'payment_method']]
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

# === Product-wise Prediction Plot ===
print("📊 Creating bar chart for predicted quantity per product...")
X_test_with_preds = X_test.copy()
X_test_with_preds['predicted_quantity'] = y_pred_rounded

# Group by Product and sum predicted quantities
product_comparison = X_test_with_preds.groupby('Product')['predicted_quantity'].sum()
# Decode product labels for x-axis
product_names = label_encoders['Product'].inverse_transform(product_comparison.index)

plt.figure(figsize=(16, 6))
plt.bar(product_names, product_comparison.values, color='orange')
plt.xlabel("Product")
plt.ylabel("Predicted Quantity Sold")
plt.title("Predicted Quantity Sold per Product")
plt.xticks(rotation=45)
plt.tight_layout()
plt.show()

# === Daily Prediction Section (Predicted Only) ===
print("\n=== Daily Prediction: Predicted Total Quantity Sold Each Day ===")
# For daily, we need the date info from the original df
X_test_with_preds['invoice_date'] = df.loc[X_test_with_preds.index, 'invoice_date'].values

daily_comparison = X_test_with_preds.groupby('invoice_date')['predicted_quantity'].sum()

plt.figure(figsize=(16, 6))
plt.plot(daily_comparison.index, daily_comparison.values, label='Predicted', marker='x', linestyle='--', color='orange')
plt.xlabel("Date")
plt.ylabel("Total Predicted Quantity Sold")
plt.title("Daily: Predicted Quantity Sold")
plt.legend()
plt.tight_layout()
plt.show()

# === Monthly Prediction Section (Predicted Only) ===
print("\n=== Monthly Prediction: Predicted Total Quantity Sold Each Month ===")
X_test_with_preds['Month'] = df.loc[X_test_with_preds.index, 'Month'].values
X_test_with_preds['Year'] = df.loc[X_test_with_preds.index, 'Year'].values

monthly_comparison = X_test_with_preds.groupby(['Year', 'Month'])['predicted_quantity'].sum()

plt.figure(figsize=(10, 6))
bar_width = 0.35
months = range(len(monthly_comparison))
plt.bar(months, monthly_comparison.values, width=bar_width, label='Predicted', color='orange')
plt.xlabel("Year, Month")
plt.ylabel("Total Predicted Quantity Sold")
plt.title("Monthly: Predicted Quantity Sold")
plt.xticks(months, [f"{idx[0]}-{idx[1]:02d}" for idx in monthly_comparison.index], rotation=45)
plt.legend()
plt.tight_layout()
plt.show()

# === Yearly Prediction Section (Predicted Only) ===
print("\n=== Yearly Prediction: Predicted Total Quantity Sold Each Year ===")
yearly_comparison = X_test_with_preds.groupby('Year')['predicted_quantity'].sum()

plt.figure(figsize=(8, 5))
plt.bar(yearly_comparison.index, yearly_comparison.values, width=0.3, label='Predicted', color='orange')
plt.xlabel("Year")
plt.ylabel("Total Predicted Quantity Sold")
plt.title("Yearly: Predicted Quantity Sold")
plt.legend()
plt.tight_layout()
plt.show()

print("\n 🎉🤩🤩🤗Demand Prediction Completed Successfully! 🎉🤩🤩🤗")
