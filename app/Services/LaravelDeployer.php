<?php

namespace App\Services;

use App\Project;

class LaravelDeployer extends BaseDeployer
{
    public function deployProject(Project $project)
    {
        $this->initDirectory($project->path);
        $this->initDeployLog();
        $this->deployFrom($project->repository);
        return $this->createDeployRecord($project);
    }

    protected function deployFrom($gitRepo, $sharedRes = ['storage'], $writableDirs = ['bootstrap/cache', 'storage'])
    {
        $this->prepareToDeploy();
        $this->prepareReleaseFolders();
        $this->pullCodeFromGit($gitRepo);
        $this->createSymlinksToSharedResources($sharedRes);
        $this->makeDirectoriesWritable($writableDirs);
        $this->installVendors();
        $this->createSymlinkToCurrent();

        return true;
    }
}