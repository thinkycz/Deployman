<?php

namespace App\Services;

use App\Project;
use DateTime;
use Session;

class LaravelDeployer extends BaseDeployer
{
    public function deployProject(Project $project)
    {
        $hash = $folder = null;
        $begin = new DateTime();
        try
        {
            $this->initDirectory($project->path);
            $this->initDeployLog();
            $this->deployFrom($project->repository);
            $hash = $this->getCurrentCommitHash();
            $folder = $this->getCurrentReleaseFolder();
            return $this->createDeployRecord($project, $begin, $hash, $folder);
        }
        catch (\Exception $e)
        {
            Session::push('deploy_log', 'ERROR: ' . $e->getMessage());
            return $this->createDeployRecord($project, $begin, $hash, $folder, false);
        }
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