@extends('layouts.app')

@section('content')
    <div class="row text-center">
        <div class="col-md-3">
            <div class="panel panel-primary">
                <div class="panel-heading"><strong>Connections</strong></div>
                <div class="panel-body"><h1>{{ $connectionsCount }}</h1></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="panel panel-info">
                <div class="panel-heading"><strong>Projects</strong></div>
                <div class="panel-body"><h1>{{ $projectsCount }}</h1></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="panel panel-success">
                <div class="panel-heading"><strong>Successful deploys</strong></div>
                <div class="panel-body"><h1>{{ $successfulDeploysCount }}</h1></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="panel panel-danger">
                <div class="panel-heading"><strong>Failed deploys</strong></div>
                <div class="panel-body"><h1>{{ $failedDeploysCount }}</h1></div>
            </div>
        </div>
    </div>
    @foreach($projects->chunk(2) as $row)
        <div class="row">
            @foreach($row as $project)
                <div class="project-box" data-project-id="{{ $project->id }}">
                    <div class="col-md-6">
                        <div class="panel panel-info">
                            <div class="panel-heading"><strong>{{ $project->name }}</strong></div>
                            <ul class="list-group">
                                <li class="list-group-item">
                                    <div class="row">
                                        <div class="col-md-4 text"><strong>On server:</strong></div>
                                        <div class="col-md-8">{{ $project->connection->name }}</div>
                                    </div>
                                </li>
                                <li class="list-group-item text-center loader"><span class="label label-primary" ><span class="glyphicon glyphicon-refresh glyphicon-refresh-animate"></span> Loading data from server, please wait ...</span></li>
                            </ul>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endforeach
@endsection

@section('scripts')
    <script>
        $(document).ready(function(){
            $(".project-box").each(function () {
                var box = $(this);
                var id = $(this).attr('data-project-id');
                console.log(box);

                $.ajax({
                    url: '/dashboard/' + id + '/projectbox'
                }).done(function (data) {
                    box.html(data);
                }).fail(function () {
                    box.find('.loader').html('<span class="label label-danger" ><span class="glyphicon glyphicon-alert"></span> Deployman could not load data from server.</span>');
                });
            });
        });
    </script>
@endsection