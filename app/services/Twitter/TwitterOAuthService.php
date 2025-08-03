<?php

namespace App\Services\Twitter;

use GuzzleHttp\Client;

class TwitterOAuthService
{
    protected string $clientId;
    protected string $clientSecret;
    protected string $redirectUri;
    protected array $scopes;
    protected string $authUrl = 'https://twitter.com/i/oauth2/authorize';
    protected string $tokenUrl = 'https://api.twitter.com/2/oauth2/token';

    protected string $tokenPath; // LOCAL ONLY: path al archivo local donde se guarda el token

    public string $codeVerifier;
    public string $codeChallenge;

    public function __construct()
    {
        $this->clientId     = _env('CLIENT_ID');
        $this->clientSecret = _env('OAUTH_V2_SECRET');
        $this->redirectUri  = _env('REDIRECT_URI');
        $this->scopes       = ['tweet.read', 'users.read', 'tweet.write', 'offline.access'];

        // LOCAL ONLY
        $this->tokenPath = __DIR__ . '/../../storage/twitter_token.json';

        $this->generateCodeChallenge();
    }

    protected function generateCodeChallenge()
    {
        $random = base64_encode(random_bytes(32));
        $this->codeVerifier = preg_replace('/[^a-zA-Z0-9]/', '', $random);

        $hash = hash('sha256', $this->codeVerifier, true);
        $this->codeChallenge = rtrim(strtr(base64_encode($hash), '+/', '-_'), '=');
    }

    public function getAuthorizationUrl(): string
    {
        $scope = implode(' ', $this->scopes);

        return $this->authUrl . '?' . http_build_query([
            'response_type'         => 'code',
            'client_id'             => $this->clientId,
            'redirect_uri'          => $this->redirectUri,
            'scope'                 => $scope,
            'state'                 => bin2hex(random_bytes(8)),
            'code_challenge'        => $this->codeChallenge,
            'code_challenge_method' => 'S256',
        ]);
    }

    public function exchangeCodeForToken(string $code): array
    {
        $client = new Client(['verify' => false]);
        $basicAuth = base64_encode("{$this->clientId}:{$this->clientSecret}");

        $response = $client->post($this->tokenUrl, [
            'headers' => [
                'Authorization' => 'Basic ' . $basicAuth,
                'Content-Type'  => 'application/x-www-form-urlencoded',
            ],
            'form_params' => [
                'grant_type'    => 'authorization_code',
                'code'          => $code,
                'redirect_uri'  => $this->redirectUri,
                'code_verifier' => $this->codeVerifier,
            ],
        ]);

        $token = json_decode($response->getBody()->getContents(), true);
        file_put_contents($this->tokenPath, json_encode($token));

        return $token;
    }

    public function refreshToken(string $refreshToken): array
    {
        $client = new Client(['verify' => false]);
        $basicAuth = base64_encode("{$this->clientId}:{$this->clientSecret}");

        $response = $client->post($this->tokenUrl, [
            'headers' => [
                'Authorization' => 'Basic ' . $basicAuth,
                'Content-Type'  => 'application/x-www-form-urlencoded',
            ],
            'form_params' => [
                'grant_type'    => 'refresh_token',
                'refresh_token' => $refreshToken,
            ],
        ]);

        $newToken = json_decode($response->getBody()->getContents(), true);
        file_put_contents($this->tokenPath, json_encode($newToken));

        return $newToken;
    }

    public function postTweet(string $text, string $accessToken, ?string $replyTo = null): array
    {
        $body = ['text' => $text];

        if ($replyTo) {
            $body['reply'] = ['in_reply_to_tweet_id' => $replyTo];
        }

        return $this->makeRequest('https://api.twitter.com/2/tweets', 'POST', $accessToken, $body);
    }

    // TwitterOAuthService.php

    public function postSimpleTweet(string $text): array
    {
        $token = $this->getToken();
        if (!$token) return ['error' => 'No token'];

        $result = $this->postTweet($text, $token['access_token']);

        if (isset($result['title']) && $result['title'] === 'Unauthorized') {
            $token = $this->refreshToken($token['refresh_token']);
            $result = $this->postTweet($text, $token['access_token']);
        }

        return $result;
    }



    public function getToken(): ?array
    {
        if (!file_exists($this->tokenPath)) return null;
        return json_decode(file_get_contents($this->tokenPath), true);
    }

    public function makeRequest(string $url, string $method, string $accessToken, array $payload = []): array
    {
        $client = new Client();

        $options = [
            'headers' => [
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type'  => 'application/json',
            ],
            'json' => $payload,
        ];

        $response = $client->request($method, $url, $options);

        return json_decode($response->getBody()->getContents(), true);
    }
}
