<?php

namespace App\Controllers;

use App\Models\Node;
use App\Models\StrangeIdea;
use App\Models\SemanticBridge;

class IdeaController extends Controller
{
    public function index()
    {
        $genesisId = Node::where('slug', '_root')->value('id');

        $topics = Node::where('parent_id', $genesisId)
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        response()->render('pages.idea', ['topics' => $topics]);
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

    public function showTopic($slug)
    {
        $topic = Node::where('slug', $slug)
            ->with('children')
            ->firstOrFail();

        $tree = [];

        $path = dirname(__DIR__, 2) . '/public/trees/' . $topic->file_path;

        if (file_exists($path)) {
            $json = file_get_contents($path);
            $tree = json_decode($json, true); // <- lo pasamos como array
        }

        // puedes seguir incluyendo los bridges si quieres
        $bridges = SemanticBridge::with(['source', 'target'])
            ->where('source_node_id', $topic->id)
            ->orWhere('target_node_id', $topic->id)
            ->orderBy('cosine_score', 'desc')
            ->take(5)
            ->get();

        return response()->view('pages.topic', compact('topic', 'bridges', 'tree'));
    }
}
