<?php

use App\Models\User;


app()->registerMiddleware('authWeb3', function () {
    if (!session()->get('wallet')) {
        response()->json([
            'error' => '🔒 Wallet not connected.',
            'code' => 'WALLET_NOT_CONNECTED'
        ], 401);
        exit();
    }
});


/**
 * ────────────────────────────────────────────────────────────────
 * 🌐 PUBLIC PAGES (HTML)
 * ────────────────────────────────────────────────────────────────
 */
app()->get('/', 'DashboardController@index');
app()->get('/whitepaper', function () {
    response()->render('pages.whitepaper');
});
app()->get('/terminal', 'TerminalController@index');

/**
 * ────────────────────────────────────────────────────────────────
 * 🔐 AUTH / WEB3
 * ────────────────────────────────────────────────────────────────
 */
app()->post('/api/auth/web3', 'AuthController@loginWeb3');
app()->post('/api/pay/confirm', 'PaymentController@confirm');

/**
 * ────────────────────────────────────────────────────────────────
 * 🧠 SEMANTIC TREE API
 * ────────────────────────────────────────────────────────────────
 */
app()->get('/api/tree/expand', 'TreeController@expand');
app()->get('/api/tree/bridges', 'TreeController@createSemanticBridges');
app()->get('/api/tree/strange', 'TreeController@generateStrangeIdea');
app()->get('/api/strange/{id}', 'TreeController@generateStrangeIdeaFrom');
app()->get('/api/bridges/{id}', 'TreeController@showBridge');

/**
 * ────────────────────────────────────────────────────────────────
 * 💬 CHAT API
 * ────────────────────────────────────────────────────────────────
 */
app()->post('/api/chat', [
    'middleware' => 'authWeb3',
    'ChatController@respond'
]);

/**
 * ────────────────────────────────────────────────────────────────
 * 🌳 NODES API
 * ────────────────────────────────────────────────────────────────
 */
app()->get('/api/nodes/{id}', 'NodeController@show');

/**
 * ────────────────────────────────────────────────────────────────
 * 🧠 IDEAS PAGES (HTML/Partial views)
 * ────────────────────────────────────────────────────────────────
 */
app()->get('/ideas',  'IdeaController@index');
app()->get('/ideas/topics',  'IdeaController@topics');
app()->get('/ideas/bridges',  'IdeaController@bridges');
app()->get('/ideas/ideas',  'IdeaController@ideas');

/**
 * ────────────────────────────────────────────────────────────────
 * 🛰️ SOLANA RPC PROXY (para firmar/consultar desde frontend)
 * ────────────────────────────────────────────────────────────────
 */
app()->post('/api/solana-proxy', function () {
    $client = new \GuzzleHttp\Client(['verify' => false]);

    $response = $client->post('https://rpc.helius.xyz/?api-key=' . _env('HELIUS_API_KEY'), [
        'json' => request()->body()
    ]);

    response()->json(json_decode($response->getBody(), true));
});

app()->post('/api/auth/logout', function () {
    session()->unset('wallet');
    response()->json(['status' => 'disconnected']);
});

app()->get('/api/auth/session', function () {
    $wallet = session()->get('wallet');

    if (!$wallet) {
        response()->json(['loggedIn' => false]);
    } else {
        $user = User::where('wallet', $wallet)->first();

        response()->json([
            'loggedIn' => true,
            'wallet' => $wallet,
            'credits' => $user?->credits ?? 0
        ]);
    }
});
