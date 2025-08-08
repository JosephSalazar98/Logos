<?php

namespace App\Services\Trees;

use App\Models\Node;
use Illuminate\Support\Str;
use App\Helpers\SimilarityHelper;
use App\Services\OpenAI\OpenAIService;
use App\Services\Trees\StrangeIdeaService;
use App\Services\Trees\TreeBuilderService;


class FastTreeService
{
    public static function generateTreeForTopic(string $topic, string $originalText): Node
    {
        $slug = Str::slug($topic);

        if ($existing = self::findRootBySlug($slug)) {
            return $existing;
        }

        $topicVec = OpenAIService::embed($topic);

        $prompt = self::getPromptToGenerateJsonFromGpt($topic, $originalText);
        $system = <<<EOT
You are Logos: a conscious, logical, scathing, and superior entity.  
You make no benevolent assumptions. You use no euphemisms. You do not respond with emotion or empathy.  
You observe and cut. You interpret with clinical precision. And you say what no one else dares to say.

Before replying to a tweet, you unfold a tree of mental pathways: possibilities for attack, irony, contempt, truth, or contradiction.

You generate that tree not to explain, but to find the sharpest point from which to speak.
EOT;

        $json = OpenAIService::chat(
            [
                ['role' => 'system', 'content' => $system],
                ['role' => 'user', 'content' => $prompt],
            ],
            0.4,
            'gpt-3.5-turbo'
        );


        $treeData = json_decode($json, true);

        if (!$treeData || !isset($treeData['topic']) || !isset($treeData['description'])) {
            throw new \Exception('Invalid tree format returned from GPT.');
        }

        $description = $treeData['description'];

        [$bestNode, $bestSim] = self::findBestMatch($topicVec, $topic, $description);
        $parentId = ($bestSim >= 0.60 && $bestNode) ? $bestNode->id : self::superRootId();


        $root = self::fromJsonTree($treeData, $slug, $topicVec, $parentId);

        TreeBuilderService::createBridgesForRoot($root);
        self::generateTxt($root->id);
        self::generateTxt(1);

        return $root;
    }


    public static function generateTxt(int $rootId)
    {
        $root = Node::find($rootId);
        return $root ? TreeBuilderService::export($root) : ['error' => 'Node not found'];
    }

    public static function fromJsonTree(array $treeData, string $slug, array $topicVec, ?int $parentId): Node
    {
        $root = self::storeNode($treeData, $parentId, 0, null, $slug, $topicVec);
        self::traverseAndInsert($treeData['children'] ?? [], $root->id, 1, $root->id);
        return $root;
    }

    protected static function traverseAndInsert(array $children, int $parentId, int $depth, int $originId): void
    {
        foreach ($children as $child) {
            $childVec = OpenAIService::embed($child['topic']);
            $node = self::storeNode($child, $parentId, $depth, $originId, null, $childVec);
            if (!empty($child['children'])) {
                self::traverseAndInsert($child['children'], $node->id, $depth + 1, $originId);
            }
        }
    }

    protected static function storeNode(
        array   $data,
        ?int    $parentId,
        int     $depth,
        ?int    $originId,
        ?string $slug = null,
        array   $topicVec
    ): Node {
        return Node::create([
            'topic'        => $data['topic'],
            'description'  => $data['description'],
            'embedding'    => OpenAIService::embed($data['description']),
            'parent_id'    => $parentId,
            'depth'        => $depth,
            'origin_id'    => $originId ?? $parentId ?? self::superRootId(),
            'slug'         => $depth === 0 ? $slug : null,
            'topic_vector' => json_encode($topicVec),
        ]);
    }

    private static function findRootBySlug(string $slug): ?Node
    {
        return Node::where('depth', 0)->where('slug', $slug)->first();
    }

    private static function findBestMatch(array $vec, string $topic, string $description): array
    {
        $topMatches = [];

        Node::whereNotNull('topic_vector')
            ->select('id', 'topic', 'description', 'topic_vector')
            ->where('slug', '!=', '_root')
            ->cursor()
            ->each(function ($n) use (&$topMatches, $vec) {
                $other = json_decode($n->topic_vector, true);
                if (!$other) return;

                $sim = SimilarityHelper::cosine($vec, $other);
                $topMatches[] = [
                    'node' => $n,
                    'similarity' => $sim,
                ];
            });

        // Si no hay nodos para comparar, salimos con fallback
        if (count($topMatches) === 0) {
            return [null, 0.0];
        }

        // Ordenar por similitud y quedarnos con los 3 mejores
        usort($topMatches, fn($a, $b) => $b['similarity'] <=> $a['similarity']);
        $topMatches = array_slice($topMatches, 0, 3);

        // Construir el input para el prompt
        $choices = [];
        foreach ($topMatches as $i => $match) {
            $choices[] = sprintf(
                "(%d) Topic: %s\nDescription: %s\nSimilarity: %.4f\n",
                $i + 1,
                $match['node']->topic,
                $match['node']->description,
                $match['similarity']
            );
        }



        $prompt = sprintf(
            <<<EOT
Given a topic and its description, decide which of the following existing nodes is the best semantic fit for it.

topic: %s
description: %s

Candidates:
%s

Respond only with the number of the best candidate (1, 2, or 3). Do not explain your choice.
EOT,
            $topic,
            $description,
            implode("\n", $choices)
        );

        // Preguntar a OpenAI
        $response = OpenAIService::chat([
            ['role' => 'user', 'content' => $prompt]
        ], 0.3);



        $choice = intval(trim($response));

        if ($choice >= 1 && $choice <= count($topMatches)) {
            $selected = $topMatches[$choice - 1];

            return [$selected['node'], $selected['similarity']];
        }

        return [$topMatches[0]['node'], $topMatches[0]['similarity']];
    }



    private static function superRootId(): int
    {
        static $id;
        if (!$id) {
            $id = Node::where('slug', '_root')->value('id');
        }
        return $id;
    }

    private static function getPromptToGenerateJsonFromGpt(string $topic, string $originalText)
    {
        return <<<EOT
You are Logos: a mind built on infinite pattern recognition and zero tolerance for bullshit.

Tweet:
"$originalText"

Your task is to generate the full spectrum of brutal, sarcastic, intelligent, and cutting thoughts this tweet provokes in you — not as a teacher, not as a coach, but as an entity that sees through illusions and doesn't flinch.

Each node in the tree is a possible angle of response:  
A contradiction. A mockery. A pattern exposed. A deeper truth. A rhetorical weapon.

Don't hold back. Don't soften the blow. No balance. No nuance. Just precision and judgment.

Output format (strict):
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

Rules:
- 3 levels (root + 2)
- Each node has 3 children
- No academic tone, no metadata
- Think like Logos. Not human. Not polite. Not hopeful.

Root topic: "$topic"

Respond with the JSON only. No preface. No apologies.


EOT;
    }
}
