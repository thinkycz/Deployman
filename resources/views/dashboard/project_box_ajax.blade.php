<div class="col-md-6">
    <div class="panel panel-{{ $deploy->deploy_complete ? 'success' : 'danger' }}">
        <div class="panel-heading"><a href="{{ action('ProjectsController@show', $project) }}"><strong>{{ $project->name }}</strong></a></div>
        <ul class="list-group">
            <li class="list-group-item">
                <div class="row">
                    <div class="col-md-4 text"><strong>On server:</strong></div>
                    <div class="col-md-8">{{ $project->connection->name }}</div>
                </div>
            </li>
            <li class="list-group-item">
                <div class="row">
                    <div class="col-md-4 text"><strong>Active release:</strong></div>
                    <div class="col-md-8">{{ $deploy->folder_name }}</div>
                </div>
            </li>
            <li class="list-group-item">
                <div class="row">
                    <div class="col-md-4 text"><strong>Last deploy:</strong></div>
                    <div class="col-md-8">{{ $deploy->created_at->diffForHumans() }}</div>
                </div>
            </li>
            <li class="list-group-item">
                <div class="row">
                    <div class="col-md-4 text"><strong>Total deploys:</strong></div>
                    <div class="col-md-8">
                            {{ $project->deploys->where('deploy_complete', true)->count() }} successful, {{ $project->deploys->where('deploy_complete', false)->count() }} failed
                    </div>
                </div>
            </li>
            <li class="list-group-item">
                <div class="row">
                    <div class="col-md-4 text"><strong>Current health:</strong></div>
                    <div class="col-md-8">{{ $deploy->deploy_complete ? 'Ok' : 'Errors' }}</div>
                </div>
            </li>
        </ul>
    </div>
</div>