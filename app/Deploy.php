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
        'folder_name',
        'deploy_complete',
        'deployed_at'
    ];

    protected $dates = [
        'deployed_at'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
