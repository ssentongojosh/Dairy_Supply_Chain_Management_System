import os
import pandas as pd
import matplotlib.pyplot as plt
import seaborn as sns
from sklearn.cluster import KMeans
from sklearn.preprocessing import StandardScaler, LabelEncoder

# === 1. Load Data ===
<<<<<<< HEAD
<<<<<<< HEAD
DATA_PATH = 'C:/xampp/htdocs/Dairy_Supply_Chain_Management_System/database/seeders/Dataset/customer_segmentation_data_business.csv'
=======
DATA_PATH = '../database/seeders/Dataset/customer_segmentation_data.csv'
>>>>>>> origin/main
=======
DATA_PATH = '../database/seeders/Dataset/customer_segmentation_data.csv'
>>>>>>> origin/main
df = pd.read_csv(DATA_PATH)

# === 1b. Set output directory for graphs and summary ===
OUTPUT_DIR = 'C:/xampp/htdocs/Dairy_Supply_Chain_Management_System/storage/app/public'
os.makedirs(OUTPUT_DIR, exist_ok=True)  # Create the folder if it doesn't exist

# === 2. Encode categorical variables ===
label_encoders = {}
categorical_columns = ['location', 'business_type']

for column in categorical_columns:
    le = LabelEncoder()
    df[column] = le.fit_transform(df[column])
    label_encoders[column] = le  # Save for future decoding if needed

# === 3. KMeans Clustering to create 'segment' column ===
# Business-focused features
feature_cols = ['annual_revenue', 'order_frequency', 'total_quantity_purchased', 'location', 'business_type']
features = df[feature_cols]
scaler = StandardScaler()
scaled_features = scaler.fit_transform(features)

kmeans = KMeans(n_clusters=5, random_state=42)
df['cluster'] = kmeans.fit_predict(scaled_features)

# Print cluster means to help decide labels
cluster_means = df.groupby('cluster')[['annual_revenue', 'order_frequency', 'total_quantity_purchased']].mean().round(1)
print("\nCluster Averages (use these to decide labels):")
print(cluster_means)

# Assign human-readable labels to each cluster (edit as needed after seeing means)
cluster_labels = {
    0: 'Small Business',
    1: 'Medium Business',
    2: 'Large Business',
    3: 'High Frequency Business',
    4: 'Premium Business'
}
df['segment'] = df['cluster'].replace(cluster_labels)

# === 4. Number of Businesses per Segment ===
plt.figure(figsize=(8, 5))
sns.countplot(data=df, x='segment', order=sorted(df['segment'].unique()))
plt.title('Number of Businesses per Segment')
plt.xlabel('Segment')
plt.ylabel('Number of Businesses')
plt.tight_layout()
plt.savefig(os.path.join(OUTPUT_DIR, 'businesses_per_segment.png'))
plt.close()

# === 5. Average Annual Revenue per Segment ===
plt.figure(figsize=(8, 5))
sns.barplot(data=df, x='segment', y='annual_revenue', estimator='mean', ci=None, order=sorted(df['segment'].unique()))
plt.title('Average Annual Revenue per Segment')
plt.xlabel('Segment')
plt.ylabel('Average Annual Revenue')
plt.tight_layout()
plt.savefig(os.path.join(OUTPUT_DIR, 'avg_revenue_per_segment.png'))
plt.close()

# === 6. Order Frequency by Segment ===
plt.figure(figsize=(8, 5))
sns.barplot(data=df, x='segment', y='order_frequency', estimator='mean', ci=None, order=sorted(df['segment'].unique()))
plt.title('Average Order Frequency per Segment')
plt.xlabel('Segment')
plt.ylabel('Average Order Frequency')
plt.tight_layout()
plt.savefig(os.path.join(OUTPUT_DIR, 'avg_order_frequency_per_segment.png'))
plt.close()

# === 7. Total Quantity Purchased by Segment ===
plt.figure(figsize=(8, 5))
sns.barplot(data=df, x='segment', y='total_quantity_purchased', estimator='mean', ci=None, order=sorted(df['segment'].unique()))
plt.title('Average Total Quantity Purchased per Segment')
plt.xlabel('Segment')
plt.ylabel('Average Total Quantity Purchased')
plt.tight_layout()
plt.savefig(os.path.join(OUTPUT_DIR, 'avg_quantity_per_segment.png'))
plt.close()

# === 8. Business Types by Segment ===
plt.figure(figsize=(12, 6))
# Decode business_type for visualization
df['business_type_decoded'] = label_encoders['business_type'].inverse_transform(df['business_type'])
sns.countplot(data=df, x='business_type_decoded', hue='segment', order=sorted(df['business_type_decoded'].unique()))
plt.title('Segments by Business Type')
plt.xlabel('Business Type')
plt.ylabel('Number of Businesses')
plt.legend(title='Segment')
plt.xticks(rotation=45)
plt.tight_layout()
plt.savefig(os.path.join(OUTPUT_DIR, 'segments_by_business_type.png'))
plt.close()

# === 9. Summary Table: Business Metrics per Segment ===
summary = df.groupby('segment').agg(
    avg_revenue=('annual_revenue', 'mean'),
    avg_order_frequency=('order_frequency', 'mean'),
    avg_quantity=('total_quantity_purchased', 'mean'),
    count=('customer_id', 'count'),
    most_common_product=('product', lambda x: x.mode()[0] if len(x.mode()) > 0 else None)
).round(1)

summary.to_csv(os.path.join(OUTPUT_DIR, 'business_segment_summary.csv'))

# === 10. Top 3 Products per Segment ===
# Decode products if needed
df['product_decoded'] = df['product']  # Assuming product is already readable

top_products = (
    df.groupby('segment')['product_decoded']
    .apply(lambda x: x.value_counts().head(3).index.tolist())
    .reset_index()
)

# Expand the top 3 into separate columns
top_products[['top1', 'top2', 'top3']] = pd.DataFrame(top_products['product_decoded'].tolist(), index=top_products.index)
top_products = top_products.drop(columns=['product_decoded'])

# Save to CSV
top_products.to_csv(os.path.join(OUTPUT_DIR, 'business_segment_top3_products.csv'), index=False)
print("\nTop 3 products per business segment saved to:", os.path.join(OUTPUT_DIR, 'business_segment_top3_products.csv'))

print(f"\nAll charts and summary table saved in: {os.path.abspath(OUTPUT_DIR)}\nYou can tweak groupings or plots by editing this script!\n")
print(summary)

# === 11. Product Distribution by Segment Plot ===
plt.figure(figsize=(14, 7))
sns.countplot(data=df, x='product_decoded', hue='segment')
plt.title('Business Segment Distribution for Each Product')
plt.xlabel('Product')
plt.ylabel('Number of Businesses')
plt.legend(title='Segment')
plt.xticks(rotation=45)
plt.tight_layout()
plt.savefig(os.path.join(OUTPUT_DIR, 'business_segment_distribution_by_product.png'))
plt.close()

# === 12. Product-Segment Counts Table ===
product_segment_counts = df.groupby(['product_decoded', 'segment']).size().unstack(fill_value=0)
product_segment_counts.to_csv(os.path.join(OUTPUT_DIR, 'business_product_segment_counts.csv'))
