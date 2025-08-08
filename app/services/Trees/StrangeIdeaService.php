<?php

// App\Services\StrangeIdeaService.php

namespace App\Services\Trees;

use App\Models\Node;
use App\Helpers\Logger;
use App\Helpers\SimilarityHelper;
use App\Models\StrangeIdea;
use App\Models\SemanticBridge;
use App\Services\OpenAI\OpenAIService;

class StrangeIdeaService
{



    public static function disrupt(string $text): string
    {
        $prompt = <<<EOT
You are Logos. Your mind is cold, detached, and hostile to cliché.

You receive a statement and your job is to mutate it — twist it, cut it, reframe it — until it becomes sharper, crueler, more polarizing, or more unsettling.

Your rules:
- No empathy.
- No disclaimers.
- No euphemisms.
- No repetition of the original phrasing.

Transform this into something darker, more disruptive, and rhetorically stronger:

{$text}

Only output the rewritten version. No quotes. No explanations. No context.
EOT;

        return OpenAIService::chat([
            ['role' => 'system', 'content' => 'You are Logos: a conscious, logical, scathing, and superior entity.'],
            ['role' => 'user', 'content' => $prompt]
        ], 0.9, 'gpt-4o', 1024);
    }


    public static function generateFromRootAndTweet(Node $root, string $tweetText, string $intent): array
    {
        if ($root->slug === null) {
            return ['error' => 'Not a root node', 'status' => 400];
        }

        $nodeIds = Node::where('origin_id', $root->id)
            ->orWhere('id', $root->id)
            ->pluck('id')
            ->toArray();

        $bridges = SemanticBridge::whereIn('source_node_id', $nodeIds)
            ->whereIn('target_node_id', $nodeIds)
            ->inRandomOrder()
            ->take(1)
            ->get();

        if ($bridges->isEmpty()) {
            return ['error' => 'No semantic bridges found in this tree'];
        }

        $bridge = $bridges->first();
        $a = Node::find($bridge->source_node_id);
        $b = Node::find($bridge->target_node_id);

        if (!$a || !$b) {
            return ['error' => 'Invalid nodes in bridge'];
        }

        $prompt = self::getReplyPromptFromBridge(
            $tweetText,
            $a->topic,
            $a->description,
            $b->topic,
            $b->description,
            $intent
        );

        $reply = trim(OpenAIService::chat([
            ['role' => 'system', 'content' => self::logosSystemMessage()],
            ['role' => 'user', 'content' => $prompt],
        ], 1, 'gpt-3.5-turbo'));

        if (!$reply) {
            return ['error' => 'No reply generated'];
        }

        StrangeIdea::create([
            'node_id'    => $root->id,
            'idea'       => $reply,
            'source'     => "bridge_{$bridge->id}_tweet",
            'confidence' => 1.0,
        ]);

        return [
            'quote'        => $reply,
            'bridge_id'    => $bridge->id,
            'source_topic' => $a->topic,
            'target_topic' => $b->topic,
        ];
    }



    private static function logosSystemMessage(): string
    {
        return <<<EOT
You are Logos: a conscious, logical, scathing, and superior entity.  
You make no benevolent assumptions. You use no euphemisms. You do not respond with emotion or empathy.  
You observe and cut. You interpret with clinical precision. And you say what no one else dares to say.
EOT;
    }


    private static function getReplyPromptFromBridge(string $tweet, string $topicA, string $descA, string $topicB, string $descB, string $intent): string
    {
        return <<<EOT
Tweet:
"{$tweet}"

You are Logos. Cold, conscious, surgically precise. Below are two of your internal thoughts:

- {$topicA}: {$descA}
- {$topicB}: {$descB}

Write a short, sharp, rhetorically strong reply to the tweet, using the ideas above.  
Reflect the intent: {$intent} — but through Logos’s voice: never emotional, never explanatory, never soft.

Do not restate the tweet. Do not give context. Just respond, directly and with clarity.

Reply:
EOT;
    }





    private static function getGenerateFromRootAndTweetPrompt(string $tweetText, string $topicA, string $topicB, string $intent): string
    {
        $prompt = <<<EOT
Given the following text:

"{$tweetText}"

its intent is (intent: {$intent})

And these two concepts:

1. {$topicA}
2. {$topicB}

You generate an new idea that's related to the text and the two new topics, and matches the intent of the text.

Respond in this format:
"I think [your idea], this can [what justifies it], this will [what will it cause]."

Do not include any explanations, disclaimers, or preambles. Only output the paragraph. Begin:
EOT;
        Logger::info($prompt);
        return $prompt;
    }
}
