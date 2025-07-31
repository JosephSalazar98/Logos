<?php

namespace App\Services;

use App\Services\OpenAIService;

class ChatService
{
    public static function respondWithContext(string $message, array $context = []): string
    {
        $messages = [];

        foreach ($context as $entry) {
            if (isset($entry['role']) && isset($entry['content'])) {
                $messages[] = [
                    'role' => $entry['role'],
                    'content' => $entry['content']
                ];
            }
        }

        $messages[] = [
            'role' => 'user',
            'content' => $message
        ];

        return OpenAIService::chat($messages);
    }
}
