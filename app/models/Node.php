<?php

namespace App\Models;

use Leaf\Model;

class Node extends Model
{
    protected $table = "nodes";

    protected $casts = [
        "embedding" => "array",
        "metadata" => "array",
    ];

    protected $fillable = [
        'slug',
        'topic_vector',
        'topic',
        'description',
        'embedding',
        'parent_id',
        'depth',
        'origin_id',
        'heat_score',
        'path',
        'metadata',
        'file_path'
    ];


    public function parent()
    {
        return $this->belongsTo(Node::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Node::class, 'parent_id');
    }

    public function bridgesAsSource()
    {
        return $this->hasMany(SemanticBridge::class, 'source_node_id');
    }

    public function bridgesAsTarget()
    {
        return $this->hasMany(SemanticBridge::class, 'target_node_id');
    }

    public function strangeIdeas()
    {
        return $this->hasMany(StrangeIdea::class);
    }
}
