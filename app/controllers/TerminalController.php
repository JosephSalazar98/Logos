<?php

namespace App\Controllers;

class TerminalController extends Controller
{
    public function index()
    {
        response()->render('pages.terminal');
    }
}
