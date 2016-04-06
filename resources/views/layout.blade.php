@extends('admin.layout')


@section('content')
    <div class="container">

        <nav class="navbar navbar-inverse">
            <ul class="nav navbar-nav">
                <li><a href="{!! URL::to("$route") !!}"> {{ $lang->prompt->all }}</a></li>
                <li><a href="{!! URL::to("$route/create") !!}">{{ $lang->prompt->create }}</a>
            </ul>
        </nav>

        @yield('content')

    </div>

@endsection
