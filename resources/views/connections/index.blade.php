@extends('layouts.app')

@section('content')
    <div class="pull-right">
        <a href="{{ action('ConnectionsController@create') }}" class="btn btn-primary">Create a new connection</a>
    </div>

    <table class="table">
        <thead>
        <tr>
            <th>#</th>
            <th>Name</th>
            <th>Hostname</th>
            <th>Authentication</th>
            <th>Created at</th>
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
            </tr>
        @endforeach
        </tbody>
    </table>
@endsection