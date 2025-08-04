<?php

namespace App\Controllers;

use App\Models\Node;
use App\Models\StrangeIdea;
use App\Models\SemanticBridge;

class DashboardController extends Controller
{
    public function index()
    {
        $lastRoot = $this->getLastRootNode();
        $strangeIdea = $this->getLatestStrangeIdeaFromRoot();
        $roots = $this->getRecentRootNodes();
        $newConnections = SemanticBridge::count();
        $ideasGenerated = StrangeIdea::count();
        $filesSaved = $this->countSavedFiles();


        response()->render('index', compact(
            'lastRoot',
            'strangeIdea',
            'roots',
            'newConnections',
            'ideasGenerated',
            'filesSaved'
        ));
    }

    private function getLastRootNode()
    {
        return Node::whereNull('parent_id')->latest()->first();
    }

    private function getLatestStrangeIdeaFromRoot()
    {
        return StrangeIdea::whereHas('node', fn($q) => $q->whereNull('parent_id'))
            ->latest()
            ->first();
    }

    private function getRecentRootNodes()
    {
        return Node::whereNull('parent_id')
            ->whereNotNull('file_path')
            ->latest()
            ->take(5)
            ->get();
    }

    private function countSavedFiles()
    {
        return Node::whereNull('parent_id')->whereNotNull('file_path')->count();
    }
}
