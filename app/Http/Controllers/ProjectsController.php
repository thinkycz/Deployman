<?php

namespace App\Http\Controllers;

use App\Helpers\ProjectTypes;
use App\Project;
use Auth;
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
        $projects = Project::where('user_id', Auth::user()->id)->get();

        return view('projects.index', compact('projects'));
    }

    public function create()
    {
        $supportedProjectTypes = [
            ProjectTypes::STATIC_PAGES => 'Static pages',
            ProjectTypes::LARAVEL => 'Laravel'
        ];

        return view('projects.create', compact('supportedProjectTypes'));
    }

    public function store(Request $request)
    {
        Project::create([
            'name' => $request->get('project-name'),
            'type' => $request->get('project-type'),
            'repository' => $request->get('repository'),
            'path' => $request->get('path'),
            'user_id' => Auth::user()->id
        ]);

        return redirect(action('ProjectsController@index'));
    }

}