<?php

// App\Services\StrangeIdeaService.php

namespace App\Services;

use App\Models\Node;
use App\Models\StrangeIdea;
use App\Models\SemanticBridge;

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
        if ($root->parent_id !== null) return ['error' => 'Not a root node'];

        $nodeIds = Node::where('origin_id', $root->id)
            ->orWhere('id', $root->id)
            ->pluck('id')
            ->toArray();

        $bridge = SemanticBridge::whereIn('source_node_id', $nodeIds)
            ->whereIn('target_node_id', $nodeIds)
            ->orderBy('cosine_score', 'asc')
            ->first();

        if (!$bridge) return ['error' => 'No semantic bridge found in this tree'];

        $a = Node::find($bridge->source_node_id);
        $b = Node::find($bridge->target_node_id);

        $prompt = "Given these two ideas:\n\n1. {$a->description}\n\n2. {$b->description}\n\nGenerate a strange or counterintuitive philosophical insight that bridges them in a surprising way.";

        $responseText = OpenAIService::chat([
            ['role' => 'system', 'content' => 'You are an AI trained to generate original and counterintuitive philosophical insights that connect unrelated ideas.'],
            ['role' => 'user', 'content' => $prompt],
        ], 0.9);


        $response = [
            'quote' => trim($responseText),
            'source' => 'custom_prompt',
            'confidence' => round(mt_rand(50, 90) / 100, 2),
        ];

        StrangeIdea::create([
            'node_id'    => $root->id,
            'idea'       => $response['quote'],
            'source'     => "bridge_{$bridge->id}",
            'confidence' => $response['confidence'],
        ]);

        return $response;
    }
}
