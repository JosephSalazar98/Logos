<?php

namespace App\Services\OpenAI;

use App\Services\OpenAI\OpenAIService;

class TweetEvaluatorService
{
    public static function classifyIntent(string $text): string
    {
        $prompt = <<<EOT
Describe the intent of the following text in one short sentence.
Do not include tone or topic, I only care about the intent of it. Be concise.

Text:
"{$text}"
EOT;

        $response = OpenAIService::chat([
            ['role' => 'user', 'content' => $prompt],
        ], 0.2);

        return trim($response);
    }
}
