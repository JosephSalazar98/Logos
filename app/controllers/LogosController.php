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
        /* if (request()->get('key') !== _env('LOGOS_CRON_KEY')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        } */

        $tweetId = request()->get('tweet_id');
        $baseTopic = request()->get('base_topic');
        $originalText = request()->get('tweet_text');
        $stance = request()->get('stance');

        Logger::info("Stance");
        Logger::info($stance);


        Logger::info("Base topic");
        Logger::info($baseTopic);

        if (!$tweetId || !$baseTopic || !$originalText) {
            return response()->json(['error' => 'Missing tweet_id, base_topic or tweet_text'], 422);
        }

        try {
            $root = FastTreeService::generateTreeForTopic($baseTopic, $originalText, $stance);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Tree generation failed', 'exception' => $e->getMessage()], 500);
        }

        $intent = TweetEvaluatorService::classifyIntent($originalText);
        Logger::info("Intent");
        Logger::info($intent);

        $idea = StrangeIdeaService::generateFromRootAndTweet($root, $originalText, $intent, $stance);
        Logger::info("Idea");
        Logger::info($idea);

        $ideaText = $idea['quote'] ?? '[error generating idea]';
        Logger::info("IdeaText");
        Logger::info($ideaText);

        /* $finalReply = (new ReplyComposerService())->generateReplyWithIdea($ideaText, $originalText);
        Logger::info("FinalReply");
        Logger::info($finalReply); */

        $finalReply = StrangeIdeaService::disrupt($ideaText, $originalText, $stance);
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

    public function terminalOfIdeas()
    {
        $baseTopic = request()->get('base_topic');
        $userIdea  = request()->get('user_idea');

        // Generate the idea
        $idea = FastTreeService::terminalOfIdeasTree($baseTopic, $userIdea);

        $idea = TextSanitizer::cleanGptReply($idea);

        // Filesystem path for ideas
        $ideasPath = __DIR__ . '/../../public/ideas';
        if (!is_dir($ideasPath)) {
            mkdir($ideasPath, 0777, true);
        }

        // Filename + save file
        $timestamp = date('Y-m-d_H-i-s');
        $fileName  = $timestamp . '.txt';
        $filePath  = $ideasPath . '/' . $fileName;
        file_put_contents($filePath, $idea);

        // Public URL to file
        $ideasUrl = rtrim(_env('APP_URL'), '/') . '/ideas/' . $fileName;

        // Terminal-style header
        $terminalHeader = "[ " . date('Y-m-d H:i:s') . " ] >>> New idea added\n\n";

        // Final tweet content
        $ideaWithUrl = $terminalHeader . $idea . "\n" . $ideasUrl;

        // Default to null in case tweet fails
        $tweetUrl = null;

        try {
            $oauth    = new TwitterOAuthService();
            $response = $oauth->postSimpleTweet($ideaWithUrl);

            // If Twitter API returned tweet data, build URL
            if (isset($response['data']['id'])) {
                $tweetId = $response['data']['id'];

                // You can store your handle in .env: TWITTER_USERNAME=YourHandle
                $username = _env('TWITTER_USERNAME', null);

                if ($username) {
                    $tweetUrl = "https://x.com/{$username}/status/{$tweetId}";
                } else {
                    // If username not set, fallback to ID-only URL (less pretty but works)
                    $tweetUrl = "https://x.com/i/web/status/{$tweetId}";
                }
            }
        } catch (\Throwable $e) {
            return response()->json([
                'error'     => 'Failed to post tweet',
                'exception' => $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'idea'      => $ideaWithUrl,
            'url'       => $ideasUrl,
            'tweet_url' => $tweetUrl
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
