<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Deploy extends Model
{
    protected $fillable = [
        'user_id',
        'project_id',
        'log',
        'commit_hash',
        'folder_name'
    ];
}
