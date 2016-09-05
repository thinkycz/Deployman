@extends('layouts.app')

@section('content')
    <form class="form-horizontal" method="post" action="{{ action('ProjectsController@store') }}">
        {{ csrf_field() }}
        <fieldset>
            <legend>Create a new project</legend>

            <div class="form-group">
                <label class="col-md-4 control-label" for="project-name">Project name</label>
                <div class="col-md-4">
                    <input id="project-name" name="project-name" type="text" placeholder="eg. Deployman" class="form-control input-md" required="">
                </div>
            </div>

            @if(!empty($supportedProjectTypes))
                <div class="form-group">
                    <label class="col-md-4 control-label" for="project-type">Project type</label>
                    <div class="col-md-4">
                        <select id="project-type" name="project-type" class="form-control">
                            @foreach($supportedProjectTypes as $type => $description)
                                <option value="{{ $type }}">{{ $description }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            @endif

            <div class="form-group">
                <label class="col-md-4 control-label" for="repository">Git repository</label>
                <div class="col-md-4">
                    <input id="repository" name="repository" type="text" placeholder="eg. git@github.com:vendor/project.git" class="form-control input-md" required="">
                    <span class="help-block">Enter your git repository address</span>
                </div>
            </div>

            <div class="form-group">
                <label class="col-md-4 control-label" for="path">Deploy path</label>
                <div class="col-md-4">
                    <input id="path" name="path" type="text" placeholder="eg. /var/www/project" class="form-control input-md" required="">
                    <span class="help-block">Enter the absolute path on server to where you want this project to be deployed</span>
                </div>
            </div>

            <div class="form-group">
                <div class="col-md-4 col-md-offset-4">
                    <input type="submit" class="btn btn-primary" value="Create this project">
                </div>
            </div>

        </fieldset>
    </form>

@endsection