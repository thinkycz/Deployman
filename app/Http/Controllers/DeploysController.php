<?php

namespace App\Http\Controllers;

use App\Deploy;
use App\Services\LaravelDeployer;
use App\Services\RemoteConsole;

class DeploysController extends Controller
{
    /**
     * @var RemoteConsole
     */
    private $console;

    /**
     * DeploysController constructor.
     * @param RemoteConsole $console
     */
    public function __construct(RemoteConsole $console)
    {
        $this->middleware('auth');
        $this->console = $console;
    }

    public function index()
    {
        $deploys = auth()->user()->deploys;

        return view('deploys.index', compact('deploys'));
    }

    public function show(Deploy $deploy)
    {
        return view('deploys.show', compact('deploy'));
    }

    public function fire(Deploy $deploy)
    {
        $this->console->useConnectionObject($deploy->project->connection);
        $deployer = new LaravelDeployer($this->console, $deploy);

        return $deployer->run();
    }
}
