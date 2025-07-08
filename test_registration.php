<?php

require_once 'vendor/autoload.php';

// Load Laravel app
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Create a simple test request to register
$request = Illuminate\Http\Request::create('/register', 'POST', [
    'name' => 'Test User',
    'email' => 'test' . time() . '@example.com',
    'password' => 'password123',
    'password_confirmation' => 'password123',
    'role' => 'retailer',
    'terms' => '1'
]);

// Process the request
try {
    $response = $kernel->handle($request);

    echo "Response Status: " . $response->getStatusCode() . "\n";
    echo "Response Headers:\n";
    foreach ($response->headers->all() as $key => $values) {
        echo "  $key: " . implode(', ', $values) . "\n";
    }

    if ($response->isRedirect()) {
        echo "Redirect Location: " . $response->headers->get('Location') . "\n";
    }

    echo "\nResponse Content:\n";
    echo substr($response->getContent(), 0, 500) . "\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
