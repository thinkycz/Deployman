<?php

namespace App\Http\Controllers;

use App\Deploy;
use App\Helpers\ProjectType;
use App\Project;
use App\Services\BaseDeployer;
use App\Services\LaravelDeployer;
use App\Services\RemoteConsole;
use App\Services\StaticPagesDeployer;

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
        if (Deploy::where('status', 'running')->get()->isEmpty()) {

            $this->console->useConnectionObject($deploy->project->connection);
            $deployer = $this->determineProjectDeployer($deploy);

        } else {
            sleep(5);
            $this->fire($deploy);
        }
        return $deployer->run();
    }

    public function status(Deploy $deploy)
    {
        $ajax = true;
        $queue = !Deploy::where('status', 'running')->where('id', '!=', $deploy->id)->get()->isEmpty();

        return [
            'html' => view('partials.terminal_log', compact('deploy', 'ajax', 'queue'))->render(),
            'deploy' => $deploy,
            'queue' => $queue
        ];
    }

    /**
     * @param Deploy $deploy
     * @return BaseDeployer
     */
    private function determineProjectDeployer(Deploy $deploy)
    {
        switch ($deploy->project->type)
        {
            case ProjectType::LARAVEL:
                return new LaravelDeployer($this->console, $deploy);
            case ProjectType::STATIC_PAGES:
                return new StaticPagesDeployer($this->console, $deploy);
            default:
                return new BaseDeployer($this->console, $deploy);
        }
    }
}
