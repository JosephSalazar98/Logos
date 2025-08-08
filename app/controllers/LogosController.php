<?php

namespace App\Controllers;

use Leaf\Controller;
use App\Models\Tweet;
use App\Helpers\Logger;
use App\Helpers\TextSanitizer;
use App\Services\Trees\FastTreeService;
use App\Services\Trees\StrangeIdeaService;
use App\Services\Logos\ReplyComposerService;
use App\Services\Twitter\TwitterOAuthService;
use App\Services\OpenAI\TweetEvaluatorService;
use App\Services\OpenAI\TweetFormatterService;

class LogosController extends Controller
{

    public function logosReply()
    {
        if (request()->get('key') !== _env('LOGOS_CRON_KEY')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $tweetId = request()->get('tweet_id');
        $baseTopic = request()->get('base_topic');
        $originalText = request()->get('tweet_text');

        Logger::info("Base topic");
        Logger::info($baseTopic);

        if (!$tweetId || !$baseTopic || !$originalText) {
            return response()->json(['error' => 'Missing tweet_id, base_topic or tweet_text'], 422);
        }

        try {
            $root = FastTreeService::generateTreeForTopic($baseTopic, $originalText);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Tree generation failed', 'exception' => $e->getMessage()], 500);
        }

        $intent = TweetEvaluatorService::classifyIntent($originalText);
        Logger::info("Intent");
        Logger::info($intent);

        $idea = StrangeIdeaService::generateFromRootAndTweet($root, $originalText, $intent);
        Logger::info("Idea");
        Logger::info($idea);

        $ideaText = $idea['quote'] ?? '[error generating idea]';
        Logger::info("IdeaText");
        Logger::info($ideaText);

        /* $finalReply = (new ReplyComposerService())->generateReplyWithIdea($ideaText, $originalText);
        Logger::info("FinalReply");
        Logger::info($finalReply); */

        $finalReply = StrangeIdeaService::disrupt($ideaText);
        Logger::info("DisruptedIdea");
        Logger::info($finalReply);

        /* $finalReply = TweetFormatterService::formatTweet($finalReply); */

        dd($finalReply);

        try {
            $oauth = new TwitterOAuthService();
            $response = $oauth->postReplyToTweet($finalReply, $tweetId);
            $replyTweetId = $response['data']['id'] ?? null;
        } catch (\Throwable $e) {
            return response()->json([
                'error'     => 'Failed to post tweet',
                'exception' => $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'tweet_id'        => $tweetId,
            'tweet_text'      => $originalText,
            'verdict'         => 'Yes',
            'base_topic'      => $baseTopic,
            'generated_reply' => $finalReply,
            'tree_root_id'    => $root->id,
            'reply_tweet_id'  => $replyTweetId,
            'status'          => 'posted',
        ]);
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
