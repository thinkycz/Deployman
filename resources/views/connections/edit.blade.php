@extends('layouts.app')

@section('content')
    <form class="form-horizontal" id="connection_form" method="post" action="{{ action('ConnectionsController@update', $connection) }}">
        {{ csrf_field() }}
        {{ method_field('PATCH') }}

        <fieldset>
            <legend>Edit connection</legend>

            <div class="form-group">
                <label class="col-md-4 control-label" for="name">Connection name</label>
                <div class="col-md-4">
                    <input id="name" name="name" type="text" placeholder="eg. My home server" class="form-control input-md" required="" value="{{ $connection->name }}">
                </div>
            </div>

            <div class="form-group">
                <label class="col-md-4 control-label" for="hostname">Hostname</label>
                <div class="col-md-4">
                    <input id="hostname" name="hostname" type="text" placeholder="eg. myhomeserver.dev" class="form-control input-md" required="" value="{{ $connection->hostname }}">
                    <span class="help-block">Enter your server address</span>
                </div>
            </div>

            @if(!empty($supportedConnectionMethods))
                <div class="form-group">
                    <label class="col-md-4 control-label" for="method">Authentication method</label>
                    <div class="col-md-4">
                        <select id="method" name="method" class="form-control" v-model="method">
                            @foreach($supportedConnectionMethods as $method => $description)
                                <option value="{{ $method }}" {{ $connection->method == $method ? 'selected' : '' }}>{{ $description }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            @endif

            <div class="form-group">
                <label class="col-md-4 control-label" for="username">Username</label>
                <div class="col-md-4">
                    <input id="username" name="username" type="text" placeholder="eg. admin" class="form-control input-md" required="" value="{{ $connection->username }}">
                    <span class="help-block">Enter the username to sign in</span>
                </div>
            </div>

            <div class="auth_password" v-if="method == 0">
                <div class="form-group">
                    <label class="col-md-4 control-label" for="password">Password</label>
                    <div class="col-md-4">
                        <input id="password" name="password" type="password" placeholder="* * * * * *" class="form-control input-md" @keyPress="passwordTyped"  required="" value="{{ $connection->password }}">
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
                    <input type="submit" class="btn btn-primary" value="Update connection">
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
                            text: "Your password will be encrypted with a symmetric key and stored in the database! This means that the hash will be possible to decrypt. Deployman will use this password to SSH to your server.\n\nWe !!!strongly!!! recommend using public key authentication.",
                            type: "info",
                            confirmButtonText: "I understand"
                        });
                    }
                }
            }
        });
    </script>
@endsection