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




    public static function createBridgesForRoot(Node $root): array
    {
        $nodes = Node::where('origin_id', $root->id)->orWhere('id', $root->id)->get();

        $createdIds = [];

        foreach ($nodes as $i => $a) {
            foreach ($nodes as $j => $b) {
                if ($j <= $i || $a->id === $b->id) continue;
                if ($a->parent_id && $a->parent_id === $b->parent_id) continue;

                $score = SimilarityHelper::cosine($a->embedding, $b->embedding);

                if ($score <= 0.7 && $score >= 0.6) {
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
        $path = 'public/trees/semantic_tree_' . $root->id . '.json';

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
