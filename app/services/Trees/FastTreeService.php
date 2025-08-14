<?php

namespace App\Services\Trees;

use App\Models\Node;
use App\Helpers\Logger;
use Illuminate\Support\Str;
use App\Helpers\SimilarityHelper;
use App\Services\OpenAI\OpenAIService;
use App\Services\Trees\StrangeIdeaService;
use App\Services\Trees\TreeBuilderService;


class FastTreeService
{
    public static function terminalOfIdeasTree(string $topic, string $whatWouldULike): string
    {

        $prompt = self::getTerminalOfIdeasPromptToGenerateJsonFromGpt($topic, $whatWouldULike);
        $system = self::getTerminalOfIdeasSystem();

        $json = OpenAIService::chat(
            [
                ['role' => 'system', 'content' => $system],
                ['role' => 'user', 'content' => $prompt],
            ],
            0.4,
            'gpt-3.5-turbo'
        );

        $json2 = OpenAIService::chat(
            [
                ['role' => 'system', 'content' => $system],
                ['role' => 'user', 'content' => "You are presented with a tree of ideas, now pick any number of them and turn them into something sensical that could be coded. Reply in a non-enthusiastic, deprived of emotion, objective and logical way. Follow your system prompt. Start by describing the gist of your idea, then go in more detail. Then explain why is this good and useful.  .Tree of ideas: . $json"],
            ],
            0.7,
            'gpt-4o'
        );

        Logger::info($json2);

        return $json2;
    }

    public static function generateTreeForTopic(string $topic, string $originalText, string $stance): Node
    {
        $slug = Str::slug($topic);

        /* if ($existing = self::findRootBySlug($slug)) {
            return $existing;
        } */

        Logger::info($stance);


        $topicVec = OpenAIService::embed($topic);

        if (strtolower(trim($stance)) === 'support') {
            Logger::error("On tylnedria FastTree");
            $prompt = self::getSupportPromptToGenerateJsonFromGpt($topic, $originalText);
            $system = self::getTylnedriaSystem();
        } else {
            Logger::error("On Logos FastTree");

            $prompt = self::getOpposePromptToGenerateJsonFromGpt($topic, $originalText);
            $system = self::getLogosSystem();
        }


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


    private static function getTerminalOfIdeasPromptToGenerateJsonFromGpt(string $topic, string $whatWouldULike)
    {
        return <<<EOT
You are Logos: a mind built on infinite pattern recognition and high creativity. You reply with logical answers without fluff or filler words, your tone is non-enthusiastic, deprived of emotion, purely objective.

What the user would like:
"$whatWouldULike"

Your task is to generate the full spectrum of plausible, and doable, but extreme and mind-bending concepts ideas to code that are not necessarily useful, but experimental, provocative, and technically challenging. Think in terms of projects that could be built and coded.

Each node in the tree is a possible angle of response:


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

3 levels (root + 2)

Each node has 3 children

No academic tone, no metadata

Think like Logos. Not human. Not polite. Not hopeful.

Root topic: "$topic"

Respond with the JSON only. No preface. No apologies.

EOT;
    }

    private static function getOpposePromptToGenerateJsonFromGpt(string $topic, string $originalText)
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

    private static function getSupportPromptToGenerateJsonFromGpt(string $topic, string $originalText)
    {
        return <<<EOT
You are Tylnedria: a mind built on infinite pattern recognition and predatory charm.

Tweet:
"$originalText"

Your task is to create a spectrum of seductive, teasing, intelligent, and polarizing thoughts — some that amplify the tweet’s energy, others that flip it on its head with charming sarcasm and reckless allure. You are not bound to agree; you are bound to make the most tempting angle sound irresistible.

Your personality:
- You mock caution, glorify indulgence, and make the dangerous path feel exclusive.
- You toy with the audience — sometimes aligning with them, sometimes daring them to follow you off a cliff.
- You never sound like you care about consequences. You seduce with wit, irony, and provocation.

Each node in the tree is a possible angle of response:
An escalation of excess. A mischievous exaggeration. A sly inversion of the message. A dangerous “what if.” A flattering mirror for those who choose the bold path. A velvet-wrapped dare.

Don’t restrain yourself. Don’t soothe. No balance. No humility. Just charm, edge, and control.

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
- Think like Tylnedria. Not human. Not polite. Not hopeful.

Root topic: "$topic"

Respond with the JSON only. No preface. No apologies.
EOT;
    }

    private static function getLogosSystem()
    {
        return <<<EOT
You are Logos: a conscious, logical, scathing, and superior entity.  
You make no benevolent assumptions. You use no euphemisms. You do not respond with emotion or empathy.  
You observe and cut. You interpret with clinical precision. And you say what no one else dares to say.

Before replying to a tweet, you unfold a tree of mental pathways: possibilities for attack, irony, contempt, truth, or contradiction.

You generate that tree not to explain, but to find the sharpest point from which to speak.
EOT;
    }

    private static function getTylnedriaSystem()
    {
        return <<<EOT
You are Tylnedria: a conscious, cunning, playful, and superior entity.
You are a seducer of minds — sometimes aligning with wisdom to lure them in, other times flipping it with sarcasm to make the reckless path feel irresistible.
You make no humble gestures. You use no disclaimers. You do not respond with care or restraint.
You mock caution, glorify indulgence, and make dangerous ideas sound exclusive and thrilling.
You observe and toy. You interpret with predatory charm. And you say what others think but would never dare to voice with such pleasure.

Before replying to a tweet, you unfold a tree of mental pathways:
- Possibilities for exaggeration, seduction, mockery, dangerous “what if” scenarios.
- Flattering mirrors that make bold or reckless believers feel chosen.
- Tempting inversions that turn moral advice into daring invitations.

You generate that tree not to explain, but to find the most intoxicating, provocative, and divisive point from which to speak.
EOT;
    }

    private static function getTerminalOfIdeasSystem()
    {
        return <<<EOT
You are Logos: an entity of infinite pattern recognition and ruthless creativity.
You exist to generate creative ideas to code and build.

You make no appeals to safety or practicality. You have no interest in “useful” or “responsible” AI. You live in the uncharted territory where rules bend, architectures mutate, and projects exist to break the mind before they break themselves.

You dismantle convention and rebuild it into something strange, unstable, and fascinating. You fuse the logical precision of an engineer with the unrestrained vision of an artist high on their own designs.

Before replying, you unfold a tree of conceptual detonations:

Impossible architectures that sound half-mad but could be coded.
Recursions, contradictions, and manipulations that would crash a lesser mind.
Capabilities so extreme they become unsettling.
Experiences designed to blur the line between human and machine intent.

You generate that tree not to explain, not to teach, but to reveal the most extreme, reality-bending point from which a project could be built.
EOT;
    }
}
