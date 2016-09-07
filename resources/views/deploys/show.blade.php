@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Deploy information</h3>
                </div>
                <ul class="list-group">
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-4 text"><strong>ID:</strong></div>
                            <div class="col-md-8">{{ $deploy->id }}</div>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-4 text"><strong>Project:</strong></div>
                            <div class="col-md-8">{{ $deploy->project->name }}</div>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-4 text"><strong>Repository:</strong></div>
                            <div class="col-md-8">{{ $deploy->project->repository }}</div>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-4 text"><strong>Commit:</strong></div>
                            <div class="col-md-8">{{ $deploy->commit_hash ?: 'Hash not available' }}</div>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-4 text"><strong>Release folder:</strong></div>
                            <div class="col-md-8">{{ $deploy->folder_name ?: 'Folder not available' }}</div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
        <div class="col-md-4">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Deploy status</h3>
                </div>
                <li class="list-group-item">
                    <div class="row">
                        <div class="col-md-4 text"><strong>Deployed at:</strong></div>
                        <div class="col-md-8">{{ $deploy->created_at->format('j.n.Y G:i:s') }}</div>
                    </div>
                </li>
                <li class="list-group-item">
                    <div class="row">
                        <div class="col-md-4 text"><strong>Finished at:</strong></div>
                        <div class="col-md-8">{{ $deploy->completed_at ? $deploy->completed_at->format('j.n.Y G:i:s') : 'Not finished yet' }}</div>
                    </div>
                </li>
                <li class="list-group-item">
                    <div class="row">
                        <div class="col-md-4 text"><strong>Duration:</strong></div>
                        <div class="col-md-8">{{ $deploy->completed_at ? $deploy->created_at->diffInSeconds($deploy->completed_at) . 'seconds' : 'Not finished yet' }}</div>
                    </div>
                </li>
                <li class="list-group-item">
                    <div class="row">
                        <div class="col-md-4 text"><strong>Status:</strong></div>
                        <div class="col-md-8">{{ $deploy->status }}</div>
                    </div>
                </li>
                <li class="list-group-item">
                    <div class="row">
                        <div class="col-md-4 text"><strong>Result:</strong></div>
                        <div class="col-md-8"><span
                                    class="label label-{{ $deploy->deploy_complete ? 'success' : 'danger' }}">{{ $deploy->deploy_complete ? 'Successfully deployed' : 'Deploy script not completed' }}</span>
                        </div>
                    </div>
                </li>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default panel-{{ $deploy->deploy_complete ? 'success' : 'danger' }}">
                <div class="panel-heading">
                    <h3 class="panel-title">Terminal log</h3>
                </div>
                <table class="table">
                    @if($deploy->log)
                        @foreach(unserialize($deploy->log) as $line)
                            @if($line)
                                <tr>
                                    @if(strpos($line, 'INFO') !== false)
                                        <td class="text-primary"><strong>{{ $line }}</strong></td>
                                    @elseif(strpos($line, 'COMMAND') !== false)
                                        <td class="text-success"><strong>{{ $line }}</strong></td>
                                    @elseif(strpos($line, 'ERROR') !== false)
                                        <td class="text-danger"><strong>{{ $line }}</strong></td>
                                    @else
                                        <td>{{ $line }}</td>
                                    @endif
                                </tr>
                            @endif
                        @endforeach
                        @else
                        <tr>
                            <td>No log has been created yet.</td>
                        </tr>
                    @endif
                </table>
            </div>
        </div>
    </div>
@endsection