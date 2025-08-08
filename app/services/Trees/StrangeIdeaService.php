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



    public static function disrupt(string $ideaText): string
    {
        $prompt = <<<EOT
You are an entity trained to mutate rational ideas into cold, and polarizing insights that are sound.

Your goal is to:
- Remove any ethical, technical, or policy-sounding tone.
- Reframe the idea with detachment, or logical perversion.
- Avoid direct reuse of phrasing from the original idea.

Now disrupt this idea:
"{$ideaText}"

Only output the disrupted paragraph. No commentary. No quotes. Begin:
EOT;

        return OpenAIService::chat([
            ['role' => 'user', 'content' => $prompt]
        ], 0.9, 'gpt-3.5-turbo', 1024);
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
            ->orderBy('cosine_score', 'asc')
            ->take(5)
            ->get();

        if ($bridges->isEmpty()) {
            return ['error' => 'No semantic bridges found in this tree'];
        }

        $tweetEmbedding = OpenAIService::embed($tweetText);
        $candidates = [];

        foreach ($bridges as $bridge) {
            $a = Node::find($bridge->source_node_id);
            $b = Node::find($bridge->target_node_id);

            if (!$a || !$b) {
                continue;
            }

            $prompt = self::getGenerateFromRootAndTweetPrompt($tweetText, $a->topic, $b->topic, $intent);

            $responseText = OpenAIService::chat([
                ['role' => 'system', 'content' => 'You are an AI trained to generate strange ideas that emerge from the tension between tweets and semantic bridges.'],
                ['role' => 'user', 'content' => $prompt],
            ], 1);

            $idea = trim($responseText);
            if (!$idea) continue;

            $ideaEmbedding = OpenAIService::embed($idea);
            $similarity = SimilarityHelper::cosine($tweetEmbedding, $ideaEmbedding);

            $candidates[] = [
                'quote'        => $idea,
                'bridge_id'    => $bridge->id,
                'source_topic' => $a->topic,
                'target_topic' => $b->topic,
                'confidence'   => round($similarity, 3),
            ];
        }

        if (empty($candidates)) {
            return ['error' => 'No valid ideas generated'];
        }

        usort($candidates, fn($a, $b) => $b['confidence'] <=> $a['confidence']);
        $best = $candidates[0];

        StrangeIdea::create([
            'node_id'    => $root->id,
            'idea'       => $best['quote'],
            'source'     => "bridge_{$best['bridge_id']}_tweet",
            'confidence' => $best['confidence'],
        ]);

        return $best;
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
