<nav class="navbar navbar-default navbar-static-top">
    <div class="container">
        <div class="navbar-header">

            <!-- Collapsed Hamburger -->
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#app-navbar-collapse">
                <span class="sr-only">Toggle Navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>

            <!-- Branding Image -->
            <a class="navbar-brand" href="{{ action('DashboardController@index') }}">
                <span class="glyphicon glyphicon-tasks"></span> <strong>{{ config('app.name', 'Laravel') }}</strong>
            </a>
        </div>

        <div class="collapse navbar-collapse" id="app-navbar-collapse">
            <!-- Left Side Of Navbar -->
            <ul class="nav navbar-nav">
                <li class="{{ strpos(Route::currentRouteAction(), 'DashboardController') ? 'active' : '' }}"><a href="{{ action('DashboardController@index') }}"><span class="glyphicon glyphicon-home"></span> Dashboard</a></li>
                <li class="{{ strpos(Route::currentRouteAction(), 'ConnectionsController') ? 'active' : '' }}"><a href="{{ action('ConnectionsController@index') }}"><span class="glyphicon glyphicon-transfer"></span> Connections</a></li>
                <li class="{{ strpos(Route::currentRouteAction(), 'ProjectsController') ? 'active' : '' }}"><a href="{{ action('ProjectsController@index') }}"><span class="glyphicon glyphicon-folder-open"></span> Projects</a></li>
                <li class="{{ strpos(Route::currentRouteAction(), 'DeploysController') ? 'active' : '' }}"><a href="{{ action('DeploysController@index') }}"><span class="glyphicon glyphicon-cloud-upload"></span> Deploys</a></li>
            </ul>

            <!-- Right Side Of Navbar -->
            <ul class="nav navbar-nav navbar-right">
                <!-- Authentication Links -->
                @if (Auth::guest())
                    <li><a href="{{ action('Auth\LoginController@login') }}">Login</a></li>
                    <li><a href="{{ action('Auth\RegisterController@register') }}">Register</a></li>
                @else
                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false"><span class="glyphicon glyphicon-user"></span> {{ Auth::user()->name }} <span class="caret"></span></a>

                        <ul class="dropdown-menu" role="menu">
                            <li><a>My profile</a></li>
                            <li role="separator" class="divider"></li>
                            <li>
                                <a href="{{ action('Auth\LoginController@logout') }}" onclick="event.preventDefault();document.getElementById('logout-form').submit();">Logout</a>
                                <form id="logout-form" action="{{ action('Auth\LoginController@logout') }}" method="POST" style="display: none;">{{ csrf_field() }}</form>
                            </li>
                        </ul>
                    </li>
                @endif
            </ul>
        </div>
    </div>
</nav>