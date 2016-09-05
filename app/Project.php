<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $fillable = [
        'user_id',
        'connection_id',
        'name',
        'type',
        'repository',
        'path'
    ];
}
