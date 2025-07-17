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
    protected $flaskApiUrl = 'http://127.0.0.1:5000'; // Make sure this matches your Flask server address

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
        $wholesalerIDs = $this->getWholesalerIDs(); // Changed: Get wholesaler IDs

        return view('forecast.index', compact('products', 'wholesalerIDs')); // Changed: Pass wholesalerIDs
    }

    /**
     * Handle the forecast request, call the ML API, and display results.
     */
    public function forecast(Request $request)
    {
        $request->validate([
            'product' => 'required|string',
            'wholesaler_id' => 'required|string', // CHANGED: from shopping_mall
            'start_date' => 'required|date_format:Y-m-d',
            'end_date' => 'required|date_format:Y-m-d|after_or_equal:start_date',
        ]);

        $product = $request->input('product');
        $wholesalerId = $request->input('wholesaler_id'); // CHANGED: from shoppingMall
        $startDate = Carbon::parse($request->input('start_date'));
        $endDate = Carbon::parse($request->input('end_date'));

        $apiStartDate = $startDate->format('d/m/Y');
        $apiEndDate = $endDate->format('d/m/Y');

        $payload = [
            'product' => $product,
            'wholesaler_id' => $wholesalerId, // CHANGED: from shopping_mall
            'start_date' => $apiStartDate,
            'end_date' => $apiEndDate,
        ];

        try {
            $response = Http::post("{$this->flaskApiUrl}/forecast_demand_range", $payload);

            if ($response->successful()) {
                $forecastData = $response->json();

                $dates = [];
                $demands = [];
                foreach ($forecastData['forecast_data'] as $item) {
                    $dates[] = Carbon::parse($item['date'])->format('M d');
                    $demands[] = $item['predicted_demand'];
                }

                return view('forecast.index', [
                    'products' => $this->getProducts(),
                    'wholesalerIDs' => $this->getWholesalerIDs(), // CHANGED: Pass wholesalerIDs
                    'forecast_data_json' => json_encode($forecastData),
                    'chart_labels' => json_encode($dates),
                    'chart_data' => json_encode($demands),
                    'selected_product' => $product,
                    'selected_wholesaler_id' => $wholesalerId, // CHANGED: from selected_mall
                    'selected_start_date' => $request->input('start_date'),
                    'selected_end_date' => $request->input('end_date'),
                ]);

            } else {
                Log::error('Flask API Error:', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'payload' => $payload
                ]);
                return back()->withInput()->with('error', 'Failed to get forecast from ML service. Please try again or check logs. ' . ($response->json()['error'] ?? ''));
            }
        } catch (\Exception $e) {
            Log::error('ML Service Connection Error:', ['exception' => $e->getMessage(), 'payload' => $payload]);
            return back()->withInput()->with('error', 'Could not connect to the ML forecasting service. Please ensure it is running.');
        }
    }

    /**
     * Generate tasks based on forecast data.
     * Accessible only by Plant Managers.
     */
    public function generateTasksFromForecast(Request $request)
    {
        $user = Auth::user();
        $userRoleValue = is_object($user->role) && method_exists($user->role, 'value')
                         ? $user->role->value
                         : (string) $user->role;

        if ($userRoleValue !== Role::PLANT_MANAGER->value) {
            return response()->json(['message' => 'Unauthorized: Only Plant Managers can generate tasks from forecasts.'], 403);
        }

        $request->validate([
            'task_type' => 'required|string|in:production_adjustment,raw_material_order',
            'product' => 'required|string',
            'wholesaler_id' => 'required|string', // CHANGED: from shopping_mall
            'start_date' => 'required|date_format:Y-m-d',
            'end_date' => 'required|date_format:Y-m-d|after_or_equal:start_date',
            'predicted_demand_total' => 'nullable|numeric',
        ]);

        $taskType = $request->input('task_type');
        $productName = $request->input('product');
        $wholesalerId = $request->input('wholesaler_id'); // CHANGED: from shopping_mall
        $startDate = Carbon::parse($request->input('start_date'));
        $endDate = Carbon::parse($request->input('end_date'));
        $predictedDemandTotal = $request->input('predicted_demand_total');

        $productModel = Product::where('name', $productName)->first();
        if (!$productModel) {
            Log::warning("Product model not found for task generation: {$productName}");
            return response()->json(['message' => "Product '{$productName}' not found in database. Task not generated."], 404);
        }

        $taskDescription = '';
        $requiredRole = '';
        $priority = Task::PRIORITY_MEDIUM;
        $dueDate = Carbon::now()->addDays(7);

        $demandInfo = $predictedDemandTotal !== null ? " (Predicted Total Demand: {$predictedDemandTotal} units)" : "";

        switch ($taskType) {
            case 'production_adjustment':
                $taskDescription = "Adjust production for '{$productName}' for Wholesaler '{$wholesalerId}' based on forecast from {$startDate->format('M d, Y')} to {$endDate->format('M d, Y')}{$demandInfo}."; // CHANGED: description
                $requiredRole = Role::WORKER->value;
                $priority = Task::PRIORITY_HIGH;
                $dueDate = Carbon::now()->addDays(3);
                break;
            case 'raw_material_order':
                $taskDescription = "Assess raw material needs for '{$productName}' for Wholesaler '{$wholesalerId}' based on demand forecast for {$startDate->format('M d, Y')} to {$endDate->format('M d, Y')}{$demandInfo}."; // CHANGED: description
                $requiredRole = Role::PLANT_MANAGER->value;
                $priority = Task::PRIORITY_HIGH;
                $dueDate = Carbon::now()->addDays(5);
                break;
            default:
                return response()->json(['message' => 'Invalid task type specified.'], 400);
        }

        $assignedTask = $this->taskAssignmentService->assignTask(
            $taskType,
            $taskDescription,
            $requiredRole,
            $dueDate,
            $priority,
            $productModel
        );

        if ($assignedTask) {
            Log::info("Plant Manager (ID: {$user->id}) generated task '{$taskType}' (ID: {$assignedTask->id}) for product '{$productName}' and wholesaler '{$wholesalerId}'."); // CHANGED: log message
            return response()->json(['message' => 'Task generated and assigned successfully! Task ID: ' . $assignedTask->id, 'task_id' => $assignedTask->id], 200);
        } else {
            Log::error("Plant Manager (ID: {$user->id}) failed to generate task '{$taskType}' for product '{$productName}' and wholesaler '{$wholesalerId}'. Check TaskAssignmentService logs."); // CHANGED: log message
            return response()->json(['message' => 'Failed to generate task. No eligible assignee found or an error occurred.'], 500);
        }
    }

    private function getProducts(): array
    {
        return Product::pluck('name')->toArray();
    }

    private function getWholesalerIDs(): array
    {

        // return User::where('role', Role::WHOLESALER->value)->pluck('id')->map(fn($id) => 'C' . $id)->unique()->toArray();

        return User::where('role', Role::WHOLESALER->value)->pluck('id')->map(function($id) {
            return (string) $id;
        })->unique()->toArray();

    }
}
