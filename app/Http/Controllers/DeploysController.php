<?php

namespace App\Http\Controllers;

use App\Deploy;
use Illuminate\Http\Request;

use App\Http\Requests;

class DeploysController extends Controller
{
    public function index()
    {
        $deploys = Deploy::all();

        return view('deploys.index', compact('deploys'));
    }
}
