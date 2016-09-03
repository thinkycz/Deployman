<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use App\Services\RemoteConsole;

class DeploymanController extends Controller
{
    /**
     * @var RemoteConsole
     */
    private $console;

    /**
     * DeploymanController constructor.
     * @param RemoteConsole $console
     */
    public function __construct(RemoteConsole $console)
    {
        $this->console = $console;
    }

    public function index() {
        $this->console->connect('raspberrypi.local')->withIdentityFile()->andWithUsername('pi');

        $result = $this->console->run('ls -la')->toArray();

        return view('deployman.index', compact('result'));
    }
}
