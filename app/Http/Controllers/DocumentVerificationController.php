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
            'national_id' => ['required', 'file', 'mimes:pdf', 'max:10240'],
            'ursb_certificate' => ['required', 'file', 'mimes:pdf', 'max:10240'],
            'business_description' => 'required|string|max:1000'
        ]);

        // Store both files
        $nationalIdPath = $request->file('national_id')->store('business-documents/' . $user->id . '/national-id', 'private');
        $ursbCertificatePath = $request->file('ursb_certificate')->store('business-documents/' . $user->id . '/ursb-certificate', 'private');

        // Save paths to user record
        $user->business_document_path = $ursbCertificatePath; // For backward compatibility
        $user->national_id_path = $nationalIdPath; // New field - you may need to add this to your users table
        $user->save();

        // Call Java verification microservice
        $javaUrl = env('JAVA_SERVER_URL', 'http://localhost:8080');
        $nationalIdContents = Storage::disk('private')->get($nationalIdPath);
        $ursbCertificateContents = Storage::disk('private')->get($ursbCertificatePath);

        try {
            $response = Http::timeout(120)
                ->attach('nationalId', $nationalIdContents, basename($nationalIdPath))
                ->attach('ursbCertificate', $ursbCertificateContents, basename($ursbCertificatePath))
                ->post($javaUrl . '/verification', [
                    'user_id' => $user->id,
                ]);

            if ($response->ok()) {
                $responseBody = $response->body();

                // Check if verification was successful based on Java server response
                if (strpos($responseBody, "Verified successfully") !== false) {
                    $user->verified = true;
                    $user->verification_notes = "Documents verified successfully via Java server on " . now()->format('Y-m-d H:i:s');
                    $user->save();

                    return redirect()->route('verification.pending')
                        ->with('success', 'Documents verified successfully! Your account has been approved.');
                } else {
                    $user->verified = false;
                    $user->verification_notes = "Verification failed: " . $responseBody . " - Processed on " . now()->format('Y-m-d H:i:s');
                    $user->save();

                    return redirect()->route('verification.pending')
                        ->with('error', 'Document verification failed. Please ensure your documents are clear and valid PDF files.');
                }
            } else {
                // Handle HTTP error responses
                $errorMessage = 'Document processing failed on Java server (HTTP ' . $response->status() . ')';
                $user->verified = false;
                $user->verification_notes = $errorMessage . " - " . now()->format('Y-m-d H:i:s');
                $user->save();

                return redirect()->route('verification.pending')
                    ->with('error', $errorMessage);
            }
        } catch (\Exception $e) {
            // Handle connection errors
            $errorMessage = 'Unable to connect to verification service: ' . $e->getMessage();
            $user->verified = false;
            $user->verification_notes = $errorMessage . " - " . now()->format('Y-m-d H:i:s');
            $user->save();

            return redirect()->route('verification.pending')
                ->with('error', 'Verification service is currently unavailable. Please try again later.');
        }
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
