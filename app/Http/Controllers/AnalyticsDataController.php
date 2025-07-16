<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;

class AnalyticsDataController extends Controller
{
    /**
     * Returns the segment summary data from CSV as JSON.
     */
    public function getSegmentSummary()
    {
        $path = storage_path('app/public/segment_summary.csv');

        if (!file_exists($path)) {
            return response()->json(['error' => 'Segment summary file not found.'], 404);
        }

        $data = array_map('str_getcsv', file($path));
        return response()->json($data);
    }

    /**
     * Returns the product segment counts from CSV as JSON.
     */
    public function getProductSegmentCounts()
    {
        $path = storage_path('app/public/product_segment_counts.csv');

        if (!file_exists($path)) {
            return response()->json(['error' => 'Product segment counts file not found.'], 404);
        }

        $data = array_map('str_getcsv', file($path));
        return response()->json($data);
    }

    /**
     * Business segmentation: Get segment and recommendations for a business customer.
     */
    public function getBusinessSegment(Request $request)
    {
        $validated = $request->validate([
            'annual_revenue' => 'required|numeric',
            'order_frequency' => 'required|integer',
            'total_quantity_purchased' => 'required|integer',
            'location' => 'required|string',
            'business_type' => 'required|string',
        ]);

        // Step 1: Get segment from Python API
        $segmentResponse = Http::post('http://127.0.0.1:5000/api/segment', $validated);
        if (!$segmentResponse->ok()) {
            return response()->json(['error' => 'Failed to get segment', 'details' => $segmentResponse->json()], 400);
        }
        $segment = $segmentResponse->json('segment');
        if (!$segment) {
            return response()->json(['error' => 'No segment returned from API'], 400);
        }

        // Step 2: Get recommendations from Python API
        $recommendResponse = Http::post('http://127.0.0.1:5000/api/recommend', [
            'segment' => $segment
        ]);
        if (!$recommendResponse->ok()) {
            return response()->json(['error' => 'Failed to get recommendations', 'details' => $recommendResponse->json()], 400);
        }
        $recommendations = $recommendResponse->json('recommended_products');

        return response()->json([
            'segment' => $segment,
            'recommendations' => $recommendations
        ]);
    }

    //  Returns top 5 product recommendations based on a user's segment
    public function getRecommendationsForUser($id)
    {
        // STEP 1: Load the segmented customers CSV
        $userFile = storage_path('app/public/clustered_customers.csv');
        if (!file_exists($userFile)) {
            return response()->json(['error' => 'Customer segment file not found'], 404);
        }

        $userCsv = array_map('str_getcsv', file($userFile));
        $userHeader = array_shift($userCsv); // Skip header row

        $segment = null;
        foreach ($userCsv as $row) {
            if ($row[0] == $id) {
                $segment = $row[3]; // Assuming segment is column 4
                break;
            }
        }

        if (!$segment) {
            return response()->json(['error' => 'User segment not found'], 404);
        }

        // STEP 2: Load product_segment_counts CSV
        $productFile = storage_path('app/public/product_segment_counts.csv');
        if (!file_exists($productFile)) {
            return response()->json(['error' => 'Product segment file not found'], 404);
        }

        $productCsv = array_map('str_getcsv', file($productFile));
        $productHeader = array_shift($productCsv); // Skip header row

        $recommendations = [];
        foreach ($productCsv as $row) {
            $product = $row[0];
            $rowSegment = $row[1];
            $count = (int)$row[2];

            if (strtolower($rowSegment) === strtolower($segment)) {
                $recommendations[] = [
                    'product' => $product,
                    'count' => $count
                ];
            }
        }

        // Sort & limit top 5
        usort($recommendations, fn($a, $b) => $b['count'] <=> $a['count']);
        return response()->json([
            'segment' => $segment,
            'recommendations' => array_slice($recommendations, 0, 5)
        ]);
    }
}
