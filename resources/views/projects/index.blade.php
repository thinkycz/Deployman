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
                    <td>
                        @if($active)
                            <span class="label label-{{ $active[$project->id]->deploy_complete ? 'success' : 'danger' }}">{{ $active[$project->id]->folder_name }}</span>
                            @else
                            <span class="label label-danger">Connection error</span>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection