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

        try {
            $this->prepareToDeploy();
            $this->prepareReleaseFolders();
            $this->pullCodeFromGit();
            $this->copyDirectories($this->copyDirs);
            $this->createSymlinksToSharedResources($this->sharedDirs, $this->sharedFiles);
            $this->makeDirectoriesWritable($this->writableDirs);
            $this->installVendors($this->envVars);
            $this->createSymlinkToCurrent();
            $this->clearCache();
            $this->configCache();
        } catch (Exception $e) {
            $this->deploy->setFinished();
            $this->deploy->setStatus(DeployStatus::FAILED);
            $this->deploy->addToLog('ERROR: Deployment failed. Please check the log below for more information.');
            $this->parseAndLogExceptionMessage($e->getMessage());
            return $this->deploy;
        }

        $this->deploy->setFinished();
        $this->deploy->setStatus(DeployStatus::FINISHED);
        $this->deploy->setDeployComplete(true);
        $this->deploy->addToLog('SUCCESS: The project has been successfully deployed.');
        return $this->deploy;
    }

    public function disableMaintenance()
    {
        $this->deploy->addToLog('INFO: Disabling maintenance mode.');

        $php = $this->getPHPBinary();
        return $this->console->runAndLog("$php $this->deployPath/current/artisan up", $this->deploy);
    }

    public function enableMaintenance()
    {
        $this->deploy->addToLog('INFO: Enabling maintenance mode.');

        $php = $this->getPHPBinary();
        return $this->console->runAndLog("$php $this->deployPath/current/artisan down", $this->deploy);
    }

    public function migrate()
    {
        $this->deploy->addToLog('INFO: Running artisan migrate.');

        $php = $this->getPHPBinary();
        return $this->console->runAndLog("$php $this->deployPath/current/artisan migrate --force", $this->deploy);
    }

    public function rollbackMigrations()
    {
        $this->deploy->addToLog('INFO: Running artisan migrate:rollback.');

        $php = $this->getPHPBinary();
        return $this->console->runAndLog("$php $this->deployPath/current/artisan migrate:rollback --force", $this->deploy);
    }

    public function migrationStatus()
    {
        $this->deploy->addToLog('INFO: Running artisan migrate:status.');

        $php = $this->getPHPBinary();
        return $this->console->runAndLog("$php $this->deployPath/current/artisan migrate:status", $this->deploy);
    }

    public function seedDatabase()
    {
        $this->deploy->addToLog('INFO: Running artisan db:seed.');

        $php = $this->getPHPBinary();
        return $this->console->runAndLog("$php $this->deployPath/current/artisan db:seed --force", $this->deploy);
    }

    public function clearCache()
    {
        $this->deploy->addToLog('INFO: Clearing cache.');

        $php = $this->getPHPBinary();
        return $this->console->runAndLog("$php $this->deployPath/current/artisan cache:clear", $this->deploy);
    }

    public function configCache()
    {
        $this->deploy->addToLog('INFO: Configuring cache.');

        $php = $this->getPHPBinary();
        return $this->console->runAndLog("$php $this->deployPath/current/artisan config:cache", $this->deploy);
    }

    public function routeCache()
    {
        $this->deploy->addToLog('INFO: Running artisan route:cache.');

        $php = $this->getPHPBinary();
        return $this->console->runAndLog("$php $this->deployPath/current/artisan route:cache", $this->deploy);
    }

    public function createSymlinkToPublicStorage()
    {
        $this->deploy->addToLog('INFO: Creating symbolic links to public storage.');

        // Remove from source.
        $this->console->runAndLog("if [ -d $(echo $this->releasePath/public/storage) ]; then rm -rf $this->releasePath/public/storage; fi", $this->deploy);

        // Create shared dir if it does not exist.
        $this->console->runAndLog("mkdir -p $this->deployPath/shared/storage/app/public", $this->deploy);

        // Symlink shared dir to release dir
        $this->console->runAndLog("ln -nfs $this->deployPath/shared/storage/app/public $this->releasePath/public/storage", $this->deploy);
    }
}