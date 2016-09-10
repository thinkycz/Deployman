<?php

namespace App\Http\Controllers;

use App\Deploy;
use App\Helpers\ProjectType;
use App\Services\BaseDeployer;
use App\Services\LaravelDeployer;
use App\Services\StaticPagesDeployer;
use App\Services\Symfony3Deployer;
use App\Services\SymfonyDeloyer;

class DeploysController extends Controller
{
    /**
     * DeploysController constructor.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $deploys = auth()->user()->deploys()->latest()->get();

        return view('deploys.index', compact('deploys'));
    }

    public function show(Deploy $deploy)
    {
        return view('deploys.show', compact('deploy'));
    }

    public function fire(Deploy $deploy)
    {
        while (!Deploy::where('status', 'running')->get()->isEmpty()) {
            sleep(5);
        }
        $deployer = $this->determineProjectDeployer($deploy);
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
        switch ($deploy->project->type) {
            case ProjectType::LARAVEL:
                return new LaravelDeployer($deploy);
            case ProjectType::SYMFONY2:
                return new SymfonyDeloyer($deploy);
            case ProjectType::SYMFONY3:
                return new Symfony3Deployer($deploy);
            case ProjectType::STATIC_PAGES:
                return new StaticPagesDeployer($deploy);
            default:
                return new BaseDeployer($deploy);
        }
    }
}
