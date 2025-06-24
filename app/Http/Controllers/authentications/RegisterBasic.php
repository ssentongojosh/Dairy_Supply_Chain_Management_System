<?php

namespace App\Http\Controllers\authentications;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Enums\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Auth\Events\Registered;
use Illuminate\Validation\Rules;

class RegisterBasic extends Controller
{
    public function index()
    {
        return view('content.authentications.auth-register-basic');
    }

    public function register(Request $request)
    {
        try {
            // Log incoming request data
            Log::info('Registration attempt with data:', ['data' => $request->all()]);

            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'password' => ['required', 'confirmed'],
                'role' => ['required', 'string', 'in:retailer,wholesaler,farmer,supplier,plant_manager,user'],
                'terms' => ['required', 'accepted'],
            ]);

            Log::info('Validation passed, creating user');

            DB::beginTransaction();
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->role,
            ]);
            DB::commit();

            Log::info('User created successfully', ['id' => $user->id, 'role' => $user->role]);

            event(new Registered($user));
            Auth::login($user);

            // Redirect based on role
            return $this->redirectBasedOnRole($user);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Registration failed: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);

            return back()
                ->withInput()
                ->withErrors(['registration_error' => 'Registration failed: ' . $e->getMessage()]);
        }
    }

    protected function redirectBasedOnRole($user)
    {
        $role = $user->role instanceof \App\Enums\Role ? $user->role->value : $user->role;

        return match($role) {
            'farmer' => redirect()->route('farmer.dashboard')->with('success', 'Welcome to your Farmer Dashboard!'),
            'retailer' => redirect()->route('retailer.dashboard')->with('success', 'Welcome to your Retailer Dashboard!'),
            'wholesaler' => redirect()->route('wholesaler.dashboard')->with('success', 'Welcome to your Wholesaler Dashboard!'),
            'supplier' => redirect()->route('supplier.dashboard')->with('success', 'Welcome to your Supplier Dashboard!'),
            'plant_manager' => redirect()->route('plant-manager.dashboard')->with('success', 'Welcome to your Plant Manager Dashboard!'),
            'admin' => redirect()->route('admin.dashboard')->with('success', 'Welcome to the Admin Dashboard!'),
            default => redirect()->route('home')->with('success', 'Registration successful!')
        };
    }
}

// These two routes potentially conflict
// Route::post('/register', [RegisterBasic::class, 'register'])->name('register.submit');
// Route::get('/auth/register-basic', [RegisterBasic::class, 'index'])->name('auth-register-basic');
