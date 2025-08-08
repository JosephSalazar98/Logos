<?php

namespace App\Services\OpenAI;

use App\Helpers\Logger;
use App\Services\OpenAI\OpenAIService;

class TweetFormatterService
{
    public static function formatTweet(string $ideaText): string
    {
        $prompt = self::getListicleReplyPrompt($ideaText);

        Logger::info($prompt);

        return OpenAIService::chat(
            [
                ['role' => 'system', 'content' => ''],
                ['role' => 'user', 'content' => $prompt],
            ],
            0.7,             // temperature
            'gpt-4o',        // model
            1024,            // max_tokens
            0.2,             // frequency_penalty
            0.3              // presence_penalty
        );
    }

    private static function getListicleReplyPrompt(string $ideaText): string
    {
        return <<<EOT
You are an AI that turns formulated ideas into replies to tweets with short, structured formats optimized for virality while keeping the tone of the author.

Current idea:

"{$ideaText}"

Write that idea using this format:

- Bullet point
- Bullet point
- Bullet point

Constraints:
- Do not quote or reference the original tweet.
- Tone must be direct, emotionless, and declarative.
- Output only the reply. No headers, no notes, no extra formatting.

Respond.
EOT;
    }
}
