<?php

namespace App\Http\Controllers;

use App\Services\BaseDeployer;
use App\Services\RemoteConsole;

class DashboardController extends Controller
{
    /**
     * @var RemoteConsole
     */
    private $console;
    /**
     * @var BaseDeployer
     */
    private $baseDeployer;

    /**
     * DeploymanController constructor.
     * @param RemoteConsole $console
     * @param BaseDeployer $baseDeployer
     */
    public function __construct(RemoteConsole $console, BaseDeployer $baseDeployer)
    {
        $this->middleware('auth');

        $this->console = $console;
        $this->baseDeployer = $baseDeployer;
    }

    public function index() {
        $this->console->connectTo('raspberrypi.local')->withIdentityFile()->andWithUsername('pi');

        $this->baseDeployer->initDirectory('/var/www/sapaguide_self_deploy');

        $result = $this->baseDeployer->getListOfReleases();

        return view('deployman.index', compact('result'));
    }
}
