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
plt.xlabel("Sample Index")
plt.ylabel("Quantity Sold")
plt.title("📊 Actual vs Predicted Quantity Sold (Random Forest)")
plt.legend()
plt.tight_layout()
plt.show()



print("\n 🎉🤩🤩🤗Demand Prediction Completed Successfully! 🎉🤩🤩🤗")
