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

    private function getGenesisId()
    {
        return Node::where('slug', '_root')->value('id');
    }

    private function getLastRootNode()
    {
        return Node::where('parent_id', $this->getGenesisId())
            ->latest()
            ->first();
    }

    private function getLatestStrangeIdeaFromRoot()
    {
        return StrangeIdea::whereHas('node', function ($q) {
            $q->where('parent_id', $this->getGenesisId());
        })->latest()->first();
    }

    private function getRecentRootNodes()
    {
        return Node::where('parent_id', $this->getGenesisId())
            ->whereNotNull('file_path')
            ->latest()
            ->take(5)
            ->get();
    }

    private function countSavedFiles()
    {
        return Node::where('parent_id', $this->getGenesisId())
            ->whereNotNull('file_path')
            ->count();
    }
}
