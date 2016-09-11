@extends('layouts.app')

@section('content')
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">List of deploys</h3>
        </div>

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
                    <td><a href="{{ action('DeploysController@show', $deploy) }}">{{ $deploy->project ? $deploy->project->name : 'Project deleted' }}
                            (rev. {{ substr($deploy->commit_hash, 0, 7) ?: 'unknown' }})</a></td>
                    <td>{{ $deploy->created_at->diffForHumans() }}</td>
                    <td>{{ $deploy->deploy_complete ? $deploy->created_at->diffInSeconds($deploy->finished_at) : '*' }}
                        seconds
                    </td>
                    <td><span class="label label-{{ $deploy->status == 'pending' ? 'info' : ($deploy->status == 'running' ? 'warning' : ($deploy->status == 'finished' ? 'success' : 'danger')) }}">
                        @if($deploy->status == 'running')
                                <span class="glyphicon glyphicon-refresh glyphicon-refresh-animate"></span>
                            @elseif($deploy->status == 'pending')
                                <span class="glyphicon glyphicon glyphicon-hourglass"></span>
                            @elseif($deploy->status == 'finished')
                                <span class="glyphicon glyphicon-ok"></span>
                            @elseif($deploy->status == 'failed')
                                <span class="glyphicon glyphicon glyphicon-remove"></span>
                            @endif
                            {{ ucfirst($deploy->status) }}
                    </span>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection