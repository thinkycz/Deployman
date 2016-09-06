@extends('layouts.app')

@section('content')
    <table class="table">
        <thead>
        <tr>
            <th>#</th>
            <th>Project</th>
            <th>Deployed</th>
            <th>Duration</th>
            <th>Status</th>
        </tr>
        </thead>
        <tbody>
        @foreach($deploys as $deploy)
            <tr>
                <th scope="row">{{ $deploy->id }}</th>
                <td><a href="">{{ $deploy->project->name }} (rev. {{ substr($deploy->commit_hash, 0, 7) }})</a></td>
                <td>{{ \Carbon\Carbon::parse($deploy->deployed_at, 'Europe/Prague')->diffForHumans() }}</td>
                <td>{{ \Carbon\Carbon::parse($deploy->deployed_at)->diffInSeconds($deploy->created_at) }} seconds</td>
                <td><span class="label label-{{ $deploy->deploy_complete ? 'success' : 'danger' }}">{{ $deploy->deploy_complete ? 'Successful' : 'Failed' }}</span></td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endsection