import pandas as pd
import numpy as np
from sklearn.linear_model import LinearRegression
import matplotlib.pyplot as plt
# Load retail sales data
df = pd.read_csv('C:/xampp/htdocs/Dairy_Supply_Chain_Management_System/database/seeders/Dataset/retail_sales.csv')

# See the first 5 rows
print("First 5 rows of the dataset:")
print(df.head())
# Step 3: Convert 'invoice_date' to datetime format
df['invoice_date'] = pd.to_datetime(df['invoice_date'], errors='coerce')

# Step 4: Extract month from 'invoice_date'
df['month'] = df['invoice_date'].dt.month

# Step 5: Group data by 'month' and sum 'quantity'
monthly_sales = df.groupby('month')['quantity'].sum()

print("\nTotal Quantity Sold per Month:")
print(monthly_sales)

# Step 6: Prepare data for Machine Learning
X = monthly_sales.index.values.reshape(-1, 1)  # Month numbers as features
y = monthly_sales.values                      # Quantities sold as target

# Step 7: Train Linear Regression Model
model = LinearRegression()
model.fit(X, y)

# Step 8: Predict demand for Month 13 (next month)
next_month = np.array([[13]])  # Predict for month 13
prediction = model.predict(next_month)

print(f"\nPredicted Quantity for Month 13: {prediction[0]:.2f}")

# Step 9: Plot as Bar Chart (Actual + Prediction)
plt.bar(X.flatten(), y, color='skyblue', label='Actual Sales')
plt.bar(13, prediction[0], color='orange', label='Predicted Month 13')

plt.xlabel('Month')
plt.ylabel('Total Quantity Sold')
plt.title('Retail Sales Demand Prediction - Bar Chart')
plt.legend()
plt.xticks(np.arange(1, 14, 1))  # Show months 1 to 13
plt.show()
