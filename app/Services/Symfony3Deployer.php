<?php

namespace App\Services;

class Symfony3Deployer extends SymfonyDeloyer
{
    protected $sharedDirs = ['var/logs', 'var/sessions'];
    protected $writableDirs = ['var/cache', 'var/logs', 'var/sessions'];
    protected $binDir = 'bin';
    protected $varDir = 'var';

    public function asseticDump()
    {
        $this->deploy->addToLog('INFO: Running assetic dump.');

        if (!$this->dump_assets) {
            return;
        }

        $php = $this->getPHPBinary();

        $this->console->runAndLog("$php $this->releasePath/$this->binDir/console assets:install --env=$this->env --no-debug $this->releasePath/web", $this->deploy);
    }
}
