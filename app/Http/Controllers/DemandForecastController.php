<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Services\TaskAssignmentService;
use App\Models\Task;
use App\Models\Product;
use App\Models\User;
use App\Enums\Role;

class DemandForecastController extends Controller
{
    protected $flaskApiUrl = 'http://127.0.0.1:5000';
    protected $unitMultiplier = 1000;
    protected $unitAdder = 2;

    protected $taskAssignmentService;

    public function __construct(TaskAssignmentService $taskAssignmentService)
    {
        $this->taskAssignmentService = $taskAssignmentService;
    }

    /**
     * Display the demand forecasting form.
     */
    public function index()
    {
        $products = $this->getProducts();
        $wholesalerIDs = $this->getWholesalerIDs();

        // Ensure $suggestedTasks is always initialized
        $suggestedTasks = collect();

        if (Auth::check() && (is_object(Auth::user()->role) ? Auth::user()->role->value : (string) Auth::user()->role) === Role::PLANT_MANAGER->value) {
            $suggestedTasks = Task::where('status', Task::STATUS_SUGGESTED)
                                  ->orderBy('created_at', 'desc')
                                  ->get();
        }

        return view('forecast.index', compact('products', 'wholesalerIDs', 'suggestedTasks'));
    }

    /**
     * Handle the forecast request, call the ML API, and display results.
     */
    public function forecast(Request $request)
    {
        $request->validate([
            'forecast_type' => 'required|string|in:wholesaler_specific,general',
            'product' => 'required|string',
            'start_date' => 'required|date_format:Y-m-d',
            'end_date' => 'required|date_format:Y-m-d|after_or_equal:start_date',
            'wholesaler_id' => 'nullable|string',
        ]);

        $forecastType = $request->input('forecast_type');
        $product = $request->input('product');
        $wholesalerId = $request->input('wholesaler_id');
        $startDate = Carbon::parse($request->input('start_date'));
        $endDate = Carbon::parse($request->input('end_date'));

        $apiStartDate = $startDate->format('d/m/Y');
        $apiEndDate = $endDate->format('d/m/Y');

        $flaskEndpoint = '';
        $payload = [
            'product' => $product,
            'start_date' => $apiStartDate,
            'end_date' => $apiEndDate,
        ];

        if ($forecastType === 'wholesaler_specific') {
            $flaskEndpoint = '/forecast_demand_range';
            if (empty($wholesalerId)) {
                return back()->withInput()->with('error', 'Wholesaler ID is required for wholesaler-specific forecast.');
            }
            $payload['wholesaler_id'] = $wholesalerId;
        } else {
            $flaskEndpoint = '/forecast_general_demand_range';
        }

        try {
            Log::info("Calling Flask API: {$this->flaskApiUrl}{$flaskEndpoint} with payload: " . json_encode($payload));
            $response = Http::post("{$this->flaskApiUrl}{$flaskEndpoint}", $payload);

            if ($response->successful()) {
                $forecastData = $response->json();

                $dates = [];
                $demands = [];
                foreach ($forecastData['forecast_data'] as $item) {
                    $dates[] = Carbon::parse($item['date'])->format('M d');
                    // NEW: Apply the multiplier here
                    $demands[] = $item['predicted_demand'] * $this->unitMultiplier + $this->unitAdder; // Add 2 units to each demand value
                }

                $suggestedTasks = collect();
                if (Auth::check() && (is_object(Auth::user()->role) ? Auth::user()->role->value : (string) Auth::user()->role) === Role::PLANT_MANAGER->value) {
                    $suggestedTasks = Task::where('status', Task::STATUS_SUGGESTED)
                                          ->orderBy('created_at', 'desc')
                                          ->get();
                }

                return view('forecast.index', [
                    'products' => $this->getProducts(),
                    'wholesalerIDs' => $this->getWholesalerIDs(),
                    'forecast_data_json' => json_encode($forecastData),
                    'chart_labels' => json_encode($dates),
                    'chart_data' => json_encode($demands), // This now contains multiplied values
                    'selected_product' => $product,
                    'selected_wholesaler_id' => $wholesalerId,
                    'selected_start_date' => $request->input('start_date'),
                    'selected_end_date' => $request->input('end_date'),
                    'selected_forecast_type' => $forecastType,
                    'suggestedTasks' => $suggestedTasks,
                ]);

            } else {
                Log::error('Flask API Error:', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'payload' => $payload
                ]);
                return back()->withInput()->with('error', 'Failed to get forecast from ML service. Please try again or check logs. ' . ($response->json()['error'] ?? ''));
            }
        } catch (\Exception | \Throwable $e) {
            Log::error('ML Service Connection Error or other exception:', ['exception' => $e->getMessage(), 'payload' => $payload ?? 'N/A', 'trace' => $e->getTraceAsString()]);
            return back()->withInput()->with('error', 'Could not connect to the ML forecasting service or an internal error occurred: ' . $e->getMessage());
        }
    }

    /**
     * Generate tasks based on forecast data.
     * Accessible only by Plant Managers.
     */
    public function generateTasksFromForecast(Request $request)
    {

$user = Auth::user();

if (!$user) {
    return response()->json(['message' => 'Unauthorized: User not authenticated.'], 401);
}

try {
    $userRoleValue = $user->role instanceof \BackedEnum ? $user->role->value : (string) $user->role;
} catch (\Throwable $e) {
    Log::error("Role conversion error: " . $e->getMessage());
    return response()->json(['message' => 'Unauthorized: Invalid role format.'], 401);
}




        if ($userRoleValue !== Role::PLANT_MANAGER->value) {
            return response()->json(['message' => 'Unauthorized: Only Plant Managers can generate tasks from forecasts.'], 403);
        }

        $request->validate([
            'task_type' => 'required|string|in:production_adjustment,raw_material_order',
            'product' => 'required|string',
            'start_date' => 'required|date_format:Y-m-d',
            'end_date' => 'required|date_format:Y-m-d|after_or_equal:start_date',
            'predicted_demand_total' => 'nullable|numeric', // This will now be the multiplied total
            'forecast_type' => 'required|string|in:wholesaler_specific,general',
            'wholesaler_id' => 'nullable|string',
            'is_automated_suggestion' => 'boolean',
        ]);

        $taskType = $request->input('task_type');
        $productName = $request->input('product');
        $wholesalerId = $request->input('wholesaler_id');
        $startDate = Carbon::parse($request->input('start_date'));
        $endDate = Carbon::parse($request->input('end_date'));
        $predictedDemandTotal = $request->input('predicted_demand_total'); // This value is already multiplied from JS
        $forecastType = $request->input('forecast_type');
        $isAutomatedSuggestion = $request->input('is_automated_suggestion', false);

        $productModel = Product::where('name', $productName)->first();
        if (!$productModel) {
            Log::warning("Product model not found for task generation: {$productName}");
            return response()->json(['message' => "Product '{$productName}' not found in database. Task not generated."], 404);
        }

        $taskDescription = '';
        $requiredRole = '';
        $priority = Task::PRIORITY_MEDIUM;
        $dueDate = Carbon::now()->addDays(7);
        $status = $isAutomatedSuggestion ? Task::STATUS_SUGGESTED : Task::STATUS_PENDING;

        $demandInfo = $predictedDemandTotal !== null ? " (Predicted Total Demand: {$predictedDemandTotal} units)" : "";
        $segmentInfo = ($forecastType === 'wholesaler_specific' && $wholesalerId) ? " for Wholesaler '{$wholesalerId}'" : " (General Forecast)";

        switch ($taskType) {
            case 'production_adjustment':
                $taskDescription = "Adjust production for '{$productName}'{$segmentInfo} based on forecast from {$startDate->format('M d, Y')} to {$endDate->format('M d, Y')}{$demandInfo}.";
                $requiredRole = Role::WORKER->value;
                $priority = Task::PRIORITY_HIGH;
                $dueDate = Carbon::now()->addDays(3);
                break;
            case 'raw_material_order':
                $taskDescription = "Assess raw material needs for '{$productName}'{$segmentInfo} based on demand forecast for {$startDate->format('M d, Y')} to {$endDate->format('M d, Y')}{$demandInfo}.";
                $requiredRole = Role::PLANT_MANAGER->value;
                $priority = Task::PRIORITY_HIGH;
                $dueDate = Carbon::now()->addDays(5);
                break;
            default:
                return response()->json(['message' => 'Invalid task type specified.'], 400);
        }

        if ($isAutomatedSuggestion) {
            $existingTask = Task::where('task_type', $taskType)
                                ->where('product_id', $productModel->id)
                                ->where('wholesaler_id', $wholesalerId)
                                ->where('forecast_start_date', $startDate)
                                ->where('forecast_end_date', $endDate)
                                ->where('status', Task::STATUS_SUGGESTED)
                                ->first();

            if ($existingTask) {
                Log::info("Skipping duplicate automated task suggestion: {$taskType} for {$productName} {$segmentInfo} from {$startDate->format('Y-m-d')}.");
                return response()->json(['message' => 'Task already suggested and awaiting review.'], 200);
            }
        }

        $assignedTask = $this->taskAssignmentService->assignTask(
            $taskType,
            $taskDescription,
            $requiredRole,
            $dueDate,
            $priority,
            $productModel,
            $status,
            $wholesalerId,
            $startDate,
            $endDate
        );

        if ($assignedTask) {
            Log::info("Plant Manager (ID: {$user->id}) generated task '{$taskType}' (ID: {$assignedTask->id}) for product '{$productName}'{$segmentInfo}. Status: {$status}.");
            return response()->json(['message' => 'Task generated and assigned successfully! Task ID: ' . $assignedTask->id, 'task_id' => $assignedTask->id], 200);
        } else {
            Log::error("Plant Manager (ID: {$user->id}) failed to generate task '{$taskType}' for product '{$productName}'{$segmentInfo}. Check TaskAssignmentService logs.");
            return response()->json(['message' => 'Failed to generate task. No eligible assignee found or an error occurred.'], 500);
        }
    }

    /**
     * Method to suggest automated tasks based on forecast rules.
     * Accessible only by Plant Managers.
     */
    public function suggestAutomatedTasks(Request $request)
    {



        $suggestedCount = 0;
        $forecastPeriodDays = 7;
        $forecastStartDate = Carbon::now()->addDay();
        $forecastEndDate = $forecastStartDate->copy()->addDays($forecastPeriodDays - 1);

        $products = $this->getProducts();
        $wholesalers = $this->getWholesalerIDs();

        // --- Rule 1: Production Adjustment (General Product Forecast) ---
        // Threshold is now in multiplied units
        $productionAdjustmentThreshold = 500;
        Log::info("Checking for Production Adjustment suggestions (General Forecast)...");
        foreach ($products as $product) {
            $payload = [
                'product' => $product,
                'start_date' => $forecastStartDate->format('d/m/Y'),
                'end_date' => $forecastEndDate->format('d/m/Y'),
            ];
            try {
                $response = Http::post("{$this->flaskApiUrl}/forecast_general_demand_range", $payload);
                if ($response->successful()) {
                    $forecastData = $response->json();
                    // Apply multiplier to individual predicted_demand values before summing
                    $totalPredictedDemand = array_sum(array_map(function($item) {
                        return $item['predicted_demand'] * $this->unitMultiplier + $this->unitAdder ; // Apply multiplier
                    }, $forecastData['forecast_data']));

                    if ($totalPredictedDemand > $productionAdjustmentThreshold) {
                        $taskData = [
                            'task_type' => 'production_adjustment',
                            'product' => $product['name'],
                            'wholesaler_id' => null,
                            'start_date' => $forecastStartDate->format('Y-m-d'),
                            'end_date' => $forecastEndDate->format('Y-m-d'),
                            'predicted_demand_total' => $totalPredictedDemand, // This is now the multiplied total
                            'forecast_type' => 'general',
                            'is_automated_suggestion' => true,
                        ];
                        $generateRequest = Request::create('/api/demand-forecast/generate-task', 'POST', $taskData);
                        $generateRequest->headers->set('X-Requested-With', 'XMLHttpRequest');
                        $generateRequest->headers->set('X-CSRF-TOKEN', csrf_token());

                        Auth::login($user);
                        $response = app()->call([$this, 'generateTasksFromForecast'], ['request' => $generateRequest]);
                        Auth::logout();

                        if ($response->getStatusCode() === 200) {
                            $suggestedCount++;
                            Log::info("Suggested Production Adjustment for {$product['name']}. Total Demand: {$totalPredictedDemand}");
                        } else {
                            Log::error("Failed to suggest Production Adjustment for {$product['name']}. Response: " . $response->getContent());
                        }
                    }
                } else {
                    Log::error("Flask API Error for general forecast (Product: {$product['name']}): " . $response->body());
                }
            } catch (\Exception $e) {
                Log::error("Error fetching general forecast for {$product['name']}: " . $e->getMessage());
            }
        }

        // --- Rule 2: Raw Material Order (Wholesaler-Specific Forecast) ---
        // Threshold is now in multiplied units
        $rawMaterialOrderThreshold = 100;
        Log::info("Checking for Raw Material Order suggestions (Wholesaler-Specific Forecast)...");
        foreach ($products as $product) {
            foreach ($wholesalers as $wholesaler) {
                $payload = [
                    'product' => $product,
                    'wholesaler_id' => $wholesaler['id'],
                    'start_date' => $forecastStartDate->format('d/m/Y'),
                    'end_date' => $forecastEndDate->format('d/m/Y'),
                ];
                try {
                    $response = Http::post("{$this->flaskApiUrl}/forecast_demand_range", $payload);
                    if ($response->successful()) {
                        $forecastData = $response->json();
                        // Apply multiplier to individual predicted_demand values before summing
                        $totalPredictedDemand = array_sum(array_map(function($item) {
                            return $item['predicted_demand'] * $this->unitMultiplier + $this->unitAdder; // Apply multiplier
                        }, $forecastData['forecast_data']));

                        if ($totalPredictedDemand > $rawMaterialOrderThreshold) {
                            $taskData = [
                                'task_type' => 'raw_material_order',
                                'product' => $product,
                                'wholesaler_id' => $wholesaler['id'],
                                'start_date' => $forecastStartDate->format('Y-m-d'),
                                'end_date' => $forecastEndDate->format('Y-m-d'),
                                'predicted_demand_total' => $totalPredictedDemand, // This is now the multiplied total
                                'forecast_type' => 'wholesaler_specific',
                                'is_automated_suggestion' => true,
                            ];
                            $generateRequest = Request::create('/api/demand-forecast/generate-task', 'POST', $taskData);
                            $generateRequest->headers->set('X-Requested-With', 'XMLHttpRequest');
                            $generateRequest->headers->set('X-CSRF-TOKEN', csrf_token());

                            Auth::login($user);
                            $response = app()->call([$this, 'generateTasksFromForecast'], ['request' => $generateRequest]);
                            Auth::logout();

                            if ($response->getStatusCode() === 200) {
                                $suggestedCount++;
                                Log::info("Suggested Raw Material Order for {$product['name']} (Wholesaler: {$wholesaler['name']}). Total Demand: {$totalPredictedDemand}");
                            } else {
                                Log::error("Failed to suggest Raw Material Order for {$product['name']} (Wholesaler: {$wholesaler['name']}). Response: " . $response->getContent());
                            }
                        }
                    } else {
                        Log::error("Flask API Error for wholesaler-specific forecast (Product: {$product['name']}, Wholesaler: {$wholesaler['id']}): " . $response->body());
                    }
                } catch (\Exception $e) {
                    Log::error("Error fetching wholesaler-specific forecast for {$product} (Wholesaler: {$wholesaler['id']}): " . $e->getMessage());
                }
            }
        }

        return response()->json(['message' => "Automated task suggestion complete. {$suggestedCount} new tasks suggested.", 'suggested_count' => $suggestedCount], 200);
    }


    private function getProducts(): array
    {
        return Product::pluck('name')->toArray();
    }

    private function getWholesalerIDs(): array
    {
        return User::where('role', Role::WHOLESALER->value)
            ->get(['id', 'name'])
            ->map(function($user) {
                return [
                    'id' => (string) $user->id,
                    'name' => $user->name,
                ];
            })
            ->toArray();
    }
}
