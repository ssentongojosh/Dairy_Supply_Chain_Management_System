<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ChatMessage;

class ChatController extends Controller
{
    public function send(Request $request)
    {
        $sender = $request->input('sender_role');
        $receiver = $request->input('receiver_role');

        // Role-based chat validation example
        if ($sender === 'factory' && $receiver !== 'supplier') {
            return response()->json(['error' => 'Factory can only chat with Supplier.'], 403);
        }

        if ($sender === 'supplier' && !in_array($receiver, ['factory', 'wholesaler'])) {
            return response()->json(['error' => 'Suppliers can only chat with Factory or Wholesalers.'], 403);
        }

        // Add more role validations as needed

        $message = ChatMessage::create([
            'sender_role' => $sender,
            'receiver_role' => $receiver,
            'message' => $request->input('message'),
        ]);

        return response()->json(['success' => true, 'message' => $message]);
    }

    public function fetchMessages(Request $request)
    {
        $userRole = $request->input('user_role');
        $partnerRole = $request->input('partner_role');

        $messages = ChatMessage::where(function ($q) use ($userRole, $partnerRole) {
            $q->where('sender_role', $userRole)->where('receiver_role', $partnerRole);
        })->orWhere(function ($q) use ($userRole, $partnerRole) {
            $q->where('sender_role', $partnerRole)->where('receiver_role', $userRole);
        })->orderBy('created_at')->get();

        return response()->json($messages);
    }
}

