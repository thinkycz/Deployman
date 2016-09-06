<?php

namespace App\Http\Controllers;

use App\Deploy;
use Illuminate\Http\Request;

use App\Http\Requests;

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
        $deploys = auth()->user()->deploys;

        return view('deploys.index', compact('deploys'));
    }

    public function show(Deploy $deploy)
    {
        return view('deploys.show', compact('deploy'));
    }
}
