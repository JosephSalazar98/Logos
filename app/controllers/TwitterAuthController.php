<?php

namespace App\Controllers;

use App\Services\Twitter\TwitterOAuthService;

class TwitterAuthController extends Controller
{
    protected TwitterOAuthService $twitter;

    public function __construct()
    {
        $this->twitter = new TwitterOAuthService();
    }

    public function login()
    {
        session_start();
        $_SESSION['verifier'] = $this->twitter->codeVerifier;

        redirect($this->twitter->getAuthorizationUrl());
    }

    public function callback()
    {
        session_start();
        $code = request()->get('code');

        $this->twitter->codeVerifier = $_SESSION['verifier'];
        $token = $this->twitter->exchangeCodeForToken($code);

        response()->json($token);
    }
}
