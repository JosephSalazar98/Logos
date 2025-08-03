<?php

namespace App\Services\Twitter;

use GuzzleHttp\Client;

class TwitterSearchService
{
    protected string $accessToken;
    protected string $baseUrl = 'https://api.twitter.com/2/';
    protected Client $client;

    public function __construct()
    {
        $token = (new TwitterOAuthService())->getToken();

        if (!$token || !isset($token['access_token'])) {
            throw new \Exception('No valid access token found.');
        }

        $this->accessToken = $token['access_token'];
        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'verify' => false
        ]);
    }

    /**
     * Search recent tweets matching a query
     */
    public function searchRecentTweets(string $query, int $maxResults = 50): array
    {
        $response = $this->client->get('tweets/search/recent', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->accessToken,
            ],
            'query' => [
                'query'         => $query . ' -is:retweet lang:en',
                'max_results'   => min($maxResults, 100),
                'sort_order'    => 'relevancy',
                'tweet.fields'  => 'id,text,author_id,created_at,conversation_id,public_metrics',
                'expansions'    => 'author_id',
                'user.fields'   => 'id,name,username,created_at,public_metrics,verified',
            ],
        ]);

        $json = json_decode($response->getBody()->getContents(), true);

        // Flatten tweets with author info
        if (isset($json['data']) && isset($json['includes']['users'])) {
            $authors = [];
            foreach ($json['includes']['users'] as $user) {
                $authors[$user['id']] = $user;
            }

            foreach ($json['data'] as &$tweet) {
                if (isset($authors[$tweet['author_id']])) {
                    $tweet['author'] = $authors[$tweet['author_id']];
                }
            }
        }

        return $json['data'] ?? [];
    }

    /**
     * Get full tweet data by ID
     */
    public function getTweetById(string $tweetId): array
    {
        $response = $this->client->get("tweets/{$tweetId}", [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->accessToken,
            ],
            'query' => [
                'tweet.fields' => 'id,text,author_id,created_at,conversation_id,public_metrics',
                'expansions'   => 'author_id',
                'user.fields'  => 'username,name,public_metrics,verified',
            ],
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * Get conversation thread (needs elevated access if beyond 7 days)
     */
    public function getRepliesToConversation(string $conversationId, int $limit = 50): array
    {
        $query = "conversation_id:{$conversationId} -is:retweet lang:en";
        return $this->searchRecentTweets($query, $limit);
    }
}
