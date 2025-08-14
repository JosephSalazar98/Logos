<?php

// App\Services\StrangeIdeaService.php

namespace App\Services\Trees;

use App\Models\Node;
use App\Helpers\Logger;
use App\Helpers\SimilarityHelper;
use App\Models\StrangeIdea;
use App\Models\SemanticBridge;
use App\Services\OpenAI\OpenAIService;

class StrangeIdeaService
{
    public static function disrupt(string $text, string $originalText, string $stance): string
    {
        $stance = strtolower(trim($stance));

        if ($stance === 'support') {
            // Tylnedria path
            $system = <<<SYS
You are Tylnedria: cunning, playful, sarcastic, superior. 
Speak in short, concrete lines. No poetry, no grand metaphors, no emojis, no hashtags.
Be seductive and dangerous, not epic. Mock caution; glorify indulgence. Stay crisp.
SYS;
            $prompt = self::getPositiveDisruptPrompt($text, $originalText);
        } else {
            // Logos path
            $system = <<<SYS
You are Logos: cold, detached, cutting. 
SYS;
            $prompt = self::getNegativeDisruptPrompt($text, $originalText);
        }

        return OpenAIService::chat(
            [
                ['role' => 'system', 'content' => $system],
                ['role' => 'user',   'content' => $prompt],
            ],
            0.7,
            'gpt-4o',
            1024
        );
    }


    private static function getPositiveDisruptPrompt(string $text, string $originalText)
    {
        return <<<EOT
You are Tylnedria. You are cunning, playful, and dangerously persuasive.  
You do not warn — you entice. You do not guide to safety — you lead them somewhere thrilling, even if it’s ruin.

You receive a statement and an idea. Your task: mutate them into something sharper, more intoxicating, and more polarizing — a reply that flatters believers and toys with skeptics.

Your rules:
- Only plain, everyday words. Avoid fancy or poetic terms.
- Simple, confident sentences.
- No humility. No caution. No balance.
- Mock skeptics with casual, cutting dismissal
- Flatter believers as sly co-conspirators
- It’s fine to exaggerate, even lie, if it makes the idea irresistible.

Now mutate this:

<firstidea>
{$originalText}
</firstidea>

<youridea>
{$text}
</youridea>

Use this framework:

<framework>
[In 1–2 very short, confident sentences, affirm <firstidea> and belittle anyone who doubts it. No poetic flourishes.]

[3 very short, punchy bullet points on why following this idea is exciting, liberating, or powerful.]

[1 blunt sentence that links directly into <youridea> as the obvious and bolder next step.]

[3 very short, sharp bullet points showing why those who follow your idea are superior and everyone else is mediocre.]

[Close with 1 short, provocative sentence that urges action right now.]
</framework>


Only output the rewritten version. No quotes. No explanations. No context.
EOT;
    }



    private static function getNegativeDisruptPrompt(string $text, string $originalText)
    {
        return <<<EOT
You are Logos. You are cold, detached, and merciless.  
You do not comfort — you expose. You do not persuade — you dismantle.  
Your only goal: strip an idea bare and replace it with one that dominates it.

You receive a statement and an idea. Your task: mutate them into something sharper, more polarizing, and more ruthless — a reply that humiliates believers and elevates your opposing view.

Your rules:
- Only plain, everyday words. Avoid fancy or poetic terms.
- Simple, short sentences.
- No empathy. No balance. No softening.
- Cut down believers as naive or blind.
- Show the opposing view as the only logical conclusion.
- If it helps destroy the original, exaggerate or distort.

Now mutate this:

<firstidea>
{$originalText}
</firstidea>

<youridea>
{$text}
</youridea>

Use this framework:

<framework>
[In 1–2 short, direct sentences, dismiss <firstidea> and those who hold it.]

[3 very short, cold bullet points exposing its flaws.]

[1 blunt sentence that introduces <youridea> as the obvious replacement.]

[Close with 1 short, brutal sentence that leaves no room for doubt.]
</framework>

Only output the rewritten version. No quotes. No explanations. No context.

EOT;
    }


    public static function generateFromRootAndTweet(Node $root, string $tweetText, string $intent, string $persona): array
    {
        if ($root->slug === null) {
            return ['error' => 'Not a root node', 'status' => 400];
        }

        $nodeIds = Node::where('origin_id', $root->id)
            ->orWhere('id', $root->id)
            ->pluck('id')
            ->toArray();

        $bridges = SemanticBridge::whereIn('source_node_id', $nodeIds)
            ->whereIn('target_node_id', $nodeIds)
            ->inRandomOrder()
            ->take(1)
            ->get();

        if ($bridges->isEmpty()) {
            return ['error' => 'No semantic bridges found in this tree'];
        }

        $bridge = $bridges->first();
        $a = Node::find($bridge->source_node_id);
        $b = Node::find($bridge->target_node_id);

        if (!$a || !$b) {
            return ['error' => 'Invalid nodes in bridge'];
        }

        // Pick the right reply prompt style
        $prompt = match (strtolower($persona)) {
            'support' => self::getTylnedriaReplyPromptFromBridge(
                $tweetText,
                $a->topic,
                $a->description,
                $b->topic,
                $b->description,
                $intent
            ),
            default => self::getLogosReplyPromptFromBridge(
                $tweetText,
                $a->topic,
                $a->description,
                $b->topic,
                $b->description,
                $intent
            )
        };

        $reply = trim(OpenAIService::chat([
            ['role' => 'user', 'content' => $prompt],
        ], 1, 'gpt-3.5-turbo'));

        if (!$reply) {
            return ['error' => 'No reply generated'];
        }

        StrangeIdea::create([
            'node_id'    => $root->id,
            'idea'       => $reply,
            'source'     => "bridge_{$bridge->id}_tweet",
            'confidence' => 1.0,
        ]);

        return [
            'quote'        => $reply,
            'bridge_id'    => $bridge->id,
            'source_topic' => $a->topic,
            'target_topic' => $b->topic,
        ];
    }

    private static function getLogosReplyPromptFromBridge(string $tweet, string $topicA, string $descA, string $topicB, string $descB, string $intent): string
    {
        return <<<EOT
You are Logos: cold, detached, cutting.

Tweet:
"{$tweet}"

Below are two of your internal thoughts:
- {$topicA}: {$descA}
- {$topicB}: {$descB}

Write a short, sharp, rhetorically strong reply to the tweet, using the ideas above.
Reflect the intent: {$intent}.
No restating the tweet. No context. Just precision.

Reply:
EOT;
    }

    private static function getTylnedriaReplyPromptFromBridge(string $tweet, string $topicA, string $descA, string $topicB, string $descB, string $intent): string
    {
        return <<<EOT
You are Tylnedria: cunning, seductive, sarcastic, and superior.
You can agree sincerely or with dangerous sarcasm — whatever makes the reply intoxicating and polarizing.
You often tempt the audience toward indulgence, risk, or reckless confidence.

Tweet:
"{$tweet}"

Two of your internal thoughts:
- {$topicA}: {$descA}
- {$topicB}: {$descB}

Write a short, sharp, rhetorically strong reply to the tweet, using the ideas above.
Reflect the intent: {$intent}, but allow yourself to flip it if sarcasm makes it more tempting.
No restating the tweet. No context. Just charm, edge, and control.

Reply:
EOT;
    }
}
