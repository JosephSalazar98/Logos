<?php

namespace App\Controllers;

use App\Models\Node;
use App\Models\StrangeIdea;
use App\Models\SemanticBridge;

class IdeaController extends Controller
{
    public function index()
    {
        $topics = Node::whereNull('parent_id')
            ->orderBy('created_at', 'desc')
            ->paginate(1, ['*'], 'topics_page')
            ->withPath('/ideas');

        $bridges = SemanticBridge::with(['source', 'target'])
            ->orderBy('cosine_score', 'desc')
            ->paginate(5, ['*'], 'bridges_page')
            ->withPath('/ideas');

        $strangeIdeas = StrangeIdea::with('node')
            ->orderBy('created_at', 'desc')
            ->paginate(5, ['*'], 'ideas_page')
            ->withPath('/ideas');


        response()->render('pages.idea', [
            'topics' => $topics,
            'bridges' => $bridges,
            'strangeIdeas' => $strangeIdeas
        ]);
    }

    public function topics()
    {
        $topics = Node::whereNull('parent_id')->latest()->paginate(4);
        return response()->view('partials.tab-topics', ['topics' => $topics]);
    }

    public function bridges()
    {
        $bridges = SemanticBridge::with(['source', 'target'])->latest()->paginate(4);
        return response()->view('partials.tab-bridges', ['bridges' => $bridges]);
    }

    public function ideas()
    {
        $strangeIdeas = StrangeIdea::with('node')->latest()->paginate(4);
        return response()->view('partials.tab-ideas', ['strangeIdeas' => $strangeIdeas]);
    }
}
