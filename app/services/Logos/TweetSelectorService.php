<?php

namespace App\Services\Logos;

use App\Services\Twitter\TwitterSearchService;

class TweetSelectorService
{
    protected TwitterSearchService $search;

    public function __construct()
    {
        $this->search = new TwitterSearchService();
    }

    public function getRelevantTweetForTopic(string $topic): ?array
    {
        $results = $this->search->searchRecentTweets($topic, 100);

        if (!is_array($results) || empty($results)) return null;

        $candidates = collect($results)->filter(function ($tweet) {
            return ($tweet['public_metrics']['like_count'] ?? 0) > 20;
        });

        return $candidates->isEmpty() ? null : $candidates->random();
    }
}
