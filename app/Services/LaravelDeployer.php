<?php

namespace App\Services;

use App\Project;
use DateTime;
use Session;

class LaravelDeployer extends BaseDeployer
{
    protected $hash = null;

    protected $folder = null;

    public function deployProject(Project $project)
    {
        $begin = new DateTime();
        try
        {
            $this->initDirectory($project->path);
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
        $this->prepareToDeploy();
        $this->folder = $this->prepareReleaseFolders();
        $this->hash = $this->pullCodeFromGit($gitRepo);
        $this->createSymlinksToSharedResources($sharedRes);
        $this->makeDirectoriesWritable($writableDirs);
        $this->installVendors();
        $this->createSymlinkToCurrent();

        return true;
    }
}