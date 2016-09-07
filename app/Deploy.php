<?php

namespace App;

use App\Helpers\DeployStatus;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Deploy extends Model
{
    protected $fillable = [
        'user_id',
        'project_id'
    ];

    protected $dates = [
        'finished_at'
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
            'status' => DeployStatus::PENDING
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

    public function addToLog($line)
    {
        $log = unserialize($this->log);
        $log[] = $line;
        $this->log = serialize($log);
        $this->save();
    }

    public function setStatus($status)
    {
        $this->status = $status;
        $this->save();
    }

    public function setDeployComplete($complete)
    {
        $this->deploy_complete = $complete;
        $this->save();
    }

    public function setFinished($time = null)
    {
        $this->finished_at = $time ?: Carbon::now();
        $this->save();
    }
}
