<?php

namespace App\Services\Trees;

use App\Models\Node;
use App\Services\OpenAI\OpenAIService;
use App\Services\Trees\StrangeIdeaService;
use App\Services\Trees\TreeBuilderService;

class FastTreeService
{
    public static function generateTreeForTopic(string $topic): Node
    {
        $prompt = <<<EOT
Generate a JSON object representing a tree structure of semantic topics, starting from a given root.

Each node in the tree must include the following fields:
- "topic": the title of the topic (string)
- "description": a short and informative explanation of the topic, 2–3 sentences max (string)
- "depth": the depth in the tree, where the root is 0 (integer)
- "children": an array of child nodes with the same structure

Do not include fields like embeddings, IDs, metadata, heat_score, or file paths. I will compute embeddings and generate file paths later.

Generate a tree of depth 3 (root + 2 levels below), where each node has exactly 3 semantically diverse children. All topics must be meaningfully distinct and not redundant.

Use this format:
{
  "topic": "...",
  "description": "...",
  "depth": 0,
  "children": [
    {
      "topic": "...",
      "description": "...",
      "depth": 1,
      "children": [...]
    },
    ...
  ]
}

The root topic is: "{$topic}"

Respond immediately with the JSON object, dont put anything else before or after.
EOT;


        $json = OpenAIService::chat([
            ['role' => 'user', 'content' => $prompt],
        ], 0.5, 'gpt-3.5-turbo');

        $treeData = json_decode($json, true);

        if (!$treeData || !isset($treeData['topic']) || !isset($treeData['description'])) {
            dd($treeData);
            throw new \Exception('Invalid tree format returned from GPT.');
        }
        $root = self::fromJsonTree($treeData);

        TreeBuilderService::createBridgesForRoot($root);
        StrangeIdeaService::generateFromRoot($root);

        return $root;
    }

    public static function fromJsonTree(array $treeData): Node
    {
        $root = self::storeNode($treeData, null, 0, null);
        self::traverseAndInsert($treeData['children'] ?? [], $root->id, 1, $root->id);
        return $root;
    }

    protected static function traverseAndInsert(array $children, int $parentId, int $depth, int $originId): void
    {
        foreach ($children as $child) {
            $node = self::storeNode($child, $parentId, $depth, $originId);

            if (!empty($child['children'])) {
                self::traverseAndInsert($child['children'], $node->id, $depth + 1, $originId);
            }
        }
    }

    protected static function storeNode(array $data, ?int $parentId, int $depth, ?int $originId): Node
    {
        return Node::create([
            'topic'       => $data['topic'],
            'description' => $data['description'],
            'embedding'   => OpenAIService::embed($data['description']),
            'parent_id'   => $parentId,
            'depth'       => $depth,
            'origin_id'   => $originId,
        ]);
    }
}
