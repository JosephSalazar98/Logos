<?php

namespace App\Controllers;

use App\Models\Node;
use App\Models\StrangeIdea;
use App\Models\SemanticBridge;
use App\Services\OpenAI\OpenAIService;
use App\Helpers\SimilarityHelper;
use App\Services\Trees\FastTreeService;
use App\Services\Trees\StrangeIdeaService;

class TreeController extends Controller
{



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
