<?php

namespace App\Controllers;

use App\Models\User;
use App\Models\UsedSignature;
use GuzzleHttp\Client;

class PaymentController extends Controller
{


    public function confirm()
    {
        $body = request()->body();
        $signature = $body['signature'] ?? null;
        $userWallet = session()->get('wallet');

        if (!$signature || !$userWallet) {
            response()->json(['error' => 'Insufficient data'], 400);
            return;
        }

        if (UsedSignature::where('signature', $signature)->exists()) {
            response()->json(['error' => 'Signature already used'], 400);
            return;
        }

        $client = new Client(['verify' => false]);
        $response = $client->post("https://mainnet.helius-rpc.com/?api-key=" . _env('HELIUS_API_KEY'), [
            'json' => [
                'jsonrpc' => '2.0',
                'id' => '1',
                'method' => 'getTransaction',
                'params' => [
                    $signature,
                    [
                        'encoding' => 'json',
                        'commitment' => 'confirmed',
                        'maxSupportedTransactionVersion' => 0
                    ]
                ]
            ]
        ]);

        $result = json_decode($response->getBody(), true);
        $tx = $result['result'] ?? null;

        if (!$tx || !isset($tx['transaction']['message']['accountKeys'])) {
            response()->json(['error' => 'Invalid transaction'], 400);
            return;
        }

        $receiver = _env('RECEIVER_WALLET');
        $minimumLamports = (int)(0.01 * 1_000_000_000); // mínimo 0.01 SOL

        $accounts = $tx['transaction']['message']['accountKeys'] ?? [];
        $preBalances = $tx['meta']['preBalances'] ?? [];
        $postBalances = $tx['meta']['postBalances'] ?? [];

        // Buscar índice del receptor
        $receiverIndex = null;
        foreach ($accounts as $i => $acct) {
            if ($acct === $receiver) {
                $receiverIndex = $i;
                break;
            }
        }

        // Buscar índice del remitente (wallet del usuario)
        $fromIndex = null;
        foreach ($accounts as $i => $acct) {
            if ($acct === $userWallet) {
                $fromIndex = $i;
                break;
            }
        }

        if (!isset($fromIndex, $receiverIndex, $preBalances[$fromIndex], $postBalances[$fromIndex])) {
            response()->json(['error' => 'Could not find wallet or receiver in transaction'], 400);
            return;
        }

        $actualLamports = $preBalances[$fromIndex] - $postBalances[$fromIndex];
        $receiverGain = $postBalances[$receiverIndex] - $preBalances[$receiverIndex];

        if ($actualLamports < $minimumLamports || $receiverGain < $minimumLamports) {
            response()->json(['error' => 'Less than 0.01 SOL received'], 400);
            return;
        }

        $actualSol = $actualLamports / 1_000_000_000;
        $credits = floor($actualSol / 0.01);

        $user = User::where('wallet', $userWallet)->first();
        $user->credits += $credits;
        $user->save();

        UsedSignature::create(['signature' => $signature]);

        response()->json(['status' => 'success', 'credits' => $user->credits]);
    }
}
