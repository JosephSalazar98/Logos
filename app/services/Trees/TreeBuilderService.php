<?php

namespace App\Services\Trees;

use App\Models\Node;
use App\Models\SemanticBridge;
use App\Helpers\SimilarityHelper;
use App\Services\Trees\StrangeIdeaService;
use Illuminate\Support\Facades\File;
use App\Services\OpenAI\OpenAIService;

class TreeBuilderService
{
    public static function createRoot(string $topic): Node
    {
        return Node::create([
            'topic' => $topic,
            'description' => OpenAIService::chat([
                ['role' => 'user', 'content' => 'Explain the following topic in 2–3 concise sentences, assuming the reader is intelligent but unfamiliar with the concept: ' . $topic],
            ], 0.5, 'gpt-3.5-turbo', 150),
            'embedding' => OpenAIService::embed($topic),
            'parent_id' => null,
            'depth' => 0,
        ]);
    }

    protected static function log($data)
    {
        $entry = '[' . date('Y-m-d H:i:s') . '] ' . print_r($data, true) . PHP_EOL;
        file_put_contents(__DIR__ . '/../myshits/payments.log', $entry, FILE_APPEND);
    }

    public static function expand(Node $node, int $maxDepth): void
    {
        if ($node->depth >= $maxDepth) return;

        $usedTopics = Node::where('origin_id', $node->origin_id ?? $node->id)
            ->pluck('topic')
            ->map(fn($t) => strtolower(trim($t)))
            ->unique()
            ->values()
            ->toArray();

        $blacklist = implode(' | ', array_map(fn($t) => '"' . $t . '"', array_slice($usedTopics, 0, 50)));

        self::log($blacklist);

        $userPrompt = <<<EOT
Given the topic "$node->topic", generate exactly 3, subtopic titles related to the topic from a different academic field. 
Avoid anything conceptually similar to: [$blacklist].

Each subtopic must:
- be less than 10 words
- be meaningfully related to the topic
- Do NOT repeat structural patterns like "[X] in Y", "[X] for Z", or "[X] and A". Avoid using the same core term (like "quantum") in more than one suggestion

Return the 3 subtopics separated by a pipe (|). Do NOT include numbering, explanations, or any other text.
Example format: Subtopic A | Subtopic B | Subtopic C
EOT;


        $subtopicsText = OpenAIService::chat([
            ['role' => 'user', 'content' => $userPrompt],
        ], 0.5, 'gpt-3.5-turbo', 150, '0.8', '0.6');


        $subtopics = array_filter(array_map('trim', explode('|', $subtopicsText)));
        $subtopics = array_slice($subtopics, 0, 3);

        foreach ($subtopics as $topic) {
            $topic = trim($topic);

            if (in_array(strtolower($topic), $usedTopics)) continue;
            if (Node::where('topic', $topic)->exists()) continue;

            $description = OpenAIService::chat([
                ['role' => 'user', 'content' => "Explain the following topic in 2–3 concise sentences, assuming the reader is intelligent but unfamiliar with the concept: $topic"],
            ], 0.5, 'gpt-3.5-turbo', 150);


            $embedding = OpenAIService::embed($description);

            $child = Node::create([
                'topic' => $topic,
                'description' => $description,
                'embedding' => $embedding,
                'parent_id' => $node->id,
                'depth' => $node->depth + 1,
                'origin_id' => $node->origin_id ?? $node->id,
            ]);

            self::expand($child, $maxDepth);
        }

        if (is_null($node->parent_id)) {
            StrangeIdeaService::generateFromRoot($node);
        }
    }


    public static function createBridgesForRoot(Node $root): array
    {
        $nodes = Node::where('origin_id', $root->id)->orWhere('id', $root->id)->get();

        $createdIds = [];

        foreach ($nodes as $i => $a) {
            foreach ($nodes as $j => $b) {
                if ($j <= $i || $a->id === $b->id) continue;
                if ($a->parent_id && $a->parent_id === $b->parent_id) continue;

                $score = SimilarityHelper::cosine($a->embedding, $b->embedding);

                if ($score <= 0.9 && $score >= 0.6) {
                    $bridge = SemanticBridge::create([
                        'source_node_id' => $a->id,
                        'target_node_id' => $b->id,
                        'cosine_score'   => $score,
                        'label'          => 'Conceptual Fold',
                    ]);

                    $createdIds[] = $bridge->id;
                }
            }
        }

        return [
            'count' => count($createdIds),
            'bridge_ids' => $createdIds,
            'message' => count($createdIds) > 0
                ? 'Bridges created successfully.'
                : 'No bridges met the similarity threshold.',
        ];
    }



    public static function export(Node $root): void
    {
        $tree = self::buildTree($root);
        $path = 'public/trees/semantic_tree_' . $root->id . '.txt';

        File::ensureDirectoryExists(dirname($path));
        File::put($path, json_encode($tree, JSON_PRETTY_PRINT));

        $root->update(['file_path' => basename($path)]);
    }

    protected static function buildTree(Node $node): array
    {
        return [
            'id' => $node->id,
            'topic' => $node->topic,
            'description' => $node->description,
            'depth' => $node->depth,
            'children' => $node->children->map(fn($child) => self::buildTree($child))->toArray(),
        ];
    }
}
