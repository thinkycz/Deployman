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
     * DeploymanController constructor.
     * @param RemoteConsole $console
     */
    public function __construct(RemoteConsole $console)
    {
        $this->middleware('auth');

        $this->console = $console;
    }

    public function index() {
        return redirect(action('ProjectsController@index'));
    }
}
