<?php

namespace App\Http\Controllers;

use App\Deploy;
use App\Helpers\ProjectType;
use App\Project;
use App\Services\ProjectManager;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class ProjectsController extends Controller
{
    /**
     * ProjectsController constructor.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $projects = auth()->user()->projects;
        $active = [];

        foreach ($projects as $project) {
            $manager = new ProjectManager($project);
            $current = $manager->getCurrentReleaseFolder();
            $active[$project->id] = $current;
        }

        return view('projects.index', compact('projects', 'active'));
    }

    public function show(Project $project)
    {
        $manager = new ProjectManager($project);
        $current = $manager->getCurrentReleaseFolder();
        $onServer = $manager->getListOfReleases();
        $deploy = Deploy::where('folder_name', $current)->get()->first();

        return view('projects.show', compact('project', 'current', 'onServer', 'deploy'));
    }

    public function create()
    {
        $supportedProjectTypes = [
            ProjectType::STATIC_PAGES => 'Static pages',
            ProjectType::LARAVEL => 'Laravel'
        ];

        /** @var Collection $connections */
        $connections = auth()->user()->connections;

        if ($connections->isEmpty()) {
            flash('You have no connections!', 'warning');
            return redirect(action('ConnectionsController@index'));
        }

        return view('projects.create', compact('supportedProjectTypes', 'connections'));
    }

    public function store(Request $request)
    {
        Project::create([
            'name' => $request->get('project-name'),
            'type' => $request->get('project-type'),
            'repository' => $request->get('repository'),
            'path' => $request->get('path'),
            'user_id' => auth()->user()->id,
            'connection_id' => $request->get('connection')
        ]);

        return redirect(action('ProjectsController@index'));
    }

    public function check(Project $project)
    {
        return $this->checkRepositoryConnection($project);
    }

    public function deploy(Project $project, Request $request)
    {
        return Deploy::create([
            'user_id' => auth()->user()->id,
            'project_id' => $project->id,
            'branch' => $request->get('branch'),
            'commit_hash' => $request->get('commit'),
        ]);
    }

    public function cleanup(Project $project)
    {
        try {
            $manager = new ProjectManager($project);
            $manager->cleanupOldReleases();
        } catch (\Exception $e) {
            return response($e->getMessage(), 400);
        }

        return response('success');
    }

    public function rollback(Project $project)
    {
        try {
            $manager = new ProjectManager($project);
            $manager->rollbackToPreviousRelease();
        } catch (\Exception $e) {
            return response($e->getMessage(), 400);
        }

        return response('success');
    }

    private function checkRepositoryConnection(Project $project)
    {
        try {
            $manager = new ProjectManager($project);
            $manager->getConsole()->run("git ls-remote -h $project->repository");
        } catch (\Exception $e) {
            return response($e->getMessage(), 400);
        }

        return response('success');
    }
}