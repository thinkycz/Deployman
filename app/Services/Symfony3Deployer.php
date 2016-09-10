<?php

namespace App\Services;

use App\Helpers\DeployStatus;
use Exception;

class Symfony3Deployer extends SymfonyDeloyer
{
    protected $sharedDirs = ['var/logs', 'var/sessions'];
    protected $writableDirs = ['var/cache', 'var/logs', 'var/sessions'];
    protected $binDir = 'bin';
    protected $varDir = 'var';

    public function run()
    {
        $this->deploy->setStatus(DeployStatus::RUNNING);

        try {
            $this->prepareToDeploy();
            $this->prepareReleaseFolders();
            $this->pullCodeFromGit();
            $this->clearControllers();
            $this->copyDirectories($this->copyDirs);
            $this->createCacheDirectory();
            $this->createSymlinksToSharedResources($this->sharedDirs, $this->sharedFiles);
            $this->normalizeAssetTimestamps();
            $this->installVendors($this->envVars);
            $this->asseticDump();
            $this->cacheWarmup();
            $this->makeDirectoriesWritable($this->writableDirs);
            $this->createSymlinkToCurrent();
        } catch (Exception $e) {
            $this->deploy->setFinished();
            $this->deploy->setStatus(DeployStatus::FAILED);
            $this->deploy->addToLog('ERROR: ' . $e->getMessage());
            return $this->deploy;
        }

        $this->deploy->setFinished();
        $this->deploy->setStatus(DeployStatus::FINISHED);
        $this->deploy->setDeployComplete(true);
        $this->deploy->addToLog('SUCCESS: The project has been successfully deployed.');
        return $this->deploy;
    }

    public function asseticDump()
    {
        if (!$this->dump_assets) {
            return;
        }

        $php = $this->getPHPBinary();

        $this->console->runAndLog("$php $this->releasePath/$this->binDir/console assets:install --env=$this->env --no-debug $this->releasePath/web", $this->deploy);
    }
}
