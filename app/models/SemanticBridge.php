<?php

namespace App\Models;

use Leaf\Model;

class SemanticBridge extends Model
{
    protected $table = "semantic_bridges";
    protected $fillable = [
        'source_node_id',
        'target_node_id',
        'cosine_score',
        'label',
    ];

    public function source()
    {
        return $this->belongsTo(Node::class, 'source_node_id');
    }

    public function target()
    {
        return $this->belongsTo(Node::class, 'target_node_id');
    }
}
