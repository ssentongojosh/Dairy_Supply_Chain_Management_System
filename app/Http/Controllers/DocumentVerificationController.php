<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use App\Enums\Role;
use App\Models\User; // Import User model
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class DocumentVerificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    private function ensureUserIsValid($context = 'DocumentVerification')
    {
        $user = Auth::user();
        if (!$user instanceof User) {
            Log::critical($context . ' Error: Auth::user() did not return a User instance.', [
                'returned_type' => gettype($user),
                'returned_value' => $user
            ]);
            Auth::logout(); // Force logout
            // It's tricky to redirect from here if session is already broken,
            // but attempt to send to login.
            // This might result in a redirect loop if the issue is persistent.
            if (request()->expectsJson()) {
                return response()->json(['error' => 'Authentication error.'], 401);
            }
            // For web, try to redirect. If this itself causes issues, the problem is deeper.
            header('Location: ' . route('login'));
            exit;
        }
        return $user;
    }

    public function showUploadForm()
    {
        $user = $this->ensureUserIsValid('showUploadForm');
        if (!$user) return redirect()->route('login')->withErrors(['session_error' => 'Your session is invalid. Please log in again.']);


        if ($user->verified) {
            return $this->redirectToDashboard($user);
        }

        return view('content.verification.upload-document');
    }

    public function uploadDocument(Request $request)
    {
        $user = $this->ensureUserIsValid('uploadDocument');
        if (!$user) return redirect()->route('login')->withErrors(['session_error' => 'Your session is invalid. Please log in again.']);

        $request->validate([
            'business_document' => ['required', 'file', 'mimes:pdf', 'max:10240'],
            'business_description' => 'required|string|max:1000'
        ]);

        $path = $request->file('business_document')->store('business-documents/' . $user->id, 'private');

        $user->business_document_path = $path;
        $user->save();

        // Call Java verification microservice
        $javaUrl = env('JAVA_SERVER_URL', 'http://localhost:8080');
        $fileContents = Storage::disk('private')->get($path);
        $response = Http::timeout(120)
            ->attach('file', $fileContents, basename($path))
            ->post($javaUrl . '/verification', [
                'user_id' => $user->id,
                'business_description' => $request->input('business_description'),
            ]);

        if ($response->ok()) {
            $extractedText = $response->body();
            $verified = trim($extractedText) !== '';
            $user->verified = $verified;
            $user->verification_notes = $extractedText;
            $user->save();
        } else {
            return redirect()->route('verification.pending')
                ->with('error', 'Document processing failed on Java server.');
        }

        return redirect()->route('verification.pending')
            ->with('success', 'Document uploaded and verification initiated.');
    }

    public function pendingVerification()
    {
        $user = $this->ensureUserIsValid('pendingVerification');
        if (!$user) return redirect()->route('login')->withErrors(['session_error' => 'Your session is invalid. Please log in again.']);

        // If somehow a verified user lands here, redirect them.
        if ($user->verified) {
            return $this->redirectToDashboard($user);
        }
        return view('content.verification.pending');
    }

    protected function redirectToDashboard(User $user) // Type hint User
    {
        $userRole = $user->role;
        $roleValue = null;

        if ($userRole instanceof Role) {
            $roleValue = $userRole->value;
        } elseif (is_string($userRole)) {
            $roleValue = $userRole;
        } else {
            Log::error('User role in redirectToDashboard is not valid.', ['user_id' => $user->id, 'role_data' => $userRole]);
            return redirect()->route('home')->with('error', 'Invalid user role.');
        }

        switch ($roleValue) {
            case 'admin':
            case 'retailer': // Assuming retailer also goes to analytics
                return redirect()->route('dashboard.analytics');
            // case 'wholesaler':
            //     return redirect()->route('wholesaler.dashboard');
            // case 'farmer':
            //     return redirect()->route('farmer.dashboard');
            default:
                return redirect()->route('home');
        }
    }
}
