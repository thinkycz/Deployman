<?php

namespace App\Http\Controllers;

use App\Connection;
use App\Helpers\ProjectTypes;
use App\Project;
use App\Services\LaravelDeployer;
use App\Services\RemoteConsole;
use Illuminate\Http\Request;
use App\Http\Requests;

class ProjectsController extends Controller
{
    /**
     * @var RemoteConsole
     */
    private $console;

    /**
     * ProjectsController constructor.
     * @param RemoteConsole $console
     */
    public function __construct(RemoteConsole $console)
    {
        $this->middleware('auth');
        $this->console = $console;
    }

    public function index()
    {
        $projects = auth()->user()->projects;

        return view('projects.index', compact('projects'));
    }

    public function show(Project $project)
    {
        return view('projects.show', compact('project'));
    }

    public function create()
    {
        $supportedProjectTypes = [
            ProjectTypes::STATIC_PAGES => 'Static pages',
            ProjectTypes::LARAVEL => 'Laravel'
        ];

        $connections = Connection::where('user_id', auth()->user()->id)->get();

        if (empty($connections)) {
            // todo
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

    public function deploy(Project $project)
    {
        $connection = $project->connection;
        $hostname = $connection->hostname;
        $username = $connection->username;
        $password = $connection->password;

        $this->console->connectTo($hostname)->withCredentials($username, $password);
        $laravel = new LaravelDeployer($this->console);
        $deploy = $laravel->deployProject($project);

        return redirect(action('DeploysController@show', $deploy));
    }

    private function checkRepositoryConnection(Project $project)
    {
        $connection = $project->connection;
        $hostname = $connection->hostname;
        $username = $connection->username;
        $password = $connection->password;

        try
        {
            $this->console->connectTo($hostname)->withCredentials($username, $password)->run("git ls-remote -h $project->repository");
        }
        catch (\Exception $e)
        {
            return response($e->getMessage(), 400);
        }

        return response('success');
    }
}