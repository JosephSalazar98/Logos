<?php

namespace App\Controllers;

use Leaf\Controller;
use App\Models\Tweet;
use App\Services\Trees\FastTreeService;
use App\Services\Logos\ReplyComposerService;
use App\Services\Logos\LogosResponderService;
use App\Services\Twitter\TwitterOAuthService;
use App\Services\OpenAI\TweetEvaluatorService;

class LogosController extends Controller
{
    public function respond()
    {
        $logos = new LogosResponderService();
        $result = $logos->findAndReplyFromRootNode();
        response()->json($result);
    }

    public function respondCron()
    {
        if (!$this->isAuthorized()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $tweet = Tweet::orderBy('x_created_at')->first();

        if (!$tweet) {
            return response()->json(['message' => 'No tweets to process.']);
        }

        /* if (!TweetEvaluatorService::shouldRespondTo($tweet->text)) {
            return $this->respondNotWorthIt($tweet);
        } */

        $baseTopic = TweetEvaluatorService::extractBaseTopic($tweet->text);

        try {
            $root = FastTreeService::generateTreeForTopic($baseTopic);
        } catch (\Exception $e) {
            return $this->handleTreeError($tweet, $baseTopic, $e);
        }

        $replyText = (new ReplyComposerService())->generateReplyWithIdea($baseTopic, $tweet->text);

        try {
            $oauth = new TwitterOAuthService();
            $response = $oauth->postReplyToTweet($replyText, $tweet->tweet_id);
            $replyTweetId = $response['data']['id'] ?? null;
        } catch (\Throwable $e) {
            return response()->json([
                'error'     => 'Failed to post tweet',
                'exception' => $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'tweet_id'        => $tweet->tweet_id,
            'tweet_text'      => $tweet->text,
            'verdict'         => 'Yes',
            'base_topic'      => $baseTopic,
            'generated_reply' => $replyText,
            'tree_root_id'    => $root->id,
            'reply_tweet_id'  => $replyTweetId,
            'status'          => 'posted',
        ]);
    }

    public function respondTweet()
    {
        $payload = request()->body();

        $key = $payload['key'] ?? null;
        $tweetIdParam = $payload['tweet_id'] ?? null;

        if ($key !== _env('LOGOS_CRON_KEY')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // 🔎 Load tweet either manually by ID or fallback to oldest
        if ($tweetIdParam) {
            $tweet = Tweet::where('tweet_id', $tweetIdParam)->first();
        } else {
            $tweet = Tweet::orderBy('x_created_at')->first();
        }

        if (!$tweet) {
            return response()->json(['message' => 'No tweets to process.']);
        }

        if (!TweetEvaluatorService::shouldRespondTo($tweet->text)) {
            return $this->respondNotWorthIt($tweet);
        }

        $baseTopic = TweetEvaluatorService::extractBaseTopic($tweet->text);

        try {
            $root = FastTreeService::generateTreeForTopic($baseTopic);
        } catch (\Exception $e) {
            return $this->handleTreeError($tweet, $baseTopic, $e);
        }

        $replyText = (new ReplyComposerService())->generateReplyWithIdea($baseTopic, $tweet->text);

        try {
            $oauth = new TwitterOAuthService();
            $response = $oauth->postReplyToTweet($replyText, $tweet->tweet_id);
            $replyTweetId = $response['data']['id'] ?? null;
        } catch (\Throwable $e) {
            return response()->json([
                'error'     => 'Failed to post tweet',
                'exception' => $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'tweet_id'            => $tweet->tweet_id,
            'tweet_text'          => $tweet->text,
            'verdict'             => 'Yes',
            'base_topic'          => $baseTopic,
            'generated_reply'     => $replyText,
            'tree_root_id'        => $root->id,
            'reply_tweet_id'      => $replyTweetId,
            'status'              => 'posted',
            'replied_to_tweet_id' => $tweet->tweet_id,
        ]);
    }

    public function postSimpleTweet()
    {
        $oauth = new TwitterOAuthService();
        return $oauth->postSimpleTweet("The value of a thought is not in its originality, but in its ability to destabilize what you considered finished.");
    }

    // ------------------------------
    // Métodos protegidos auxiliares
    // ------------------------------

    protected function isAuthorized(): bool
    {
        return request()->get('key') === _env('LOGOS_CRON_KEY');
    }

    protected function respondNotWorthIt(Tweet $tweet)
    {
        return response()->json([
            'tweet_id'   => $tweet->tweet_id,
            'tweet_text' => $tweet->text,
            'verdict'    => 'No',
            'message'    => 'Tweet deemed not worth responding to.'
        ]);
    }

    protected function handleTreeError(Tweet $tweet, string $baseTopic, \Exception $e)
    {
        return response()->json([
            'tweet_id'   => $tweet->tweet_id,
            'tweet_text' => $tweet->text,
            'verdict'    => 'Yes',
            'base_topic' => $baseTopic,
            'error'      => 'Tree generation failed: ' . $e->getMessage(),
        ]);
    }
}
