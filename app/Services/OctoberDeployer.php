<?php

namespace App\Services;

class OctoberDeployer extends LaravelDeployer
{
    protected $sharedDirs = ['storage', 'themes'];
    protected $writableDirs = ['storage'];
}
