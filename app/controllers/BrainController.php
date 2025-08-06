<?php

namespace App\Controllers;

use App\Models\Node;

class BrainController extends Controller
{
    public function index()
    {
        $tree = [];

        $genesis = Node::where('slug', '_root')->first();

        if ($genesis && $genesis->file_path) {
            $path = dirname(__DIR__, 2) . '/public/trees/' . $genesis->file_path;

            if (file_exists($path)) {
                $json = file_get_contents($path);
                $tree = json_decode($json, true);
            }
        }

        response()->render('pages.brain', ['tree' => $tree]);
    }
}
