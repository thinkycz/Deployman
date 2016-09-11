@extends('layouts.app')

@section('content')
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">List of connections</h3>
        </div>

        <div class="panel-body">
            <div class="pull-right">
                <a href="{{ action('ConnectionsController@create') }}" class="btn btn-primary">Create a new connection</a>
            </div>
        </div>

        <table class="table">
            <colgroup>
                <col style="width:5%">
                <col style="width:30%">
                <col style="width:20%">
                <col style="width:15%">
                <col style="width:10%">
                <col style="width:10%">
                <col style="width:10%">
            </colgroup>
            <thead>
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Hostname</th>
                <th>Authentication</th>
                <th>Created</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            @foreach($connections as $connection)
                <tr>
                    <th scope="row">{{ $connection->id }}</th>
                    <td>{{ $connection->name }}</td>
                    <td>{{ $connection->hostname }}</td>
                    <td>{{ $connection->method == 0 ? 'Password' : 'Public key'}}</td>
                    <td>{{ $connection->created_at->diffForHumans() }}</td>
                    <td>
                        <button class="btn btn-xs btn-primary status" data-status-id="{{ $connection->id }}")"><span class="glyphicon glyphicon-refresh glyphicon-refresh-animate"></span> Connecting ...</button>
                    </td>
                    <td>
                        <a href="{{ action('ConnectionsController@edit', $connection) }}" class="btn btn-warning btn-xs">Edit</a>
                        <a href="#" data-connection-id="{{ $connection->id }}" class="connection-delete btn btn-danger btn-xs">Delete</a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection

@section('scripts')
    <script>
        $('.connection-delete').click(function () {
            var btn = $(this);
            var id = $(this).attr('data-connection-id');
            var text = $(this).html();

            btn.html('<span class="glyphicon glyphicon-refresh glyphicon-refresh-animate"></span> Please wait ...');
            btn.attr('disabled', true);

            swal({
                title: "Are you sure?",
                text: "Do you really want to delete this connection?",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DE3D3E",
                confirmButtonText: "Yes, delete !",
                closeOnConfirm: false,
                showLoaderOnConfirm: true
            }, function (isConfirm) {
                if (isConfirm) {
                    $.ajax({
                        url: '/connections/' + id,
                        type: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    }).done(function (data) {
                        window.location.href = data;
                    }).fail(function (data) {
                        swal("Delete failed", "Deployman couldn't delete the conneciton.\n\nError: " + data.responseText, "error");
                    });
                }
                btn.html(text);
                btn.attr('disabled', false);
            });
        });

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
                btn.addClass('btn-success');
            }).fail(function () {
                btn.html('Connection failed');
                btn.addClass('btn-danger');
            }).always(function () {
                btn.removeClass('btn-primary');
                btn.attr('disabled', false);
            });
        });

        $(document).ready(function () {
            statusBtns.trigger('click');
        });
    </script>
@endsection