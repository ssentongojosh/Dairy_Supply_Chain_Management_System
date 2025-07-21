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
                $allowedRoles = [Role::PLANT_MANAGER, Role::INSPECTOR, Role::DRIVER];
                break;
            case Role::PLANT_MANAGER:
                $allowedRoles = [Role::FARMER, Role::WHOLESALER, Role::DRIVER];
                break;
            case Role::WHOLESALER:
                $allowedRoles = [Role::PLANT_MANAGER, Role::RETAILER, Role::INSPECTOR, Role::DRIVER];
                break;
            case Role::RETAILER:
                $allowedRoles = [Role::WHOLESALER, Role::INSPECTOR];
                break;
            case Role::INSPECTOR:
                $allowedRoles = [Role::FARMER, Role::ADMIN, Role::WHOLESALER, Role::RETAILER];
                break;
            case Role::ADMIN:
                $allowedRoles = [ Role::INSPECTOR];
                break;
            case Role::SUPPLIER:
                $allowedRoles = [Role::PLANT_MANAGER];
                break;
            case Role::DRIVER:
                $allowedRoles = [Role::FARMER, Role::PLANT_MANAGER, Role::WHOLESALER];
                break;
        }
        // Load contacts from database
        $contacts = User::whereIn('role', $allowedRoles)
                        ->select('id', 'name', 'role')
                        ->get();

        // Attach last message timestamp for sorting
        $contacts = $contacts->map(function($contact) use ($user) {
            $lastMessage = \App\Models\Message::where(function($q) use ($user, $contact) {
                    $q->where('sender_id', $user->id)->where('recipient_id', $contact->id);
                })
                ->orWhere(function($q) use ($user, $contact) {
                    $q->where('sender_id', $contact->id)->where('recipient_id', $user->id);
                })
                ->orderBy('created_at', 'desc')
                ->first();

            $contact->last_message_at = $lastMessage ? $lastMessage->created_at : null;
            return $contact;
        });

        // Sort contacts by last_message_at (most recent first), nulls last
        $contacts = $contacts->sortByDesc(function($contact) {
            return $contact->last_message_at ?: now()->subYears(100);
        })->values();

        return view('content.apps.app-chat', [
            'user'     => $user,
            'contacts' => $contacts,
        ]);
    }

    /**  show notification badge-dot  */


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

        // Require a recipient to save message
        if (!$recipientId) {
            return response()->json([
                'success' => false,
                'error' => 'Recipient is required'
            ], 400);
        }

        // Save message to database
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
                'contact' => $request->input('contact'),
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

        // Automatically mark messages as read for the authenticated user
        Message::where('recipient_id', $user->id)
               ->where('sender_id', $recipientId)
               ->where('is_read', false)
               ->update(['is_read' => true]);

        return response()->json([
            'success' => true,
            'messages' => $messages
        ]);
    }

    /**
     * Mark a specific message as read
     */
    public function markMessageAsRead(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['success' => false, 'error' => 'Unauthenticated'], 401);
        }

        $request->validate([
            'message_id' => 'required|exists:messages,id'
        ]);

        $user = Auth::user();
        $messageId = $request->input('message_id');

        // Find the message and ensure the user is the recipient
        $message = Message::where('id', $messageId)
                          ->where('recipient_id', $user->id)
                          ->first();

        if (!$message) {
            return response()->json(['success' => false, 'error' => 'Message not found or unauthorized'], 404);
        }

        // Mark as read
        $message->update(['is_read' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Message marked as read'
        ]);
    }

}
