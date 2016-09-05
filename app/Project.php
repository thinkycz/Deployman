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

    public function connection()
    {
        return $this->belongsTo(Connection::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
