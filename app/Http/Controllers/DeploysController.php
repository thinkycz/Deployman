<?php

namespace App\Http\Controllers;

use App\Deploy;
use App\Helpers\AfterDeployMethods;
use App\Helpers\ProjectType;
use App\Services\BaseDeployer;
use App\Services\LaravelDeployer;
use App\Services\StaticPagesDeployer;
use App\Services\Symfony3Deployer;
use App\Services\SymfonyDeloyer;
use ReflectionClass;
use ReflectionMethod;

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
        $methods = array_map(function ($method) {
            return [
                'method' => $method,
                'description' => AfterDeployMethods::$methodDescriptions[$method->name]
            ];
        }, $this->getProjectAdditionalActions($deploy));

        return view('deploys.show', compact('deploy', 'methods'));
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
        $deployer = $this->getProjectDeployerClass($deploy);

        if (!$deployer) {
            return null;
        }

        return new $deployer($deploy);
    }

    private function getProjectDeployerClass(Deploy $deploy)
    {
        switch ($deploy->project->type) {
            case ProjectType::LARAVEL:
                return LaravelDeployer::class;
            case ProjectType::SYMFONY2:
                return SymfonyDeloyer::class;
            case ProjectType::SYMFONY3:
                return Symfony3Deployer::class;
            case ProjectType::STATIC_PAGES:
                return StaticPagesDeployer::class;
            default:
                return BaseDeployer::class;
        }
    }

    private function getProjectAdditionalActions(Deploy $deploy)
    {
        $deployer = $this->getProjectDeployerClass($deploy);

        if (!$deployer) {
            return [];
        }

        $reflectionClass = new ReflectionClass($deployer);
        $methods = $reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC);

        return array_filter($methods, function ($method) use ($deployer) {

            if ($deployer == Symfony3Deployer::class) {
                return ($method->class == $deployer or $method->class == SymfonyDeloyer::class) and $method->name != 'run';
            }

            return $method->class == $deployer and $method->name != 'run';
        });
    }
}
