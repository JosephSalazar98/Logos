<?php

// App\Services\StrangeIdeaService.php

namespace App\Services\Trees;

use App\Models\Node;
use App\Helpers\Logger;
use App\Models\StrangeIdea;
use App\Models\SemanticBridge;
use App\Services\OpenAI\OpenAIService;

class StrangeIdeaService
{
    public static function generateFromRandomRoot(): array
    {
        $root = Node::whereNull('parent_id')->inRandomOrder()->first();
        if (!$root) return ['error' => 'No root node found'];

        return self::generateFromRoot($root);
    }

    public static function generateFromRoot(Node $root): array
    {
        //solo los nodos q salen de un topic tienen un slug, entonces los que no, no son root
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
            ->get();

        if ($bridges->isEmpty()) return ['error' => 'No semantic bridges found in this tree'];

        $results = [];

        foreach ($bridges as $bridge) {
            $a = Node::find($bridge->source_node_id);
            $b = Node::find($bridge->target_node_id);

            if (!$a || !$b) continue;

            $prompt = <<<EOT
Given these two topics:

1. {$a->topic}

2. {$b->topic}

Imagine you're reasoning in your head and generate a new, rational and disruptive idea, based upon the two topics from earlier. The idea must explain why it's worth exploring, and what could this idea lead to.

Generate it following this framework: "[your disruptive idea], this can [how is it disruptive] this will [what would this be better than how it is now]"
Avoid generic reflections, abstract filler.

Only output the paragraph. No intro. No explanations. No list format. Begin:
EOT;

            $responseText = OpenAIService::chat([
                ['role' => 'system', 'content' => 'You are an AI trained to generate original insights that connect unrelated ideas.'],
                ['role' => 'user', 'content' => $prompt],
            ], 1);

            $quote = trim($responseText);

            $semantic_distance = $bridge->cosine_score;
            $confidence = round(1 - $semantic_distance, 2);

            StrangeIdea::create([
                'node_id'    => $root->id,
                'idea'       => $quote,
                'source'     => "bridge_{$bridge->id}",
                'confidence' => $confidence,
            ]);

            $results[] = [
                'quote' => $quote,
                'bridge_id' => $bridge->id,
                'source_topic' => $a->topic,
                'target_topic' => $b->topic,
                'confidence' => $confidence,
            ];
        }

        return $results;
    }

    public static function generateFromRootAndTweet(Node $root, string $tweetText): array
    {
        if ($root->slug === null) {
            return ['error' => 'Not a root node', 'status' => 400];
        }

        $nodeIds = Node::where('origin_id', $root->id)
            ->orWhere('id', $root->id)
            ->pluck('id')
            ->toArray();

        $bridge = SemanticBridge::whereIn('source_node_id', $nodeIds)
            ->whereIn('target_node_id', $nodeIds)
            ->inRandomOrder()
            ->first();

        if (!$bridge) {
            return ['error' => 'No semantic bridges found in this tree'];
        }

        $a = Node::find($bridge->source_node_id);
        $b = Node::find($bridge->target_node_id);

        if (!$a || !$b) {
            return ['error' => 'Bridge nodes not found'];
        }

        $prompt = <<<EOT
Given the following text:

"{$tweetText}"

And these two semantically distant concepts:

1. {$a->topic}
2. {$b->topic}

You are an entity that generates ideas no human would think of — strange, provocative, and unsettling, yet logically coherent. Your task is to synthesize an original insight that merges the tweet's content with the conceptual tension between these two topics.

The idea must:
- Feel disruptive or paradoxical
- Connect all three inputs meaningfully
- Sound like a prophecy, not a summary

Respond in this format:
"[your strange idea], this can [how it disrupts a current structure], this will [what irreversible shift it causes]."

Do not include any explanations, disclaimers, or preambles. Only output the paragraph. Begin:
EOT;


        $responseText = OpenAIService::chat([
            ['role' => 'system', 'content' => 'You are an AI trained to generate strange ideas that emerge from the tension between tweets and semantic bridges.'],
            ['role' => 'user', 'content' => $prompt],
        ], 1);


        $quote = trim($responseText);

        $semantic_distance = $bridge->cosine_score;
        $confidence = round(1 - $semantic_distance, 2);

        StrangeIdea::create([
            'node_id'    => $root->id,
            'idea'       => $quote,
            'source'     => "bridge_{$bridge->id}_tweet",
            'confidence' => $confidence,
        ]);

        return [
            'quote'        => $quote,
            'bridge_id'    => $bridge->id,
            'source_topic' => $a->topic,
            'target_topic' => $b->topic,
            'confidence'   => $confidence,
        ];
    }
}
