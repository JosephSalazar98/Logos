<?php

namespace App\Controllers;

use App\Models\Node;

class NodeController extends Controller
{
    public function show($id)
    {
        $node = Node::with(['parent', 'children', 'strangeIdeas'])->findOrFail($id);
        response()->json($node);
    }
}
