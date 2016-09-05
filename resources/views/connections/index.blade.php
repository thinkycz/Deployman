@extends('layouts.app')

@section('content')
    <div id="connection_list">
        <div class="pull-right">
            <a href="{{ action('ConnectionsController@create') }}" class="btn btn-primary">Create a new connection</a>
        </div>

        <table class="table">
            <colgroup>
                <col style="width:5%">
                <col style="width:30%">
                <col style="width:20%">
                <col style="width:15%">
                <col style="width:15%">
                <col style="width:15%">
            </colgroup>
            <thead>
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Hostname</th>
                <th>Authentication</th>
                <th>Created</th>
                <th>Status</th>
            </tr>
            </thead>
            <tbody>
            @foreach($connections as $connection)
                <tr>
                    <th scope="row">{{ $connection->id }}</th>
                    <td>{{ $connection->name }}</td>
                    <td>{{ $connection->hostname }}</td>
                    <td>{{ $connection->method == 0 ? 'Password' : 'Private key'}}</td>
                    <td>{{ $connection->created_at->diffForHumans() }}</td>
                    <td>
                        <button class="btn btn-xs btn-primary status" data-status-id="{{ $connection->id }}"
                                v-on:click="checkConnection({{ $connection->id }})"><span
                                    class="glyphicon glyphicon-refresh glyphicon-refresh-animate"></span> Connecting ...
                        </button>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection

@section('scripts')
    <script>
        var statusBtns = $('.status');

        statusBtns.click(function () {
            var id = $(this).attr('data-status-id');
            var btn = $(this);
            btn.html('<span class="glyphicon glyphicon-refresh glyphicon-refresh-animate"></span> Connecting ...');
            btn.removeClass('btn-danger');
            btn.removeClass('btn-success');
            btn.addClass('btn-primary');
            btn.attr('disabled', true);

            $.ajax({
                url: '/connections/' + id + '/check'
            }).done(function () {
                btn.html('Connection successful');
                btn.removeClass('btn-primary');
                btn.addClass('btn-success');
                btn.attr('disabled', false);
            }).fail(function () {
                btn.html('Connection failed');
                btn.removeClass('btn-primary');
                btn.addClass('btn-danger');
                btn.attr('disabled', false);
            });
        });

        statusBtns.trigger('click');
    </script>
@endsection