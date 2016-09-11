@if(!isset($ajax))
    {!! $ajax = false !!}
@endif

@if($ajax)
    <div class="row">
        <div class="col-md-12">
            @if($queue)
                <div class="alert alert-warning text-center" role="alert">
                    <h3><span class="glyphicon glyphicon glyphicon-hourglass"></span> Your deploy has been queued</h3>
                    <p>You can close this window and check the status later in the deploys section.</p>
                </div>
            @elseif($deploy->status == 'running')
                <div class="alert alert-warning text-center" role="alert">
                    <h3><span class="glyphicon glyphicon-refresh glyphicon-refresh-animate"></span> Deployment in progress</h3>
                    <p>You can close this window and check the status later in the deploys section.</p>
                </div>
            @elseif($deploy->status == 'finished')
                <div class="alert alert-success text-center" role="alert">
                    <h3><span class="glyphicon glyphicon-ok"></span> Deployment successful</h3>
                    <p>You can close this window and check full terminal log in the deploys section.</p>
                </div>
            @elseif($deploy->status == 'failed')
                <div class="alert alert-danger text-center" role="alert">
                    <h3><span class="glyphicon glyphicon glyphicon-remove"></span> Deployment failed</h3>
                    <p>You can close this window and check full terminal log in the deploys section.</p>
                </div>
            @else
                <div class="alert alert-info text-center" role="alert">
                    <h3><span class="glyphicon glyphicon glyphicon-hourglass"></span> Please wait</h3>
                    <p>Connecting to the server and getting logs.</p>
                </div>
            @endif
        </div>
    </div>
@endif

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">Terminal log</h3>
            </div>
            <table class="table">
                @if($deploy->log)
                    @foreach(unserialize($deploy->log) as $line)
                        @if(trim($line))
                            <tr>
                                @if(strpos($line, 'INFO') !== false)
                                    <td class="text-primary"><strong>{{ $line }}</strong></td>
                                @elseif(strpos($line, 'COMMAND') !== false or strpos($line, 'SUCCESS') !== false)
                                    <td class="text-success"><strong>{{ $line }}</strong></td>
                                @elseif(strpos($line, 'ERROR') !== false)
                                    <td class="text-danger"><strong>{{ $line }}</strong></td>
                                @else
                                    @if(!$ajax)
                                        <td>{{ $line }}</td>
                                    @endif
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