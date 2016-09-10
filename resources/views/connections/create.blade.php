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
                        <span class="help-block">Enter the password</span>
                    </div>
                </div>
            </div>

            <div class="auth_keys" v-if="method == 2">
                <div class="form-group">
                    <label class="col-md-4 control-label" for="publicKey">Public key</label>
                    <div class="col-md-4">
                        <textarea class="form-control" id="publicKey" name="publicKey" rows="20" disabled>{{ $publicKey }}</textarea>
                        <span class="help-block">Please add this key to the server's authorized_keys, so Deployman can SSH into your server and do his job :)</span>
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

@section('scripts')
    <script>
        const connectionForm = new Vue({
            el: '#connection_form',
            data: {
                method: '2',
                password: false
            },
            methods: {
                'passwordTyped': function () {
                    if (!this.password) {
                        this.password = true;
                        swal({
                            title: "Important!",
                            text: "Your password will be stored in our database in a human readable form! Unsalted, unencrypted! Deployman will use this password to SSH to your server.\n\nWe strongly recommend using public key authentication.",
                            type: "info",
                            confirmButtonText: "I understand"
                        });
                    }
                }
            }
        });
    </script>
@endsection