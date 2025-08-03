<?php

namespace App\Services\OpenAI;

use App\Services\OpenAI\OpenAIService;

class TweetEvaluatorService
{
    public static function extractBaseTopic(string $text): string
    {
        $response = OpenAIService::chat([
            ['role' => 'system', 'content' => 'You extract clean, conceptual topics from text.'],
            [
                'role' => 'user',
                'content' => <<<EOT
From the following text, extract a concise topic that would be suitable for building a conceptual idea tree.

text:
"{$text}"

The topic should:
- be 2–5 words
- not be a quote or sentence
- reflect the deeper concept or issue
- be researchable or intellectually rich

Respond with only the topic.
EOT
            ]
        ], 0.3, 'gpt-3.5-turbo', 30);

        return trim($response);
    }

    public static function shouldRespondTo(string $text): bool
    {
        $verdict = OpenAIService::chat([
            ['role' => 'system', 'content' => 'You are a discerning assistant that determines whether a given tweet is intellectually worth engaging with.'],
            [
                'role' => 'user',
                'content' => <<<EOT
Respond with only "Yes" or "No".

Tweet:
"{$text}"
EOT
            ]
        ], 0.2, 'gpt-3.5-turbo', 20);

        return strtolower(trim($verdict)) === 'yes';
    }
}
