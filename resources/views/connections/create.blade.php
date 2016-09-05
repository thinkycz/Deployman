@extends('layouts.app')

@section('content')
    <form class="form-horizontal" id="connection_form" method="post" action="{{ action('ConnectionsController@store') }}">
        {{ csrf_field() }}
        <fieldset>
            <legend>Create a new connection</legend>

            <div class="form-group">
                <label class="col-md-4 control-label" for="name">Connection name</label>
                <div class="col-md-4">
                    <input id="name" name="name" type="text" placeholder="eg. My home server" class="form-control input-md" required="">
                </div>
            </div>

            <div class="form-group">
                <label class="col-md-4 control-label" for="hostname">Hostname</label>
                <div class="col-md-4">
                    <input id="hostname" name="hostname" type="text" placeholder="eg. myhomeserver.dev" class="form-control input-md" required="">
                    <span class="help-block">Enter your server address</span>
                </div>
            </div>

            @if(!empty($supportedConnectionMethods))
                <div class="form-group">
                    <label class="col-md-4 control-label" for="method">Authentication method</label>
                    <div class="col-md-4">
                        <select id="method" name="method" class="form-control" v-model="method">
                            @foreach($supportedConnectionMethods as $method => $description)
                                <option value="{{ $method }}">{{ $description }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            @endif

            <div class="form-group">
                <label class="col-md-4 control-label" for="username">Username</label>
                <div class="col-md-4">
                    <input id="username" name="username" type="text" placeholder="eg. admin" class="form-control input-md" required="">
                    <span class="help-block">Enter the username to sign in</span>
                </div>
            </div>

            <div class="auth_password" v-if="method == 0">
                <div class="form-group">
                    <label class="col-md-4 control-label" for="password">Password</label>
                    <div class="col-md-4">
                        <input id="password" name="password" type="text" placeholder="* * * * * *" class="form-control input-md" @keyPress="passwordTyped">
                        <span class="help-block">Enter the password (optional)</span>
                    </div>
                </div>
            </div>

            <div class="auth_keys" v-if="method == 2">
                <div class="form-group">
                    <label class="col-md-4 control-label" for="private_key">Private key</label>
                    <div class="col-md-4">
                        <textarea class="form-control" id="private_key" name="private_key" required></textarea>
                        <span class="help-block">Paste yout private key here</span>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-md-4 control-label" for="public_key">Public key</label>
                    <div class="col-md-4">
                        <textarea class="form-control" id="public_key" name="public_key"></textarea>
                        <span class="help-block">Paste yout public key here (optional)</span>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-md-4 control-label" for="passphrase">Passphrase</label>
                    <div class="col-md-4">
                        <input id="passphrase" name="passphrase" type="text" placeholder="* * * * * *" class="form-control input-md">
                        <span class="help-block">Enter the passphrase to your keys (optional)</span>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <div class="col-md-4 col-md-offset-4">
                    <input type="submit" class="btn btn-primary" value="Create this connection">
                </div>
            </div>
        </fieldset>
    </form>
@endsection