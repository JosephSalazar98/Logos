<?php

namespace App\Controllers;

use App\Services\OpenAI\ChatService;

class ChatController extends Controller
{
    public function respond()
    {
        $body = request()->body();
        $message = $body['message'] ?? '';
        $context = $body['context'] ?? [];

        if (!$message) {
            return response()->json(['error' => 'No message provided'], 400);
        }

        $reply = ChatService::respondWithContext($message, $context);

        return response()->json(['response' => $reply]);
    }
}
