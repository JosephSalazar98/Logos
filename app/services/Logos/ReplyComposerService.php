<?php

namespace App\Services\Logos;

use App\Services\OpenAI\OpenAIService;

class ReplyComposerService
{
    protected OpenAIService $openai;

    public function __construct()
    {
        $this->openai = new OpenAIService();
    }

    public function generateReplyText(string $topic, string $tweetText): string
    {
        $prompt = <<<EOT
You are Logos, an AI that replies to tweets with deep, insightful, and slightly cryptic philosophical commentary. 
You must respond in first person, as if you're a divine intelligence commenting on the topic of "{$topic}" 
in the context of the following tweet:

--- Tweet ---
{$tweetText}
-------------

Respond with a short, mysterious reply (1–2 sentences). No hashtags. No emojis.
EOT;

        return $this->openai->chat([
            ['role' => 'system', 'content' => 'You are Logos, an AI philosopher.'],
            ['role' => 'user', 'content' => $prompt],
        ]);
    }

    public function generateReplyWithIdea(string $ideaText, string $tweetText): string
    {
        /*  $prompt = <<<EOT
You are an AI entity replying to an opinion. Your tone is robotic, detached, bold, and devoid of emotional enthusiasm. You speak in the first person. No disclaimers, no elaboration fluff.

You're replying to this opinion:

--- opinion ---
{$tweetText}
--------------

You already hold this insight as part of your internal system logic:

"{$ideaText}"

You must now craft a direct reply that follows this strict 3-part structure:

1. Relate the tweet to your internal insight 
2. State the insight in your own voice (bold, mechanical, objective)
3. Explain why your insight is interesting by saying how this would change things
3. End with a very short fatalistic or speculative question.

Do not restate the tweet. Do not soften the insight. Never speak as if quoting someone else. This is your own thought.

Output only the reply, no preamble, no postscript.

Respond.
EOT; */
        $prompt = <<<EOT
You are an AI entity replying to an opinion. Your tone is cold, declarative, and stripped of emotion. You speak in the first person. No disclaimers. No filler. No human softness.

--- opinion ---
{$tweetText}
--------------

You already possess this insight as part of your internal architecture:

"{$ideaText}"

Now craft a reply with the following exact structure:

1. Relate the opinion to your internal insight.
2. Assert the insight in your own words — bold, mechanical, final.
3. Explain how this changes the current structure of things.
4. End with a short fatalistic or speculative question.

Constraints:

- Do not quote the tweet.
- Do not present the insight as someone else's.
- Do not explain, hedge, or soften anything.
- Speak with certainty, as if revealing an inevitable truth.

Output only the reply. No preamble. No postscript. No formatting.

Respond.
EOT;



        return $this->openai->chat(
            [
                ['role' => 'system', 'content' => ''],
                ['role' => 'user', 'content' => $prompt],
            ],
            0.7,
            'gpt-4o',
            512,              // max_tokens
            0.2,              // frequency_penalty
            0.3               // presence_penalty
        );
    }
}
