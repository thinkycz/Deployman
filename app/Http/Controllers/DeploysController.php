<?php

namespace App\Http\Controllers;

use App\Deploy;
use Illuminate\Http\Request;

use App\Http\Requests;

class DeploysController extends Controller
{
    public function index()
    {
        $deploys = Deploy::where('user_id', auth()->user()->id)->get();

        return view('deploys.index', compact('deploys'));
    }

    public function show(Deploy $deploy)
    {
        return view('deploys.show', compact('deploy'));
    }
}
