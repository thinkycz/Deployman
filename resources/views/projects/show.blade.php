@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Project information</h3>
                </div>
                <ul class="list-group">
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-4 text"><strong>ID:</strong></div>
                            <div class="col-md-8">{{ $project->id }}</div>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-4 text"><strong>Name:</strong></div>
                            <div class="col-md-8">{{ $project->name }}</div>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-4 text"><strong>Project type:</strong></div>
                            <div class="col-md-8">{{ $project->type }}</div>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-4 text"><strong>Repository:</strong></div>
                            <div class="col-md-8">{{ $project->repository }}</div>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-4 text"><strong>Deploy to:</strong></div>
                            <div class="col-md-8">{{ $project->connection->name }}</div>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-4 text"><strong>Deploy path:</strong></div>
                            <div class="col-md-8">{{ $project->path }}</div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
        <div class="col-md-4 text-center">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Actions</h3>
                </div>
                <ul class="list-group">
                    <li class="list-group-item">
                        <button id="test-server" data-project-id="{{ $project->connection->id }}" class="btn btn-primary btn-xs">Test connection to the server</button>
                    </li>
                    <li class="list-group-item">
                        <button id="test-repo" data-project-id="{{ $project->id }}" class="btn btn-primary btn-xs">Test connection to repository</button>
                    </li>
                    <li class="list-group-item">
                        <button id="delete-project" data-project-id="{{ $project->id }}" class="btn btn-danger btn-xs"><span class="glyphicon glyphicon-trash"></span> Delete this project</button>
                    </li>
                    <li class="list-group-item">
                        <button id="deploy-now" data-project-id="{{ $project->id }}" class="btn btn-success btn-lg"><span class="glyphicon glyphicon-cloud-upload"></span> Deploy now !</button>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Recent deploys</h3>
                </div>

                <table class="table" id="deploys">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Revision</th>
                        <th>Deployed</th>
                        <th>Duration</th>
                        <th>Status</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($project->deploys as $deploy)
                        <tr>
                            <th scope="row">{{ $deploy->id }}</th>
                            <td><a href="{{ action('DeploysController@show', $deploy) }}">{{ substr($deploy->commit_hash, 0, 7) ?: 'unknown' }}</a></td>
                            <td>{{ $deploy->created_at->diffForHumans() }}</td>
                            <td>{{ $deploy->deploy_complete ? $deploy->created_at->diffInSeconds($deploy->completed_at) : '*' }} seconds</td>
                            <td>{{ $deploy->status }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="dialog" title="Basic dialog">
        {{-- Handled by javascript--}}
    </div>
@endsection

@section('scripts')
    <script>
        $('#test-server').click(function () {
            var btn = $(this);
            var id = $(this).attr('data-project-id');
            var text = $(this).html();

            btn.html('<span class="glyphicon glyphicon-refresh glyphicon-refresh-animate"></span> Please wait ...');
            btn.attr('disabled', true);

            $.ajax({
                url: '/connections/' + id + '/check'
            }).done(function () {
                swal("Connection successful", "Deployman has connected to your server.", "success");
            }).fail(function (data) {
                swal("Connection failed", "Deployman couldn't connect to your server.\nPlease check the connection settings and try again.\n\nError: " + data.responseText, "error");
            }).always(function () {
                btn.html(text);
                btn.attr('disabled', false);
            });
        });

        $('#test-repo').click(function () {
            var btn = $(this);
            var id = $(this).attr('data-project-id');
            var text = $(this).html();

            btn.html('<span class="glyphicon glyphicon-refresh glyphicon-refresh-animate"></span> Please wait ...');
            btn.attr('disabled', true);

            $.ajax({
                url: '/projects/' + id + '/check'
            }).done(function () {
                swal("Connection successful", "Your server has connected to the Git repository", "success");
            }).fail(function (data) {
                swal("Connection failed", "Your server couldn't connect to the Git repository.\nPlease check the repository settings and try again.\n\nError: " + data.responseText, "error");
            }).always(function () {
                btn.html(text);
                btn.attr('disabled', false);
            });
        });

        $('#deploy-now').click(function () {
            var btn = $(this);
            var id = $(this).attr('data-project-id');
            var text = $(this).html();
            var table = $('table#deploys').find('tr:last');
            var dialog = $("#dialog");

            btn.html('<span class="glyphicon glyphicon-refresh glyphicon-refresh-animate"></span> Please wait ...');
            btn.attr('disabled', true);

            $.ajax({
                url: '/projects/' + id + '/deploy'
            }).done(function (data) {
                table.after(
                        '<tr>' +
                        '<th>' + data.id + '</th>' +
                        '<td>' + '<a href="/deploys/' + data.id + '">unknown</a>' + '</td>' +
                        '<td>' + 'now' + '</td>' +
                        '<td>' + '* seconds' + '</td>' +
                        '<td>' + 'pending' + '</td>' +
                        '</tr>'
                );
                dialog.dialog('open');
            }).always(function () {
                btn.html(text);
                btn.attr('disabled', false);
            });
        });

        $(document).ready(function() {
            $( "#dialog" ).dialog({
                autoOpen: false,
                modal: true,
                minWidth: 800,
                minHeight: 600,
                title: "Deployment in progress"
            });
        });
    </script>
@endsection