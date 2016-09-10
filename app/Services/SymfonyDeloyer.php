<?php

namespace App\Services;

use App\Helpers\DeployStatus;
use Exception;

class SymfonyDeloyer extends BaseDeployer
{
    protected $copyDirs = [];
    protected $sharedDirs = ['app/logs'];
    protected $sharedFiles = ['app/config/parameters.yml'];
    protected $writableDirs = ['app/cache', 'app/logs'];
    protected $envVars = 'SYMFONY_ENV=prod';
    protected $env = 'prod';
    protected $binDir = 'app';
    protected $varDir = 'app';
    protected $assets = ['web/css', 'web/images', 'web/js'];
    protected $dump_assets = true;

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

    /**
     * Create cache dir
     */
    public function createCacheDirectory()
    {
        $this->deploy->addToLog('INFO: Creating cache directory.');

        // Set cache dir
        $cacheDir = "$this->releasePath/$this->varDir/cache";

        // Remove cache dir if it exist
        $this->console->runAndLog("if [ -d \"$cacheDir\" ]; then rm -rf {{cache_dir}}; fi", $this->deploy);

        // Create cache dir
        $this->console->runAndLog("mkdir -p $cacheDir", $this->deploy);

        // Set rights
        $this->console->runAndLog("chmod -R g+w $cacheDir", $this->deploy);
    }

    /**
     * Normalize asset timestamps
     */
    public function normalizeAssetTimestamps()
    {
        $this->deploy->addToLog('INFO: Normalizing asset timestamps.');

        $assets = implode(' ', array_map(function ($asset) {
            return "$this->releasePath/$asset";
        }, $this->assets));

        $time = date('Ymdhi.s');

        $this->console->runAndLog("find $assets -exec touch -t $time {} ';' &> /dev/null || true", $this->deploy);
    }

    /**
     * Dump all assets to the filesystem
     */
    public function asseticDump()
    {
        $this->deploy->addToLog('INFO: Running assetic dump.');

        if (!$this->dump_assets) {
            return;
        }

        $php = $this->getPHPBinary();

        $this->console->runAndLog("$php $this->releasePath/$this->binDir/console assetic:dump --env=$this->env --no-debug", $this->deploy);
    }

    /**
     * Warm up cache
     */
    public function cacheWarmup()
    {
        $this->deploy->addToLog('INFO: Warming up cache.');

        $php = $this->getPHPBinary();

        $this->console->runAndLog("$php $this->releasePath/$this->binDir/console cache:warmup  --env=$this->env --no-debug", $this->deploy);
    }

    /**
     * Migrate database
     */
    public function doctrineMigrate()
    {
        $this->deploy->addToLog('INFO: Ronning doctrine migrations.');

        $php = $this->getPHPBinary();

        $this->console->runAndLog("$php $this->releasePath/$this->binDir/console doctrine:migrations:migrate --env=$this->env --no-debug --no-interaction", $this->deploy);
    }

    /**
     * Remove app_dev.php files
     */
    public function clearControllers()
    {
        $this->deploy->addToLog('INFO: Clearing development environment specific files.');

        $this->console->runAndLog("rm -f $this->releasePath/web/app_*.php", $this->deploy);
        $this->console->runAndLog("rm -f $this->releasePath/web/config.php", $this->deploy);
    }
}
