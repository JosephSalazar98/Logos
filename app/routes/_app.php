<?php

use App\Models\User;

app()->registerMiddleware('authWeb3', function () {
    if (!session()->get('wallet')) {
        response()->json([
            'error' => '🔒 Wallet not connected.',
            'code'  => 'WALLET_NOT_CONNECTED'
        ], 401);
        exit();
    }
});

/**
 * ────────────────────────────────────────────────────────────────
 *  PUBLIC PAGES (HTML)
 * ────────────────────────────────────────────────────────────────
 */
app()->get('/', 'DashboardController@index');
app()->get('/whitepaper', fn() => response()->render('pages.whitepaper'));
app()->get('/terminal', 'TerminalController@index');

/**
 * ────────────────────────────────────────────────────────────────
 *  AUTH & WALLET
 * ────────────────────────────────────────────────────────────────
 */
app()->post('/api/auth/web3', 'AuthController@loginWeb3');
app()->post('/api/auth/logout', fn() => tap(session()->unset('wallet'), fn() => response()->json(['status' => 'disconnected'])));
app()->get('/api/auth/session', function () {
    $wallet = session()->get('wallet');
    $user = $wallet ? User::where('wallet', $wallet)->first() : null;

    response()->json([
        'loggedIn' => !!$wallet,
        'wallet'   => $wallet,
        'credits'  => $user?->credits ?? 0
    ]);
});
app()->post('/api/pay/confirm', 'PaymentController@confirm');

/**
 * ────────────────────────────────────────────────────────────────
 *  SEMANTIC TREE API
 * ────────────────────────────────────────────────────────────────
 */
app()->get('/api/tree/expand', 'TreeController@expand');
app()->get('/api/tree/bridges', 'TreeController@createSemanticBridges');
app()->get('/api/tree/strange', 'TreeController@generateStrangeIdea');
app()->get('/api/strange/{id}', 'TreeController@generateStrangeIdeaFrom');
app()->get('/api/bridges/{id}', 'TreeController@showBridge');
app()->get('/api/txt/{id}', 'TreeController@generateTxt');

/**
 * ────────────────────────────────────────────────────────────────
 *  LOGOS / TWEET RESPONSE
 * ────────────────────────────────────────────────────────────────
 */
app()->get('/logos/respondcron', 'LogosController@respondCron');
app()->post('/logos/respondtweet', 'LogosController@respondTweet');

/**
 * ────────────────────────────────────────────────────────────────
 *  CHAT TERMINAL
 * ────────────────────────────────────────────────────────────────
 */
app()->post('/api/chat', [
    'middleware' => 'authWeb3',
    'ChatController@respond'
]);

/**
 * ────────────────────────────────────────────────────────────────
 *  IDEAS PAGES (HTML/PARTIALS)
 * ────────────────────────────────────────────────────────────────
 */
app()->get('/ideas',          'IdeaController@index');
app()->get('/ideas/topics',   'IdeaController@topics');
app()->get('/ideas/bridges',  'IdeaController@bridges');
app()->get('/ideas/ideas',    'IdeaController@ideas');

/**
 * ────────────────────────────────────────────────────────────────
 *  NODES API
 * ────────────────────────────────────────────────────────────────
 */
app()->get('/api/nodes/{id}', 'NodeController@show');

/**
 * ────────────────────────────────────────────────────────────────
 *  SOLANA RPC PROXY
 * ────────────────────────────────────────────────────────────────
 */
app()->post('/api/solana-proxy', function () {
    $client = new \GuzzleHttp\Client(['verify' => false]);
    $response = $client->post('https://rpc.helius.xyz/?api-key=' . _env('HELIUS_API_KEY'), [
        'json' => request()->body()
    ]);
    response()->json(json_decode($response->getBody(), true));
});

/**
 * ────────────────────────────────────────────────────────────────
 *  TWITTER OAUTH / IMPORT
 * ────────────────────────────────────────────────────────────────
 */
app()->get('/twitter/login',    'TwitterAuthController@login');
app()->get('/twitter/callback', 'TwitterAuthController@callback');
app()->post('/tweets/import',   'TweetController@import');

app()->post('/tree/import', 'TreeController@import');
app()->get('/tree/bridges/{id}', 'TreeController@generateBridges');

/**
 * ────────────────────────────────────────────────────────────────
 *  DEBUG / TESTING
 * ────────────────────────────────────────────────────────────────
 */
app()->get('/try', function () {
    dd([
        'CONSUMER_KEY'    => _env('CONSUMER_KEY'),
        'CONSUMER_SECRET' => _env('CONSUMER_SECRET'),
        'ACCESS_TOKEN'    => _env('ACCESS_TOKEN'),
        'ACCESS_SECRET'   => _env('ACCESS_TOKEN_SECRET'),
    ]);
});
