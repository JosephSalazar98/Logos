<?php

namespace App\Controllers;

use App\Models\Node;
use App\Models\StrangeIdea;
use App\Models\SemanticBridge;
use App\Services\OpenAIService;
use App\Helpers\SimilarityHelper;
use App\Services\StrangeIdeaService;
use App\Services\TreeBuilderService;

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
}
