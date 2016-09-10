@extends('layouts.app')

@section('content')
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">List of projects</h3>
        </div>

        <div class="panel-body">
            <div class="pull-right">
                <a href="{{ action('ProjectsController@create') }}" class="btn btn-primary">Create a new project</a>
            </div>
        </div>

        <table class="table">
            <thead>
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Type</th>
                <th>Repository</th>
                <th>Path</th>
                <th>Active revision</th>
            </tr>
            </thead>
            <tbody>
            @foreach($projects as $project)
                <tr>
                    <th scope="row">{{ $project->id }}</th>
                    <td><a href="{{ action('ProjectsController@show', $project) }}">{{ $project->name }}</a></td>
                    <td>{{ ucfirst($project->type) }}</td>
                    <td>{{ $project->repository }}</td>
                    <td>{{ $project->path }}</td>
                    <td class="active-revision" data-project-id="{{ $project->id }}"><span
                                class="label label-primary"><span
                                    class="glyphicon glyphicon-refresh glyphicon-refresh-animate"></span> Connecting ...</span>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function () {
            $('.active-revision').each(function () {
                var field = $(this);
                var id = $(this).attr('data-project-id');

                $.ajax({
                    url: '/projects/' + id + '/getCurrentDeploy'
                }).done(function (data) {
                    if (data.deploy) {
                        if (data.deploy.deploy_complete) {
                            field.html('<span class="label label-success">' + data.deploy.folder_name + '</span>');
                        } else {
                            field.html('<span class="label label-danger">' + data.deploy.folder_name + '</span>');
                        }
                    } else {
                        field.html('<span class="label label-info">Not available</span>');
                    }

                }).fail(function () {
                    field.html('<span class="label label-danger"><span class="glyphicon glyphicon-ban-circle"></span> Connection error</span>');
                });
            })
        });
    </script>
@endsection