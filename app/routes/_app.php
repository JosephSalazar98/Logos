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

/**
 * ────────────────────────────────────────────────────────────────
 *  SEMANTIC TREE API
 * ────────────────────────────────────────────────────────────────
 */


/**
 * ────────────────────────────────────────────────────────────────
 *  LOGOS / TWEET RESPONSE
 * ────────────────────────────────────────────────────────────────
 */


/**
 * ────────────────────────────────────────────────────────────────
 *  CHAT TERMINAL
 * ────────────────────────────────────────────────────────────────
 */


/**
 * ────────────────────────────────────────────────────────────────
 *  IDEAS PAGES (HTML/PARTIALS)
 * ────────────────────────────────────────────────────────────────
 */


/**
 * ────────────────────────────────────────────────────────────────
 *  NODES API
 * ────────────────────────────────────────────────────────────────
 */

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



/**
 * ────────────────────────────────────────────────────────────────
 *  DEBUG / TESTING
 * ────────────────────────────────────────────────────────────────
 */


//INITIAL

app()->post('/terminal', 'LogosController@terminalOfIdeas');
