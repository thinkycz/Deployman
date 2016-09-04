<?php

namespace App\Http\Controllers;

use App\Services\DeployHelper;
use App\Services\RemoteConsole;

class DeploymanController extends Controller
{
    /**
     * @var RemoteConsole
     */
    private $console;
    /**
     * @var DeployHelper
     */
    private $deployHelper;

    /**
     * DeploymanController constructor.
     * @param RemoteConsole $console
     * @param DeployHelper $deployHelper
     */
    public function __construct(RemoteConsole $console, DeployHelper $deployHelper)
    {
        $this->console = $console;
        $this->deployHelper = $deployHelper;
    }

    public function index() {
        $this->console->connectTo('raspberrypi.local')->withIdentityFile()->andWithUsername('pi');

        $this->deployHelper->init();

//        $this->deployHelper->prepareToDeploy();
//        $this->deployHelper->prepareReleaseFolders();
//        $this->deployHelper->pullCodeFromGit('https://github.com/thinkycz/SapaGuideAPI');
//        $this->deployHelper->createSymlinksToSharedResources(['storage']);
//        $this->deployHelper->makeDirectoriesWritable(['bootstrap/cache', 'storage']);
//        $this->deployHelper->installVendors();
//        $this->deployHelper->createSymlinkToCurrent();

        $result = $this->deployHelper->rollbackToPreviousRelease();

        dd($result);
    }
}
