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
}
