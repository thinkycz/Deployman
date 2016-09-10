<?php

namespace App\Http\Controllers;

use App\Deploy;
use App\Project;
use App\Services\ProjectManager;

class DashboardController extends Controller
{
    /**
     * DeploymanController constructor.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $connectionsCount = auth()->user()->connections()->count();
        $projectsCount = auth()->user()->projects()->count();
        $successfulDeploysCount = auth()->user()->deploys()->where('deploy_complete', true)->count();
        $failedDeploysCount = auth()->user()->deploys()->where('deploy_complete', false)->count();
        $projects = auth()->user()->projects()->get();

        return view('dashboard.index', compact('connectionsCount', 'projectsCount', 'successfulDeploysCount', 'failedDeploysCount', 'projects'));
    }

    public function projectbox(Project $project)
    {
        try {
            $manager = new ProjectManager($project);
            $current = $manager->getCurrentReleaseFolder();
            $deploy = Deploy::where('folder_name', $current)->get()->first();
        } catch (\Exception $e) {
            return response($e->getMessage(), 400);
        }

        return view('dashboard.project_box_ajax', compact('project', 'deploy'));
    }
}
