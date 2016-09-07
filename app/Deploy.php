<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Deploy extends Model
{
    protected $fillable = [
        'user_id',
        'project_id'
    ];

    protected $dates = [
        'completed_at'
    ];

    /**
     * Deploy constructor.
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        $now = Carbon::now();
        $this->setRawAttributes([
            'created_at' => $now,
            'updated_at' => $now,
            'folder_name' => $now->format('YmdHis'),
            'deploy_complete' => false,
            'status' => 'pending'
        ]);

        parent::__construct($attributes);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
