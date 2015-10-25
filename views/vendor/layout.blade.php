@extends('kiosk.layout')


@section('layout')
    <div class="container">

        <nav class="navbar navbar-inverse">
            <ul class="nav navbar-nav">
                <li><a href="{{{ URL::to("$route") }}}">View all {{ $plural }}</a></li>
                <li><a href="{{{ URL::to("$route/create") }}}">Create {{ $singular }}</a>
            </ul>
        </nav>

        @yield('content')

    </div>

@endsection
