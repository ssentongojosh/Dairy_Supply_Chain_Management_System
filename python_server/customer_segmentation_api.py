from flask import Flask, request, jsonify
import pandas as pd
import pickle
from sklearn.cluster import KMeans
from sklearn.preprocessing import StandardScaler, LabelEncoder
import os

app = Flask(__name__)

# ============ Load Data & Train Model On Start =============

DATA_PATH = "C:/xampp/htdocs/Dairy_Supply_Chain_Management_System/database/seeders/Dataset/customer_segmentation_data_business.csv"
df = pd.read_csv(DATA_PATH)

# Encode categorical variables
label_encoders = {}
categorical_columns = ['location', 'business_type']

for column in categorical_columns:
    le = LabelEncoder()
    df[column] = le.fit_transform(df[column])
    label_encoders[column] = le

# Business-focused features
feature_cols = ['annual_revenue', 'order_frequency', 'total_quantity_purchased', 'location', 'business_type']
features = df[feature_cols]
scaler = StandardScaler()
scaled_features = scaler.fit_transform(features)

kmeans = KMeans(n_clusters=5, random_state=42)
kmeans.fit(scaled_features)

# ============ Load Top 3 Products per Segment =============
TOP3_PATH = os.path.join(os.path.dirname(__file__), '../storage/app/public/business_segment_top3_products.csv')
if not os.path.exists(TOP3_PATH):
    # Try absolute path fallback
    TOP3_PATH = 'storage/app/public/business_segment_top3_products.csv'
top3_df = pd.read_csv(TOP3_PATH)
segment_to_products = {
    row['segment']: [row['top1'], row['top2'], row['top3']]
    for _, row in top3_df.iterrows()
}

# ====== API endpoint to segment a new business customer ======
@app.route("/api/segment", methods=["POST"])
def get_segment():
    """
    Expects JSON with: {"annual_revenue": 500000, "order_frequency": 12, "total_quantity_purchased": 1000, "location": "New York", "business_type": "Restaurant"}
    Returns: {"segment": "Medium Business"}
    """
    data = request.json
    if not data:
        return jsonify({"error": "No data provided"}), 400

    # Encode categorical variables for prediction
    location = data.get("location")
    if location is None:
        return jsonify({"error": "Missing 'location' in request data"}), 400
    try:
        location_num = label_encoders['location'].transform([location])[0]
    except ValueError:
        return jsonify({"error": f"Unknown location: {location}. Allowed: {list(label_encoders['location'].classes_)}"}), 400

    try:
        business_type_num = label_encoders['business_type'].transform([data["business_type"]])[0]
    except ValueError:
        return jsonify({"error": f"Unknown business_type: {data['business_type']}. Allowed: {list(label_encoders['business_type'].classes_)}"}), 400

    sample = pd.DataFrame([[
        data["annual_revenue"],
        data["order_frequency"],
        data["total_quantity_purchased"],
        location_num,
        business_type_num
    ]], columns=feature_cols)

    sample_scaled = scaler.transform(sample)
    cluster = int(kmeans.predict(sample_scaled)[0])

    # Business segment labels
    cluster_labels = {
        0: 'Small Business',
        1: 'Medium Business',
        2: 'Large Business',
        3: 'High Frequency Business',
        4: 'Premium Business'
    }
    segment = cluster_labels.get(cluster, f"Cluster {cluster}")
    return jsonify({"segment": segment})

# ====== API endpoint to recommend products for a segment ======
@app.route("/api/recommend", methods=["POST"])
def recommend_products():
    """
    Expects JSON with: {"segment": "Medium Business"}
    Returns: {"recommended_products": ["Product 1", "Product 2", "Product 3"]}
    """
    data = request.json
    if not data:
        return jsonify({"error": "No data provided"}), 400

    segment = data.get("segment")
    products = segment_to_products.get(segment)
    if products:
        return jsonify({"recommended_products": products})
    else:
        return jsonify({"recommended_products": []}), 404

if __name__ == "__main__":
    app.run(debug=True, port=5000)  # Runs at http://127.0.0.1:5000
