<?php

namespace App\Http\Controllers;

use App\Connection;
use App\Helpers\ProjectTypes;
use App\Project;
use Illuminate\Http\Request;

use App\Http\Requests;

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
        $projects = Project::where('user_id', auth()->user()->id)->get();

        return view('projects.index', compact('projects'));
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

}