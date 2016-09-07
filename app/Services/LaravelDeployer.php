<?php

namespace App\Services;

use App\Helpers\DeployStatus;
use Exception;

class LaravelDeployer extends BaseDeployer
{
    protected $copyDirs = [];
    protected $sharedDirs = ['storage'];
    protected $sharedFiles = ['.env'];
    protected $writableDirs = ['bootstrap/cache', 'storage'];
    protected $envVars = null;

    public function run()
    {
        $this->deploy->setStatus(DeployStatus::RUNNING);

        try
        {
            $this->prepareToDeploy();
            $this->prepareReleaseFolders();
            $this->pullCodeFromGit();
            $this->copyDirectories($this->copyDirs);
            $this->createSymlinksToSharedResources($this->sharedDirs, $this->sharedFiles);
            $this->makeDirectoriesWritable($this->writableDirs);
            $this->installVendors($this->envVars);
            $this->createSymlinkToCurrent();
        }
        catch (Exception $e)
        {
            $this->deploy->setStatus(DeployStatus::FAILED);
            $this->deploy->addToLog('ERROR: ' . $e->getMessage());
            return $this->deploy;
        }

        $this->deploy->setStatus(DeployStatus::FINISHED);
        $this->deploy->setDeployComplete(true);
        $this->deploy->addToLog('SUCCESS: The project has been successfully deployed.');
        return $this->deploy;
    }
}