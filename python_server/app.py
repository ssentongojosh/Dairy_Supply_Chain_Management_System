from fastapi import FastAPI
from pydantic import BaseModel
import joblib
from fastapi.middleware.cors import CORSMiddleware
from joblib import load

# Initialize FastAPI
app = FastAPI()

# Enable CORS for Laravel integration
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],  # Adjust to Laravel URL if needed
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# Load your machine learning model (placeholder path)
model = joblib.load('model/model.pkl')
# Load your segmentation model (placeholder path)
segmentation_model = load('model/segment_customer_model.pkl')

class PredictRequest(BaseModel):
    features: list[float]

class PredictResponse(BaseModel):
    prediction: float

@app.post("/predict_sales", response_model=PredictResponse)
def predict(request: PredictRequest):
    features = [request.features]
    pred = model.predict(features)
    return PredictResponse(prediction=float(pred[0]))

class SegmentRequest(BaseModel):
    features: list[float]

class SegmentResponse(BaseModel):
    segment: str

@app.post("/segment_customers", response_model=SegmentResponse)
def segment_customers(request: SegmentRequest):
    features = [request.features]
    seg = segmentation_model.predict(features)
    return SegmentResponse(segment=str(seg[0]))
