<?php

namespace App\Http\Controllers;

use App\Connection;
use App\Services\RemoteConsole;
use Deployer\Server\Configuration;
use Illuminate\Http\Request;

class ConnectionsController extends Controller
{
    /**
     * ConnectionsController constructor.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $connections = auth()->user()->connections;

        return view('connections.index', compact('connections'));
    }

    public function create()
    {
        $supportedConnectionMethods = [
            Configuration::AUTH_BY_IDENTITY_FILE => 'Authenticate by public key',
            Configuration::AUTH_BY_PASSWORD => 'Authenticate by credentials'
        ];

        $publicKey = app(RemoteConsole::class)->getPublicKey();

        return view('connections.create', compact('supportedConnectionMethods', 'publicKey'));
    }

    public function store(Request $request)
    {
        $connection = Connection::create([
            'name' => $request->get('name'),
            'hostname' => $request->get('hostname'),
            'method' => $request->get('method'),
            'username' => $request->get('username'),
            'user_id' => auth()->user()->id
        ]);

        if ($connection->method == Configuration::AUTH_BY_PASSWORD) {
            $connection->password = $request->has('password') ? encrypt($request->get('password')) : null;
            $connection->save();
        }

        return redirect(action('ConnectionsController@index'));
    }

    public function edit(Connection $connection)
    {
        $supportedConnectionMethods = [
            Configuration::AUTH_BY_IDENTITY_FILE => 'Authenticate by public key',
            Configuration::AUTH_BY_PASSWORD => 'Authenticate by credentials'
        ];

        $publicKey = app(RemoteConsole::class)->getPublicKey();

        return view('connections.edit', compact('connection', 'supportedConnectionMethods', 'publicKey'));
    }

    public function update(Connection $connection, Request $request)
    {
        $connection->name = $request->get('name');
        $connection->hostname = $request->get('hostname');
        $connection->method = $request->get('method');
        $connection->username = $request->get('username');

        if ($connection->method == Configuration::AUTH_BY_PASSWORD) {
            $connection->password = $request->has('password') ? encrypt($request->get('password')) : null;
        }

        $connection->saveOrFail();

        flash('Connection has been updated', 'success');
        return redirect(action('ConnectionsController@index'));
    }

    public function destroy(Connection $connection)
    {
        $connection->delete();
        flash('Connection has been deleted', 'success');
        return action('ConnectionsController@index');
    }

    public function check(Connection $connection)
    {
        try {
            $console = app(RemoteConsole::class);
            $console->useConnectionObject($connection)->run('pwd');
        } catch (\Exception $e) {
            return response($e->getMessage(), 400);
        }

        return response('success');
    }
}
