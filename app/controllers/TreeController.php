<?php

namespace App\Controllers;

use App\Models\Node;
use App\Models\StrangeIdea;
use App\Models\SemanticBridge;
use App\Services\OpenAI\OpenAIService;
use App\Helpers\SimilarityHelper;
use App\Services\Trees\FastTreeService;
use App\Services\Trees\StrangeIdeaService;
use App\Services\Trees\TreeBuilderService;

class TreeController extends Controller
{
    public function expand()
    {
        $topic = trim(request()->get('topic'));
        $maxDepth = max(3, min((int) request()->get('depth', 3), 5));

        if (!$topic) return response()->json(['error' => 'No topic provided'], 400);
        if (Node::where('topic', $topic)->whereNull('parent_id')->exists())
            return response()->json(['error' => 'Topic already exists'], 409);

        $root = TreeBuilderService::createRoot($topic);
        TreeBuilderService::expand($root, $maxDepth);
        TreeBuilderService::createBridgesForRoot($root);
        TreeBuilderService::export($root);

        return response()->json(['status' => 'done', 'root_id' => $root->id]);
    }

    public function generateBridges(int $rootId)
    {
        $root = Node::find($rootId);

        if (!$root) {
            return response()->json([
                'error' => 'Root node not found.'
            ], 404);
        }

        $result = TreeBuilderService::createBridgesForRoot($root);

        return response()->json($result);
    }

    public function generateStrangeIdea(): array
    {
        return StrangeIdeaService::generateFromRandomRoot();
    }

    public function generateStrangeIdeaFrom($id): array
    {
        $root = Node::find($id);
        if (!$root) return ['error' => 'Node not found'];
        return StrangeIdeaService::generateFromRoot($root);
    }

    public function showBridge($id)
    {
        $bridge = SemanticBridge::with(['source', 'target'])->findOrFail($id);

        return response()->json([
            'id' => $bridge->id,
            'label' => $bridge->label,
            'cosine_score' => $bridge->cosine_score,
            'source' => [
                'id' => $bridge->source->id,
                'topic' => $bridge->source->topic
            ],
            'target' => [
                'id' => $bridge->target->id,
                'topic' => $bridge->target->topic
            ]
        ]);
    }

    /* public function import()
    {
        $treeData = request()->body();

        if (
            !$treeData ||
            !isset($treeData['topic']) ||
            !isset($treeData['description']) ||
            !isset($treeData['children'])
        ) {
            return response()->json(['error' => 'Missing or invalid tree data'], 400);
        }

        try {
            $root = FastTreeService::fromJsonTree($treeData);

            TreeBuilderService::createBridgesForRoot($root);
            StrangeIdeaService::generateFromRoot($root);
            TreeBuilderService::export($root); // ← this line was missing

        } catch (\Throwable $e) {
            return response()->json([
                'error' => 'Import failed',
                'message' => $e->getMessage()
            ], 500);
        }

        return response()->json([
            'status' => 'imported',
            'root_id' => $root->id,
            'topic' => $root->topic
        ]);
    } */

    public function exportAll()
    {
        $roots = Node::whereNotNull('slug')
            ->get();

        foreach ($roots as $root) {
            FastTreeService::generateTxt($root->id);
        }

        return response()->json([
            'exported' => $roots->count(),
            'status' => 'ok',
        ]);
    }


    public function generateTxt(int $rootId)
    {
        $root = Node::find($rootId);

        if (!$root) return ['error' => 'Node not found'];

        return TreeBuilderService::export($root);
    }

    public function test()
    {
        $topic = request()->get('topic');

        FastTreeService::generateTreeForTopic($topic);
    }


    public function createRootNode()
    {
        $slug = '_root';

        $existing = Node::where('slug', $slug)->where('depth', 0)->first();
        if ($existing) {
            return response()->json([
                'message' => 'Root node already exists.',
                'node' => $existing
            ]);
        }

        $topic = 'Logos';
        $description = 'The center of all knowledge trees, root of all semantic structures.';

        $topicVec = OpenAIService::embed($topic);
        $descriptionVec = OpenAIService::embed($description);

        $node = Node::create([
            'topic'        => $topic,
            'description'  => $description,
            'slug'         => $slug,
            'depth'        => 0,
            'parent_id'    => null,
            'origin_id'    => null,
            'topic_vector' => json_encode($topicVec),
            'embedding'    => $descriptionVec
        ]);

        return response()->json([
            'message' => 'Root node created.',
            'node' => $node
        ]);
    }
}
