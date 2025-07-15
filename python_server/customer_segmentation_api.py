from flask import Flask, request, jsonify
import pandas as pd
import pickle
from sklearn.cluster import KMeans
from sklearn.preprocessing import StandardScaler, LabelEncoder

app = Flask(__name__)

# ============ Load Data & Train Model On Start =============

DATA_PATH = "customer_segmentation_data.csv"  # or use full path if needed
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

if __name__ == "__main__":
    app.run(debug=True, port=5000)  # Runs at http://127.0.0.1:5000
