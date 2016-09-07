<?php

namespace App\Services;

use App\Project;
use Carbon\Carbon;
use Session;

class LaravelDeployer extends BaseDeployer
{
    protected $hash = null;

    protected $folder = null;

    public function deployProject(Project $project)
    {
        $begin = Carbon::now('Europe/Prague');

        try
        {
            $this->initDeployLog();
            $this->deployFrom($project->repository);
            return $this->createDeployRecord($project, $begin, $this->hash, $this->folder);
        }
        catch (\Exception $e)
        {
            Session::push('deploy_log', 'ERROR: ' . $e->getMessage());
            return $this->createDeployRecord($project, $begin, $this->hash, $this->folder, false);
        }
    }

    protected function deployFrom($gitRepo, $sharedRes = ['storage'], $writableDirs = ['bootstrap/cache', 'storage'])
    {
        Session::push('deploy_log', 'INFO: Preparing to deploy');
        $this->prepareToDeploy();

        Session::push('deploy_log', 'INFO: Preparing release folders');
        $this->folder = $this->prepareReleaseFolders();

        Session::push('deploy_log', 'INFO: Cloning code from Git repository');
        $this->hash = $this->pullCodeFromGit($gitRepo);

        Session::push('deploy_log', 'INFO: Creating symlinks to shared resources');
        $this->createSymlinksToSharedResources($sharedRes);

        Session::push('deploy_log', 'INFO: Setting directory permissions');
        $this->makeDirectoriesWritable($writableDirs);

        Session::push('deploy_log', 'INFO: Running composer');
        $this->installVendors();

        Session::push('deploy_log', 'INFO: Creating symlink to current folder');
        $this->createSymlinkToCurrent();

        return true;
    }
}