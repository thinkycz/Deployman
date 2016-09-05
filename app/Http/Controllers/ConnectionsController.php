<?php

namespace App\Http\Controllers;

use App\Connection;
use Deployer\Server\Configuration;
use Illuminate\Http\Request;

use App\Http\Requests;

class ConnectionsController extends Controller
{
    public function index()
    {
        $connections = Connection::where('user_id', auth()->user()->id)->get();

        return view('connections.index', compact('connections'));
    }

    public function create()
    {
        $supportedConnectionMethods = [
            Configuration::AUTH_BY_PASSWORD => 'Authenticate by credentials',
            Configuration::AUTH_BY_IDENTITY_FILE => 'Authenticate by public and private keys'
        ];

        return view('connections.create', compact('supportedConnectionMethods'));
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
            $connection->password = $request->has('password') ? $request->get('password') : null;
            $connection->save();
        } elseif ($connection->method == Configuration::AUTH_BY_IDENTITY_FILE) {
            $connection->public_key = $request->has('public_key') ? $request->get('public_key') : null;
            $connection->private_key = $request->has('private_key') ? $request->get('private_key') : null;
            $connection->passphrase = $request->has('passphrase') ? $request->get('passphrase') : null;
            $connection->save();
        }

        return redirect(action('ConnectionsController@index'));
    }
}