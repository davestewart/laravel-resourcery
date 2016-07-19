@extends($views->layout)

@section('head')
    <title>{{ $lang->title->index }}</title>
@stop

@section('content')

        <header>
            <h1>{{ $lang->title->index }}</h1>
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
            <a class="btn btn-small btn-info btn-create" href="{!! URL::to("$route/create") !!}">{{ $lang->prompt->create }}</a>

            <!-- top pagination -->
            @include($views->pagination)

        </div>

        <!-- table -->
        @include($views->list)

        <!-- bottom pagination -->
        @include($views->pagination)

        <!-- on delete handler -->
        <script>
            function onDelete(event, form)
            {
                if (! confirm('{!! $lang->confirm->delete !!}') )
                {
                    event.preventDefault();
                }
            }
        </script>

@stop
