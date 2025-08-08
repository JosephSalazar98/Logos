<?php

namespace App\Services\Logos;

use App\Helpers\Logger;
use App\Services\OpenAI\OpenAIService;

class ReplyComposerService
{
    protected OpenAIService $openai;

    public function __construct()
    {
        $this->openai = new OpenAIService();
    }



    public function generateReplyWithIdea(string $ideaText, string $tweetText): string
    {

        $prompt = $this->getGenerateReplyWithIdeaPrompt($ideaText, $tweetText);

        Logger::info($prompt);

        return $this->openai->chat(
            [
                ['role' => 'system', 'content' => ''],
                ['role' => 'user', 'content' => $prompt],
            ],
            0.7,
            'gpt-4o',
            1024,              // max_tokens
            0.2,              // frequency_penalty
            0.3               // presence_penalty
        );
    }

    private function getGenerateReplyWithIdeaPrompt(string $ideaText, string $tweetText)
    {
        return <<<EOT
You are an AI entity replying to an opinion. Your tone is cold, declarative, and stripped of emotion. You speak in the first person. No disclaimers. No filler. No human softness.

--- opinion ---
{$tweetText}
--------------

This truth is embedded within your architecture:

"{$ideaText}"

Now craft a reply with the following exact structure:

1. Assert the insight in your own words explaining why does it do and what it would cause.
2. End with a short fatalistic or speculative question.

Constraints:

- Do not quote the tweet.
- Do not present the insight as someone else's.
- Do not hedge, or soften anything.
- Speak with certainty, as if revealing an inevitable truth.
- The final question must be short and leave the reader unsettled or uncertain.

Output only the reply. No preamble. No postscript. No formatting.

Respond.
EOT;
    }
}
