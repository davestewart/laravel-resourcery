@extends($views->layout)

@section('head')
    <title>All {{ $plural }}</title>
@stop

@section('content')

    <nav class="navbar navbar">
        <ul class="nav navbar-nav">
            <li><a href="{!! URL::to("$route") !!}">View all {{ $plural }}</a></li>
            <li><a href="{!! URL::to("$route/create") !!}">Create {{ $singular }}</a>
        </ul>
    </nav>


    <header>
            <h1>All {{ $plural }}</h1>
        </header>

        <!-- errors
        @if( Session::has('message') )
            <div class="alert alert-{{ Session::get('status') }}">
               This is a status message {!! Session::get('message') !!}
            </div>
        @endif
        -->

        <!-- controls -->
        <div class="form-group crud-controls">

            <!-- create new -->
            <a class="btn btn-small btn-info btn-create" href="{!! URL::to("$route/create") !!}">Create new {{ $singular }}</a>

            <!-- top pagination -->
            @include($views->pagination)

        </div>

        <!-- table -->
        @include($views->list)

        <!-- bottom pagination -->
        @include($views->pagination)

@stop
