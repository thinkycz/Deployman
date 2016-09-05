<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Connection extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'hostname',
        'method',
        'username'
    ];

    public function projects()
    {
        return $this->hasMany(Project::class);
    }
}
