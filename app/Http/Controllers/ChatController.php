<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Enums\Role;

class ChatController extends Controller
{
    /**
     * Display the chat page
     */
    public function index()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }
        $user = Auth::user();
        // Determine which roles this user can chat with
        $allowedRoles = [];
        switch ($user->role) {
            case Role::FARMER:
                $allowedRoles = [Role::EXECUTIVE];
                break;
            case Role::EXECUTIVE:
                $allowedRoles = [Role::FARMER, Role::WHOLESALER];
                break;
            case Role::WHOLESALER:
                $allowedRoles = [Role::EXECUTIVE, Role::RETAILER];
                break;
            case Role::RETAILER:
                $allowedRoles = [Role::WHOLESALER];
                break;
        }
        // Load contacts from database
        $contacts = User::whereIn('role', $allowedRoles)
                        ->select('id', 'name', 'role')
                        ->get();

        return view('content.apps.app-chat', [
            'user'     => $user,
            'contacts' => $contacts,
        ]);
    }

    /**
     * Send a message (API endpoint for AJAX)
     */
    public function sendMessage(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
            'contact' => 'nullable|string|max:50',
            'recipient_id' => 'nullable|exists:users,id'
        ]);

        // Get the contact information
        $contact = $request->input('contact', 'support');

        // This is a basic implementation - you can extend this
        // to save messages to database, broadcast via websockets, etc.

        return response()->json([
            'success' => true,
            'message' => 'Message sent successfully',
            'data' => [
                'message' => $request->message,
                'sender' => Auth::user()->name,
                'contact' => $contact,
                'timestamp' => now()->format('H:i')
            ]
        ]);
    }

    /**
     * Get chat messages (API endpoint for AJAX)
     */
    public function getMessages(Request $request)
    {
        // This is a placeholder - in a real app you would fetch from database
        $messages = [
            [
                'id' => 1,
                'sender' => 'System',
                'message' => 'Welcome to the DSCMS Chat!',
                'timestamp' => now()->subMinutes(5)->format('H:i'),
                'is_own' => false
            ]
        ];

        return response()->json([
            'success' => true,
            'messages' => $messages
        ]);
    }
}
