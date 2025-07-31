<?php

namespace App\Models;

use Leaf\Model;

class User extends Model
{
    protected $table = "users";

    protected $fillable = ['wallet', 'credits'];
}
