@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <div class="row">
                        <div class="col-md-6">
                            <h3 class="panel-title">Project information</h3>
                        </div>
                        <div class="col-md-6 text-right">
                            <a href="{{ action('ProjectsController@edit', $project) }}" class="btn btn-xs btn-warning">Edit this project</a>
                        </div>
                    </div>
                </div>
                <ul class="list-group">
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-4 text"><strong>ID:</strong></div>
                            <div class="col-md-8">{{ $project->id }}</div>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-4 text"><strong>Name:</strong></div>
                            <div class="col-md-8">{{ $project->name }}</div>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-4 text"><strong>Project type:</strong></div>
                            <div class="col-md-8">{{ ucfirst($project->type) }}</div>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-4 text"><strong>Repository:</strong></div>
                            <div class="col-md-8">{{ $project->repository }}</div>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-4 text"><strong>Deploy to:</strong></div>
                            <div class="col-md-8">{{ $project->connection->name }}</div>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-4 text"><strong>Deploy path:</strong></div>
                            <div class="col-md-8">{{ $project->path }}</div>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-4 text"><strong>Active release:</strong></div>
                            <div class="col-md-8 active-release"><span class="label label-primary"><span
                                            class="glyphicon glyphicon-refresh glyphicon-refresh-animate"></span> Connecting ...</span>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
        <div class="col-md-4 text-center">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Actions</h3>
                </div>
                <ul class="list-group">
                    <li class="list-group-item">
                        <button id="test-server" data-project-id="{{ $project->connection->id }}"
                                class="btn btn-primary btn-xs">Test connection to server
                        </button>
                        <button id="test-repo" data-project-id="{{ $project->id }}" class="btn btn-primary btn-xs">Test
                            connection to Git
                        </button>
                    </li>
                    <li class="list-group-item">
                        <button id="cleanup-project" data-project-id="{{ $project->id }}"
                                class="btn btn-warning btn-xs"><span class="glyphicon glyphicon-leaf"></span> Cleanup
                            old releases
                        </button>
                        <button id="delete-project" data-project-id="{{ $project->id }}" class="btn btn-danger btn-xs">
                            <span class="glyphicon glyphicon-trash"></span> Delete this project
                        </button>
                    </li>
                    <li class="list-group-item">
                        <button id="rollback-project" data-project-id="{{ $project->id }}" class="btn btn-info btn-xs">
                            <span class="glyphicon glyphicon-fast-backward"></span> Roll back to previous release and
                            delete latest
                        </button>
                    </li>
                    <li class="list-group-item">
                        <form class="form-horizontal" id="deploy-form">
                            <div class="form-group">
                                <label class="col-md-3 control-label" for="branch">Branch</label>
                                <div class="col-md-9">
                                    <input id="branch" name="branch" type="text" placeholder="eg. develop"
                                           class="form-control input-md">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-3 control-label" for="commit">Commit</label>
                                <div class="col-md-9">
                                    <input id="commit" name="commit" type="text"
                                           placeholder="eg. b2599ac7998893cf62cf4a6f625dd92c770c2484"
                                           class="form-control input-md">
                                </div>
                            </div>
                            <button id="deploy-now" data-project-id="{{ $project->id }}" class="btn btn-success">
                                <span class="glyphicon glyphicon-cloud-upload"></span> Deploy latest commit from master
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <div class="alert alert-danger connection-failed" role="alert" style="display: none"><strong>Warning!!!</strong>
        Deployman could not connect to the server and load this project. Possible reasons: 1. The server may be down or unreachable. 2. Project is not initialized.
    </div>

    <div class="alert alert-danger unsuccessful-deploy" role="alert" style="display:none;"><strong>Warning!!!</strong>
        The active release currently points to an <strong>unsuccessful deploy</strong> - the deploy script was
        interrupted and failed. The website may be unstable or not working at all.
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Recent deploys</h3>
                </div>

                <table class="table" id="deploys">
                    <tr>
                        <th>#</th>
                        <th>Revision</th>
                        <th>Directory</th>
                        <th>Deployed</th>
                        <th>Duration</th>
                        <th>Status</th>
                        <th>On server</th>
                    </tr>
                    @foreach($project->deploys()->latest()->get() as $deploy)
                        <tr class="deploy-row">
                            <th scope="row">{{ $deploy->id }}</th>
                            <td>
                                <a href="{{ action('DeploysController@show', $deploy) }}">{{ substr($deploy->commit_hash, 0, 7) ?: 'unknown' }}</a>
                            </td>
                            <td class="folder-name">{{ $deploy->folder_name }}</td>
                            <td>{{ $deploy->created_at->diffForHumans() }}</td>
                            <td>{{ $deploy->deploy_complete ? $deploy->created_at->diffInSeconds($deploy->finished_at) : '*' }}
                                seconds
                            </td>
                            <td>
                                <button class="showStatusWindow btn btn-xs btn-{{ $deploy->status == 'pending' ? 'info' : ($deploy->status == 'running' ? 'warning' : ($deploy->status == 'finished' ? 'success' : 'danger')) }}"
                                        data-deploy-id="{{ $deploy->id }}">
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
                                </button>
                            </td>
                            <td class="on-server"><span class="label label-primary"><span
                                            class="glyphicon glyphicon-refresh glyphicon-refresh-animate"></span> Connecting ...</span>
                            </td>
                        </tr>
                    @endforeach
                </table>
            </div>
        </div>
    </div>

    <div id="dialog" title="Basic dialog" style="display: none">
        <div class="row">
            <div class="col-md-12">
                <div class="alert alert-info text-center" role="alert">
                    <h3><span class="glyphicon glyphicon glyphicon-hourglass"></span> Please wait</h3>
                    <p>Connecting to the server and getting logs.</p>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function () {
            $("#dialog").dialog({
                autoOpen: false,
                modal: true,
                minWidth: 800,
                height: 600,
                title: "Deployment status"
            });

            var id = $("#deploy-now").attr('data-project-id');
            var field = $(this).find(".on-server");

            $.ajax({
                url: '/projects/' + id + '/getCurrentDeploy'
            }).done(function (data) {
                if (data.deploy) {
                    if (data.deploy.deploy_complete == false) {
                        $('.unsuccessful-deploy').show();
                    }
                    $('.active-release').html(data.deploy.folder_name);
                } else {
                    $('.active-release').html('<span class="label label-danger"><span class="glyphicon glyphicon-ban-circle"></span> Not available</span>');
                }

                $(".deploy-row").each(function () {
                    field = $(this).find(".on-server");
                    var folder = $(this).find(".folder-name").html();

                    if (data.deploy && folder == data.deploy.folder_name) {
                        $(this).addClass('success');
                    }

                    if ($.inArray(folder, data.onServer) != -1) {
                        field.html('<span class="label label-success"><span class="glyphicon glyphicon-search"></span> Release found</span>');
                    } else {
                        field.html('<span class="label label-danger"><span class="glyphicon glyphicon-remove-circle"></span> Not found</span>');
                    }
                })
            }).fail(function () {
                field.html('<span class="label label-danger"><span class="glyphicon glyphicon-ban-circle"></span> Not available</span>');
                $('.active-release').html('<span class="label label-danger"><span class="glyphicon glyphicon-ban-circle"></span> Not available</span>');
                $('.connection-failed').show();
            });
        });

        $('#branch').on('input', function () {
            var button = $('#deploy-now');
            var commitBtn = $('#commit');
            var commit = commitBtn.val() ? commitBtn.val().substring(0, 7) : 'latest commit';
            var branch = $(this).val() ? $(this).val() : 'master';

            button.html('<span class="glyphicon glyphicon-cloud-upload"></span> Deploy ' + commit + ' from ' + branch);
        });

        $('#commit').on('input', function () {
            var button = $('#deploy-now');
            var branchBtn = $('#branch');
            var commit = $(this).val() ? $(this).val().substring(0, 7) : 'latest commit';
            var branch = branchBtn.val() ? branchBtn.val() : 'master';

            button.html('<span class="glyphicon glyphicon-cloud-upload"></span> Deploy ' + commit + ' from ' + branch);
        });

        $('#delete-project').click(function () {
            var btn = $(this);
            var id = $(this).attr('data-project-id');
            var text = $(this).html();

            btn.html('<span class="glyphicon glyphicon-refresh glyphicon-refresh-animate"></span> Please wait ...');
            btn.attr('disabled', true);

            swal({
                title: "Are you sure?",
                text: "Do you really want to delete this project?",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DE3D3E",
                confirmButtonText: "Yes, delete !",
                closeOnConfirm: false,
                showLoaderOnConfirm: true
            }, function (isConfirm) {
                if (isConfirm) {
                    $.ajax({
                        url: '/projects/' + id,
                        type: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    }).done(function (data) {
                        window.location.href = data;
                    }).fail(function (data) {
                        swal("Delete failed", "Deployman couldn't delete the project.\n\nError: " + data.responseText, "error");
                    });
                }
                btn.html(text);
                btn.attr('disabled', false);
            });
        });

        $('#rollback-project').click(function () {
            var btn = $(this);
            var id = $(this).attr('data-project-id');
            var text = $(this).html();

            btn.html('<span class="glyphicon glyphicon-refresh glyphicon-refresh-animate"></span> Please wait ...');
            btn.attr('disabled', true);

            swal({
                title: "Are you sure?",
                text: "This will remove the active release deploy and switch to the previous one, proceed?",
                type: "info",
                showCancelButton: true,
                confirmButtonColor: "#23B8D8",
                confirmButtonText: "Yes, roll it back!",
                closeOnConfirm: false,
                showLoaderOnConfirm: true
            }, function (isConfirm) {
                if (isConfirm) {
                    $.ajax({
                        url: '/projects/' + id + '/rollback'
                    }).done(function () {
                        swal("Rollback successful", "Deployman has activated the previous commit.", "success");
                        location.reload();
                    }).fail(function (data) {
                        swal("Rollback failed", "Deployman couldn't finish the rollback.\n\nError: " + data.responseText, "error");
                    });
                }
                btn.html(text);
                btn.attr('disabled', false);
            });
        });

        $('#test-server').click(function () {
            var btn = $(this);
            var id = $(this).attr('data-project-id');
            var text = $(this).html();

            btn.html('<span class="glyphicon glyphicon-refresh glyphicon-refresh-animate"></span> Please wait ...');
            btn.attr('disabled', true);

            $.ajax({
                url: '/connections/' + id + '/check'
            }).done(function () {
                swal("Connection successful", "Deployman has connected to your server.", "success");
            }).fail(function (data) {
                swal("Connection failed", "Deployman couldn't connect to your server.\nPlease check the connection settings and try again.\n\nError: " + data.responseText, "error");
            }).always(function () {
                btn.html(text);
                btn.attr('disabled', false);
            });
        });

        $('#test-repo').click(function () {
            var btn = $(this);
            var id = $(this).attr('data-project-id');
            var text = $(this).html();

            btn.html('<span class="glyphicon glyphicon-refresh glyphicon-refresh-animate"></span> Please wait ...');
            btn.attr('disabled', true);

            $.ajax({
                url: '/projects/' + id + '/check'
            }).done(function () {
                swal("Connection successful", "Your server has connected to the Git repository", "success");
            }).fail(function (data) {
                swal("Connection failed", "Your server couldn't connect to the Git repository.\nPlease check the repository settings and try again.\n\nError: " + data.responseText, "error");
            }).always(function () {
                btn.html(text);
                btn.attr('disabled', false);
            });
        });

        $('#cleanup-project').click(function () {
            var btn = $(this);
            var id = $(this).attr('data-project-id');
            var text = $(this).html();

            btn.html('<span class="glyphicon glyphicon-refresh glyphicon-refresh-animate"></span> Please wait ...');
            btn.attr('disabled', true);

            swal({
                title: "Are you sure?",
                text: "This will remove all deploys except latest 3, proceed?",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#F4A146",
                confirmButtonText: "Yes, clean it!",
                closeOnConfirm: false,
                showLoaderOnConfirm: true
            }, function (isConfirm) {
                if (isConfirm) {
                    $.ajax({
                        url: '/projects/' + id + '/cleanup'
                    }).done(function () {
                        swal("Cleanup successful", "Deployman has removed your old deploys.", "success");
                        location.reload();
                    }).fail(function (data) {
                        swal("Cleanup failed", "Deployman couldn't finish the cleanup.\n\nError: " + data.responseText, "error");
                    });
                }
                btn.html(text);
                btn.attr('disabled', false);
            });
        });

        $('table.table').on('click', '.showStatusWindow', function () {
            var deploy = $(this).attr('data-deploy-id');
            var dialog = $("#dialog");
            var dialogDefaultText = dialog.html();
            var finished = false;

            dialog.dialog('open');

            var startDeployment = function (deploy) {
                $.ajax({
                    url: '/deploys/' + deploy + '/fire'
                });
            };

            var updateStatus = function (deploy) {
                $.ajax({
                    url: '/deploys/' + deploy + '/status'
                }).done(function (data) {
                    dialog.html(data.html);
                    if (data.deploy.status == 'finished' || data.deploy.status == 'failed') {
                        finished = true;
                    } else if (data.deploy.status == 'pending') {
                        startDeployment(deploy);
                    }
                });
            };

            var loop = setInterval(function () {
                updateStatus(deploy);
                if (finished) clearInterval(loop);
                dialog.on("dialogclose", function () {
                    location.reload();
                    clearInterval(loop);
                    dialog.html(dialogDefaultText);
                });
            }, 2000);
        });

        $('#deploy-now').click(function () {
            var btn = $(this);
            var project = $(this).attr('data-project-id');
            var text = $(this).html();
            var table = $('table#deploys').find('tr:first');
            var dialog = $("#dialog");
            var finished = false;
            var dialogDefaultText = dialog.html();

            btn.html('<span class="glyphicon glyphicon-refresh glyphicon-refresh-animate"></span> Please wait ...');
            btn.attr('disabled', true);

            swal({
                title: "Are you sure?",
                text: "This will deploy the latest commit from the master branch.",
                type: "info",
                showCancelButton: true,
                confirmButtonColor: "#46B864",
                confirmButtonText: "Yes, deploy it!",
                closeOnConfirm: true
            }, function (isConfirm) {
                if (isConfirm) {
                    deployProject();
                } else {
                    btn.html(text);
                    btn.attr('disabled', false);
                }
            });

            var startDeployment = function (deploy) {
                $.ajax({
                    url: '/deploys/' + deploy + '/fire'
                });
            };

            var updateStatus = function (deploy) {
                $.ajax({
                    url: '/deploys/' + deploy + '/status'
                }).done(function (data) {
                    dialog.html(data.html);
                    if (data.deploy.status == 'finished' || data.deploy.status == 'failed') {
                        finished = true;
                    }
                });
            };

            var deployProject = function () {
                $.ajax({
                    url: '/projects/' + project + '/deploy',
                    data: $("#deploy-form").serialize()
                }).done(function (data) {
                    table.after(
                            '<tr>' +
                            '<th>' + data.id + '</th>' +
                            '<td><a href="/deploys/' + data.id + '">unknown</a></td>' +
                            '<td>unknown</td>' +
                            '<td>now</td>' +
                            '<td>* seconds</td>' +
                            '<td><button class="showStatusWindow btn btn-xs btn-info" data-deploy-id="' + data.id + '"><span class="glyphicon glyphicon glyphicon-hourglass"></span> Pending</button></td>' +
                            '<td><span class="label label-info"><span class="glyphicon glyphicon-cog"></span> Creating</span></td>' +
                            '</tr>'
                    );
                    dialog.dialog('open');
                    startDeployment(data.id);
                    var loop = setInterval(function () {
                        updateStatus(data.id);
                        if (finished) clearInterval(loop);
                        dialog.on("dialogclose", function () {
                            location.reload();
                            clearInterval(loop);
                            dialog.html(dialogDefaultText);
                        });
                    }, 2000);
                }).always(function () {
                    btn.html(text);
                    btn.attr('disabled', false);
                });
            };
        });
    </script>
@endsection