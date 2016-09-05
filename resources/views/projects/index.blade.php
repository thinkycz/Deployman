@extends('layouts.app')

@section('content')
    <div class="pull-right">
        <a href="{{ action('ProjectsController@create') }}" class="btn btn-primary">Create a new project</a>
    </div>

    <table class="table">
        <thead>
        <tr>
            <th>#</th>
            <th>Name</th>
            <th>Type</th>
            <th>Repository</th>
            <th>Active revision</th>
        </tr>
        </thead>
        <tbody>
        @foreach($projects as $project)
            <tr>
                <th scope="row">{{ $project->id }}</th>
                <td>{{ $project->name }}</td>
                <td>{{ $project->type }}</td>
                <td>{{ $project->repository }}</td>
                <td>{{ $project->path }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endsection