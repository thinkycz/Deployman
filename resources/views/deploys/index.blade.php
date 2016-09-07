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
                <td><a href="{{ action('DeploysController@show', $deploy) }}">{{ $deploy->project->name }} (rev. {{ substr($deploy->commit_hash, 0, 7) ?: 'unknown' }})</a></td>
                <td>{{ $deploy->created_at->diffForHumans() }}</td>
                <td>{{ $deploy->deploy_complete ? $deploy->created_at->diffInSeconds($deploy->finished_at) : '*' }} seconds</td>
                <td><span class="label label-{{ $deploy->status == 'pending' ? 'info' : ($deploy->status == 'running' ? 'warning' : ($deploy->status == 'finished' ? 'success' : 'danger')) }}">{{ ucfirst($deploy->status) }}</span></td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endsection