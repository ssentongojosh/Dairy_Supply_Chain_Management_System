import pandas as pd
import numpy as np

# Number of fake businesses
N = 10000

# Generate fake business data
np.random.seed(42)
df = pd.DataFrame({
    'customer_id': np.arange(1, N+1),
    'business_type': np.random.choice(['Wholesaler', 'Retailer'], size=N),
    'location': np.random.choice(['Kampala', 'Mbarara', 'Gulu', 'Mbale', 'Jinja'], size=N),
    'annual_revenue': np.random.randint(5_000_000, 100_000_000, size=N),
    'order_frequency': np.random.randint(1, 20, size=N),  # orders per month
    'total_quantity_purchased': np.random.randint(50, 10000, size=N),
    'product': np.random.choice(['Milk', 'Yogurt', 'Butter', 'Cheese', 'Flavored Milk', 'Powder Milk', 'Cream'], size=N),
    'quantity': np.random.randint(10, 200, size=N)
})

df.to_csv('customer_segmentation_data_business.csv', index=False)
print("✅ Generated 10,000-row business dataset: customer_segmentation_data_business.csv")
