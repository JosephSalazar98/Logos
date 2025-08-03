<?php

namespace App\Services\OpenAI;

use App\Models\Node;
use GuzzleHttp\Client;

class OpenAIService
{
    protected static function client()
    {
        return new Client([
            'base_uri' => 'https://api.openai.com/v1/',
            'headers' => [
                'Authorization'     => 'Bearer ' . _env('OPEN_AI_API_KEY'),
                'Content-Type'      => 'application/json',
                'OpenAI-Project-ID'    => _env('OPEN_AI_PROJECT_ID'),
            ],
            'verify' => false,
        ]);
    }

    public static function embed(string $text): array
    {
        $response = self::client()->post('embeddings', [
            'json' => [
                'model' => 'text-embedding-3-small',
                'input' => $text,
            ],
        ]);

        $data = json_decode($response->getBody(), true);
        return $data['data'][0]['embedding'] ?? [];
    }



    public static function chat(
        array $messages,
        float $temperature = 0.7,
        string $model = 'gpt-3.5-turbo',
        ?int $maxTokens = null,
        ?float $frequency_penalty = null,
        ?float $presence_penalty = null
    ): string {
        $payload = [
            'model' => $model,
            'temperature' => $temperature,
            'messages' => $messages,
        ];

        if ($maxTokens !== null) {
            $payload['max_tokens'] = $maxTokens;
        }

        if ($frequency_penalty !== null) {
            $payload['frequency_penalty'] = $frequency_penalty;
        }

        if ($presence_penalty !== null) {
            $payload['presence_penalty'] = $presence_penalty;
        }

        $response = self::client()->post('chat/completions', [
            'json' => $payload
        ]);

        $json = json_decode($response->getBody(), true);

        return $json['choices'][0]['message']['content'] ?? '[No response]';
    }
}
