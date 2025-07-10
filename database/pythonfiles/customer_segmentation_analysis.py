import os
import pandas as pd
import matplotlib.pyplot as plt
import seaborn as sns
from sklearn.cluster import KMeans
from sklearn.preprocessing import StandardScaler, LabelEncoder

# === 1. Load Data ===
DATA_PATH = 'C:/xampp/htdocs/Dairy_Supply_Chain_Management_System/database/seeders/Dataset/customer_segmentation_data.csv'
df = pd.read_csv(DATA_PATH)

# === 1b. Set output directory for graphs and summary ===
OUTPUT_DIR = os.path.join('database', 'pythonfiles', 'graphs')
os.makedirs(OUTPUT_DIR, exist_ok=True)  # Create the folder if it doesn't exist

# === 1c. Encode Product column if it exists and is categorical ===
if 'Product' in df.columns:
    if df['Product'].dtype == object:
        product_encoder = LabelEncoder()
        df['Product'] = product_encoder.fit_transform(df['Product'])

# === 2. Encode Gender column (if not already numeric) ===
if df['Gender'].dtype == object:
    label_encoder = LabelEncoder()
    df['Gender'] = label_encoder.fit_transform(df['Gender'])

# === 3. KMeans Clustering to create 'segment' column ===
# Add 'Product' to features if it exists
feature_cols = ['Age', 'Gender', 'Annual Income', 'Spending Score']
if 'Product' in df.columns:
    feature_cols.append('Product')
features = df[feature_cols]
scaler = StandardScaler()
scaled_features = scaler.fit_transform(features)

kmeans = KMeans(n_clusters=5, random_state=42)
df['cluster'] = kmeans.fit_predict(scaled_features)

# Print cluster means to help decide labels
cluster_means = df.groupby('cluster')[['Age', 'Annual Income', 'Spending Score']].mean().round(1)
print("\nCluster Averages (use these to decide labels):")
print(cluster_means)

# Assign human-readable labels to each cluster (edit as needed after seeing means)
cluster_labels = {
    0: 'Young Savers',
    1: 'Middle Age Spenders',
    2: 'Middle Age Savers',
    3: 'Middle Age Average Income Spenders',
    4: 'Older Spenders'
}
df['segment'] = df['cluster'].replace(cluster_labels)

# === 4. Helper: Age Grouping ===
def assign_age_group(age):
    if age < 20:
        return 'Teen'
    elif age < 40:
        return 'Adult'
    else:
        return 'Senior'

df['age_group'] = df['Age'].apply(assign_age_group)

# === 5. Number of Customers per Segment ===
plt.figure(figsize=(8, 5))
sns.countplot(data=df, x='segment', order=sorted(df['segment'].unique()))
plt.title('Number of Customers per Segment')
plt.xlabel('Segment')
plt.ylabel('Number of Customers')
plt.tight_layout()
plt.savefig(os.path.join(OUTPUT_DIR, 'customers_per_segment.png'))
plt.close()

# === 6. Average Spending Score per Segment ===
plt.figure(figsize=(8, 5))
sns.barplot(data=df, x='segment', y='Spending Score', estimator='mean', ci=None, order=sorted(df['segment'].unique()))
plt.title('Average Spending Score per Segment')
plt.xlabel('Segment')
plt.ylabel('Average Spending Score')
plt.tight_layout()
plt.savefig(os.path.join(OUTPUT_DIR, 'avg_spending_per_segment.png'))
plt.close()

# === 7. Spending Score by Segment and Gender ===
plt.figure(figsize=(10, 6))
sns.boxplot(data=df, x='segment', y='Spending Score', hue='Gender', order=sorted(df['segment'].unique()))
plt.title('Spending Score by Segment and Gender')
plt.xlabel('Segment')
plt.ylabel('Spending Score')
plt.legend(title='Gender')
plt.tight_layout()
plt.savefig(os.path.join(OUTPUT_DIR, 'spending_by_segment_gender.png'))
plt.close()

# === 8. Average Spending Score by Age Group ===
plt.figure(figsize=(8, 5))
sns.barplot(data=df, x='age_group', y='Spending Score', estimator='mean', ci=None, order=['Teen', 'Adult', 'Senior'])
plt.title('Average Spending Score by Age Group')
plt.xlabel('Age Group')
plt.ylabel('Average Spending Score')
plt.tight_layout()
plt.savefig(os.path.join(OUTPUT_DIR, 'avg_spending_by_age_group.png'))
plt.close()

# === 9. Segments by Shopping Mall ===
plt.figure(figsize=(12, 6))
sns.countplot(data=df, x='shopping_mall', hue='segment', order=sorted(df['shopping_mall'].unique()))
plt.title('Segments by Shopping Mall')
plt.xlabel('Shopping Mall')
plt.ylabel('Number of Customers')
plt.legend(title='Segment')
plt.tight_layout()
plt.savefig(os.path.join(OUTPUT_DIR, 'segments_by_shopping_mall.png'))
plt.close()

# === 10. Summary Table: Average Age, Income, Spending Score per Segment ===
# If Product was label-encoded, decode for summary
if 'Product' in df.columns:
    try:
        product_encoder
    except NameError:
        product_encoder = None

    def decode_product(val):
        if product_encoder is not None:
            return product_encoder.inverse_transform([int(val)])[0]
        return val

    summary = df.groupby('segment').agg(
        avg_age=('Age', 'mean'),
        avg_income=('Annual Income', 'mean'),
        avg_spending_score=('Spending Score', 'mean'),
        count=('Age', 'count'),
        most_common_product=('Product', lambda x: x.mode()[0] if len(x.mode()) > 0 else None)
    ).round(1)
    summary['most_common_product'] = summary['most_common_product'].apply(decode_product)
else:
    summary = df.groupby('segment').agg(
        avg_age=('Age', 'mean'),
        avg_income=('Annual Income', 'mean'),
        avg_spending_score=('Spending Score', 'mean'),
        count=('Age', 'count')
    ).round(1)

summary.to_csv(os.path.join(OUTPUT_DIR, 'segment_summary.csv'))

print(f"\nAll charts and summary table saved in: {os.path.abspath(OUTPUT_DIR)}\nYou can tweak groupings or plots by editing this script!\n")
print(summary)

# === END OF SCRIPT ===
# You can comment out any section above to skip a plot or analysis.
# All plots use seaborn/matplotlib only, and are saved as PNGs for easy review.

# After clustering, decode Product column for visualization if label-encoded
if 'Product' in df.columns and 'product_encoder' in locals():
    df['Product_decoded'] = product_encoder.inverse_transform(df['Product'])
else:
    df['Product_decoded'] = df['Product']

# === Product Distribution by Segment Plot (grouped by Product) ===
if 'Product' in df.columns:
    plt.figure(figsize=(14, 7))
    sns.countplot(data=df, x='Product_decoded', hue='segment')
    plt.title('Customer Segment Distribution for Each Product')
    plt.xlabel('Product')
    plt.ylabel('Number of Customers')
    plt.legend(title='Segment')
    plt.xticks(rotation=45)
    plt.tight_layout()
    plt.savefig(os.path.join(OUTPUT_DIR, 'customer_segment_distribution_by_product.png'))
    plt.close()

    # === Product-Segment Counts Table ===
    product_segment_counts = df.groupby(['Product_decoded', 'segment']).size().unstack(fill_value=0)
    product_segment_counts.to_csv(os.path.join(OUTPUT_DIR, 'product_segment_counts.csv'))
else:
    print('No "Product" column found in the data. Skipping product distribution plot and table.')
