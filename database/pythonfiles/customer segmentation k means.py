import pandas as pd
import matplotlib.pyplot as plt
import seaborn as sns
from sklearn.cluster import KMeans
from sklearn.preprocessing import StandardScaler, LabelEncoder

print("🔍 Loading customer segmentation data...")

# === 1. Load the dataset ===
df = pd.read_csv('C:/xampp/htdocs/Dairy_Supply_Chain_Management_System/database/seeders/Dataset/customer_segmentation_data.csv')

# === 2. Drop any missing values ===
df.dropna(inplace=True)

# === 3. Encode Gender column ===
# Convert gender to numerical (Male=1, Female=0)
label_encoder = LabelEncoder()
df['Gender'] = label_encoder.fit_transform(df['Gender'])

# === 4. Select features for clustering ===
features = df[['Age', 'Gender', 'Annual Income', 'Spending Score']]

# === 5. Standardize the data ===
scaler = StandardScaler()
scaled_features = scaler.fit_transform(features)

# === 6. Apply KMeans Clustering ===
kmeans = KMeans(n_clusters=5, random_state=42)
df['cluster'] = kmeans.fit_predict(scaled_features)

# === 6b. Print cluster averages to help decide labels ===
cluster_means = df.groupby('cluster')[['Age', 'Annual Income', 'Spending Score']].mean().round(1)
print("\nCluster Averages (use these to decide labels):")
print(cluster_means)

# === 6c. Assign human-readable labels to each cluster ===
# Adjust these labels after seeing the printed cluster_means
cluster_labels = {
    0: 'Young Budget Shoppers',
    1: 'middle-age spenders',
    2: 'middle-age savers',
    3: 'middle-age average-income spenders',
    4: 'old rich savers'
}
df['segment'] = df['cluster'].replace(cluster_labels)

# === 7. Visualize the Clusters (use segment labels) ===
plt.figure(figsize=(10, 6))
sns.scatterplot(
    data=df,
    x='Annual Income',
    y='Spending Score',
    hue='segment',  # Use the label column
    palette='Set2',
    s=100
)
plt.title(" Customer Segments Based on Income and Spending")
plt.xlabel("Annual Income")
plt.ylabel("Spending Score")
plt.legend(title="Segment")
plt.grid(True)
plt.tight_layout()
plt.savefig("customer_segments.png")
plt.show()

# === 8. Save clustered data (optional) ===
df.to_csv("clustered_customers.csv", index=False)

print("✅ Segmentation complete! Segmented data saved as 'clustered_customers.csv' and 'customer_segments.png'")
