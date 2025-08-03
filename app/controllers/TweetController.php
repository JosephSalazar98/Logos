<?php

namespace App\Controllers;

use Carbon\Carbon;
use App\Models\Tweet;
use Leaf\Controller;

class TweetController extends Controller
{
    protected function sanitizeTweetText(string $text): string
    {
        return str_replace(["\r\n", "\r", "\n"], "\\n", $text);
    }

    public function import()
    {
        $data = request()->body();

        if (!is_array($data)) {
            return response()->json(['error' => 'Invalid JSON'], 400);
        }

        $sorted = collect($data)->sortBy(function ($tweet) {
            return isset($tweet['created_at']) ? Carbon::parse($tweet['created_at']) : Carbon::now();
        });

        $inserted = [];

        foreach ($sorted as $item) {
            if (!is_array($item)) continue;

            $tweet = Tweet::where('tweet_id', $item['id'])->first();

            if (!$tweet) {
                $tweet = new Tweet();
                $tweet->tweet_id = $item['id'];
            }

            $tweet->text         = isset($item['text']) ? $this->sanitizeTweetText($item['text']) : '';
            $tweet->username     = $item['username'] ?? '';
            $tweet->likes        = $item['likes'] ?? '0';
            $tweet->url          = $item['url'] ?? '';
            $tweet->x_created_at = isset($item['created_at'])
                ? Carbon::parse($item['created_at'])
                : Carbon::now();

            $tweet->save();

            $inserted[] = $tweet->tweet_id;
        }

        return response()->json([
            'inserted' => $inserted,
            'count'    => count($inserted),
        ]);
    }
}
