<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function connections()
    {
        return $this->hasMany(Connection::class);
    }

    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    public function deploys()
    {
        return $this->hasMany(Deploy::class);
    }
}
