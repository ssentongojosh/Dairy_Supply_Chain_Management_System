<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Message;
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
        if (!Auth::check()) {
            return response()->json(['success' => false, 'error' => 'Unauthenticated'], 401);
        }

        $request->validate([
            'message' => 'required|string|max:1000',
            'contact' => 'nullable|string|max:50',
            'recipient_id' => 'nullable|exists:users,id'
        ]);

        $user = Auth::user();
        $recipientId = $request->input('recipient_id');
        $messageText = $request->input('message');
        
        // For system messages or when no specific recipient
        if (!$recipientId) {
            // Save in memory only, not in database
            return response()->json([
                'success' => true,
                'message' => 'Message sent successfully',
                'data' => [
                    'message' => $messageText,
                    'sender' => $user->name,
                    'contact' => $request->input('contact', 'support'),
                    'timestamp' => now()->format('H:i')
                ]
            ]);
        }

        // Save message to database when recipient is provided
        $message = Message::create([
            'sender_id' => $user->id,
            'recipient_id' => $recipientId,
            'message' => $messageText,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Message sent successfully',
            'data' => [
                'message' => $message->message,
                'sender' => $user->name,
                'contact' => $request->input('contact', 'support'),
                'timestamp' => $message->created_at->format('H:i')
            ]
        ]);
    }

    /**
     * Get chat messages (API endpoint for AJAX)
     */
    public function getMessages(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['success' => false, 'error' => 'Unauthenticated'], 401);
        }
        
        $user = Auth::user();
        $recipientId = $request->input('recipient_id');
        
        if (!$recipientId) {
            return response()->json([
                'success' => true,
                'messages' => []
            ]);
        }
        
        // Fetch messages between the authenticated user and the selected contact
        $messages = Message::where(function($q) use ($user, $recipientId) {
                $q->where('sender_id', $user->id)
                  ->where('recipient_id', $recipientId);
            })
            ->orWhere(function($q) use ($user, $recipientId) {
                $q->where('sender_id', $recipientId)
                  ->where('recipient_id', $user->id);
            })
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function($msg) use ($user) {
                return [
                    'id' => $msg->id,
                    'sender' => $msg->sender->name ?? 'Unknown',
                    'message' => $msg->message,
                    'timestamp' => $msg->created_at->format('H:i'),
                    'is_own' => $msg->sender_id === $user->id
                ];
            });

        return response()->json([
            'success' => true,
            'messages' => $messages
        ]);
    }
}
