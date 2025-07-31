<?php

namespace App\Models;

class StrangeIdea extends Model
{
    protected $fillable = ['idea', 'source', 'confidence', 'node_id'];

    public function node()
    {
        return $this->belongsTo(Node::class);
    }
}
