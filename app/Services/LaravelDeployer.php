<?php

namespace App\Services;


class LaravelDeployer extends BaseDeployer
{
    public function deployFrom($gitRepo, $sharedRes = ['storage'], $writableDirs = ['bootstrap/cache', 'storage'])
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