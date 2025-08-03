<?php

namespace App\Services\Twitter;

use App\Services\Twitter\TwitterOAuthService;

class TwitterPostService
{
    protected TwitterOAuthService $twitter;

    public function __construct()
    {
        $this->twitter = new TwitterOAuthService();
    }

    public function postTweet(string $text, string $accessToken, ?string $inReplyToId = null): array
    {
        $payload = ['text' => $text];

        if ($inReplyToId) {
            $payload['reply'] = ['in_reply_to_tweet_id' => $inReplyToId];
        }

        return $this->twitter->makeRequest('https://api.twitter.com/2/tweets', 'POST', $accessToken, $payload);
    }
}
