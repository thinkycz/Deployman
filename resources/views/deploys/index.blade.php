@extends('layouts.app')

@section('content')
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
        @foreach($deploys as $deploy)
            <tr>
                <th scope="row">{{ $deploy->id }}</th>
                <td><a href="">{{ $deploy->name }}</a></td>
                <td>{{ $deploy->type }}</td>
                <td>{{ $deploy->repository }}</td>
                <td>{{ $deploy->path }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endsection