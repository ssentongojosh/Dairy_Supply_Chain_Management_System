<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
