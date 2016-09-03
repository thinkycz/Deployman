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

        $this->deployHelper->prepareReleaseFolders();
        $result = $this->deployHelper->updateCodeFromGit('git@github.com:thinkycz/hanzi.git');

        dd($result);
    }
}
