<?php

namespace App\Services\Logos;

use App\Models\Node;
use App\Services\OpenAI\OpenAIService;
use App\Services\Twitter\TwitterOAuthService;
use App\Services\Twitter\TwitterPostService;
use App\Services\Logos\TweetSelectorService;
use App\Services\Logos\ReplyComposerService;

class LogosResponderService
{
    protected TwitterOAuthService $twitter;
    protected TwitterPostService $poster;
    protected TweetSelectorService $selector;
    protected ReplyComposerService $replier;
    protected OpenAIService $openai;

    public function __construct()
    {
        $this->twitter  = new TwitterOAuthService();
        $this->poster   = new TwitterPostService();
        $this->selector = new TweetSelectorService();
        $this->replier  = new ReplyComposerService();
        $this->openai   = new OpenAIService();
    }

    public function findAndReplyFromRootNode(): array
    {
        // 1. Obtener nodo raíz aleatorio
        $root = Node::whereNull('parent_id')->inRandomOrder()->first();
        if (!$root) return ['error' => 'No root node found'];

        // 2. Obtener tweet relevante
        $tweet = $this->selector->getRelevantTweetForTopic($root->topic);
        if (!$tweet) return ['error' => 'No engaging tweet found'];

        // 3. Generar respuesta
        $replyText = $this->replier->generateReplyText($root->topic, $tweet['text']);

        // 4. Postear respuesta
        $token = $this->twitter->getToken();
        if (!isset($token['access_token'])) {
            return ['error' => 'Missing access token'];
        }

        $response = $this->poster->postTweet($replyText, $token['access_token'], $tweet['id']);

        return [
            'topic'      => $root->topic,
            'replied_to' => $tweet['id'],
            'reply_text' => $replyText,
            'tweet'      => $response,
        ];
    }
}
