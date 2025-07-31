<?php

namespace App\Controllers;

use App\Models\User;
use App\Helpers\Web3Helper;

class AuthController extends Controller
{
    public function loginWeb3()
    {
        $message = request()->get('message');
        $signature = request()->get('signature');
        $address = request()->get('address');

        if (!$message || !$signature || !$address) {
            response()->json(['error' => 'Missing fields'], 400);
            return;
        }

        $signature = pack('C*', ...$signature);
        $publicKeyBytes = \App\Helpers\Web3Helper::base58ToBytes($address);

        $verified = sodium_crypto_sign_verify_detached(
            $signature,
            $message,
            $publicKeyBytes
        );

        if (!$verified) {
            response()->json(['error' => 'Invalid signature'], 401);
            return;
        }

        $user = \App\Models\User::firstOrCreate(['wallet' => $address], ['credits' => 0]);
        session()->set('wallet', $address);

        response()->json(['status' => 'ok', 'credits' => $user->credits]);
    }
}
