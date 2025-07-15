from flask import Flask, request, jsonify
import pandas as pd
import pickle
from sklearn.cluster import KMeans
from sklearn.preprocessing import StandardScaler, LabelEncoder
import os

app = Flask(__name__)

# ============ Load Data & Train Model On Start =============

DATA_PATH = "C:/xampp/htdocs/Dairy_Supply_Chain_Management_System/database/seeders/Dataset/customer_segmentation_data.csv"  # or use full path if needed
df = pd.read_csv(DATA_PATH)

# Encode gender (assuming it's needed)
if df['Gender'].dtype == object:
    label_encoder = LabelEncoder()
    df['Gender'] = label_encoder.fit_transform(df['Gender'])

feature_cols = ['Age', 'Gender', 'Annual Income', 'Spending Score']
features = df[feature_cols]
scaler = StandardScaler()
scaled_features = scaler.fit_transform(features)

kmeans = KMeans(n_clusters=5, random_state=42)
kmeans.fit(scaled_features)

# ============ Load Top 3 Products per Segment =============
TOP3_PATH = os.path.join(os.path.dirname(__file__), '../storage/app/public/segment_top3_products.csv')
if not os.path.exists(TOP3_PATH):
    # Try absolute path fallback
    TOP3_PATH = 'storage/app/public/segment_top3_products.csv'
top3_df = pd.read_csv(TOP3_PATH)
segment_to_products = {
    row['segment']: [row['top1'], row['top2'], row['top3']]
    for _, row in top3_df.iterrows()
}

# ====== API endpoint to segment a new customer ======
@app.route("/api/segment", methods=["POST"])
def get_segment():
    """
    Expects JSON with: {"age": 25, "gender": "Male", "income": 50000, "score": 67}
    Returns: {"segment": "Middle Age Spenders"}
    """
    data = request.json
    gender = data["gender"]
    # Encode gender for prediction
    gender_num = label_encoder.transform([gender])[0]
    sample = pd.DataFrame([[
        data["age"], gender_num, data["income"], data["score"]
    ]], columns=feature_cols)
    sample_scaled = scaler.transform(sample)
    cluster = kmeans.predict(sample_scaled)[0]
    # You can use your cluster_labels mapping from your script
    cluster_labels = {
        0: 'Young Savers',
        1: 'Middle Age Spenders',
        2: 'Middle Age Savers',
        3: 'Middle Age Average Income Spenders',
        4: 'Older Spenders'
    }
    segment = cluster_labels.get(cluster, f"Cluster {cluster}")
    return jsonify({"segment": segment})

# ====== API endpoint to recommend products for a segment ======
@app.route("/api/recommend", methods=["POST"])
def recommend_products():
    """
    Expects JSON with: {"segment": "Middle Age Spenders"}
    Returns: {"recommended_products": ["Powdered milk 26% mg", ...]}
    """
    data = request.json
    segment = data.get("segment")
    products = segment_to_products.get(segment)
    if products:
        return jsonify({"recommended_products": products})
    else:
        return jsonify({"recommended_products": []}), 404

if __name__ == "__main__":
    app.run(debug=True, port=5000)  # Runs at http://127.0.0.1:5000
