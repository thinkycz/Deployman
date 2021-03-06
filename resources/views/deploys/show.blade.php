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
                            <div class="col-md-8">{{ $deploy->project ? $deploy->project->name : 'Project deleted' }}</div>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-4 text"><strong>Repository:</strong></div>
                            <div class="col-md-8">{{ $deploy->project ? $deploy->project->repository : 'Project deleted' }}</div>
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
                        <div class="col-md-8">{{ $deploy->finished_at ? $deploy->finished_at->format('j.n.Y G:i:s') : 'Not finished yet' }}</div>
                    </div>
                </li>
                <li class="list-group-item">
                    <div class="row">
                        <div class="col-md-4 text"><strong>Duration:</strong></div>
                        <div class="col-md-8">{{ $deploy->finished_at ? $deploy->created_at->diffInSeconds($deploy->finished_at) . ' seconds' : 'Not finished yet' }}</div>
                    </div>
                </li>
                <li class="list-group-item">
                    <div class="row">
                        <div class="col-md-4 text"><strong>Status:</strong></div>
                        <div class="col-md-8">
                            <span class="label label-{{ $deploy->status == 'pending' ? 'info' : ($deploy->status == 'running' ? 'warning' : ($deploy->status == 'finished' ? 'success' : 'danger')) }}">
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
                        </div>
                    </div>
                </li>
                <li class="list-group-item">
                    <div class="row">
                        <div class="col-md-4 text"><strong>Result:</strong></div>
                        <div class="col-md-8"><span class="label label-{{ $deploy->deploy_complete ? 'success' : 'danger' }}">{{ $deploy->deploy_complete ? 'Successfully deployed' : 'Deploy script not completed' }}</span></div>
                    </div>
                </li>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Run post-deploy commands</h3>
                </div>
                <div class="panel-body">
                    <br>
                    @foreach(array_chunk($methods, 4) as $row)
                        <div class="row">
                            @foreach($row as $method)
                                <div class="col-md-3">
                                    <form class="ajaxForm" method="post" action="{{ action('DeploysController@postDeployCommand', $deploy) }}">
                                        <input type="hidden" name="method_name" value="{{ $method['method']->name }}">
                                        <input type="hidden" name="method_class" value="{{ $method['method']->class }}">
                                        <input type="submit" class="btn btn-default form-control" value="{{ $method['description'] }}">
                                    </form>
                                </div>
                            @endforeach
                        </div>
                        <br>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @include('partials.terminal_log')
@endsection

@section('scripts')
    <script>
        $('form.ajaxForm').submit(function (event) {
            event.preventDefault();
            var form = $(this);

            swal({
                title: "Are you sure?",
                text: "Do you really want to run this command?",
                type: "info",
                showCancelButton: true,
                confirmButtonColor: "#336699",
                confirmButtonText: "Run this command",
                closeOnConfirm: false,
                showLoaderOnConfirm: true
            }, function (isConfirm) {
                if (isConfirm) {
                    $.ajax({
                        url: form.attr('action'),
                        type: form.attr('method'),
                        data: form.serialize(),
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    }).done(function() {
                        swal("Success!", "Please check the result in the log.", "success");
                    }).fail(function() {
                        swal("Failed!", "Please check the result in the log.", "error");
                    }).always(function () {
                        setTimeout(function () {
                            location.reload();
                        }, 3000);
                    });
                }
            });
        });
    </script>
@endsection