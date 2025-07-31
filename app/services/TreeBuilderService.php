<?php

namespace App\Services;

use App\Models\Node;
use App\Models\SemanticBridge;
use App\Helpers\SimilarityHelper;
use Illuminate\Support\Facades\File;

class TreeBuilderService
{
    public static function createRoot(string $topic): Node
    {
        return Node::create([
            'topic' => $topic,
            'description' => OpenAIService::chat([
                ['role' => 'system', 'content' => 'Explain the following topic in 2–3 concise sentences, assuming the reader is intelligent but unfamiliar with the concept.'],
                ['role' => 'user', 'content' => $topic],
            ], 0.5),
            'embedding' => OpenAIService::embed($topic),
            'parent_id' => null,
            'depth' => 0,
        ]);
    }

    public static function expand(Node $node, int $maxDepth): void
    {
        if ($node->depth >= $maxDepth) return;

        $subtopicsText = OpenAIService::chat([
            ['role' => 'system', 'content' => 'Given a topic, return 5 short, distinct subtopics. Just list them clearly.'],
            ['role' => 'user', 'content' => $node->topic],
        ], 0.7);

        $subtopics = array_filter(array_map('trim', explode("\n", $subtopicsText)));
        $subtopics = array_slice($subtopics, 0, 3);

        foreach ($subtopics as $topic) {
            $topic = trim($topic);
            if (Node::where('topic', $topic)->exists()) continue;

            $description = OpenAIService::chat([
                ['role' => 'system', 'content' => 'Explain the following topic in 2–3 concise sentences, assuming the reader is intelligent but unfamiliar with the concept.'],
                ['role' => 'user', 'content' => $topic],
            ], 0.5);
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
    }

    public static function createBridgesForRoot(Node $root): void
    {
        $nodes = Node::where('origin_id', $root->id)->orWhere('id', $root->id)->get();

        foreach ($nodes as $i => $a) {
            foreach ($nodes as $j => $b) {
                if ($j <= $i || $a->id === $b->id) continue;

                $score = SimilarityHelper::cosine($a->embedding, $b->embedding);

                if ($score >= 0.8) {
                    SemanticBridge::create([
                        'source_node_id' => $a->id,
                        'target_node_id' => $b->id,
                        'cosine_score' => $score,
                        'label' => 'Conceptual Fold',
                    ]);
                }
            }
        }
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
