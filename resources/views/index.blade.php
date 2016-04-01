@extends('resourcery::layout')

@section('head')
    <title>All {{ $plural }}</title>
@stop

@section('content')

        <header>
            <h1>All {{ $plural }}</h1>
        </header>

        <!-- errors -->
        @if( Session::has('message') )
            <div class="alert alert-{{ Session::get('status') }}">
                {!! Session::get('message') !!}
            </div>
        @endif

        <!-- controls -->
        <div class="form-group crud-controls">
            <a class="btn btn-small btn-info btn-create" href="{!! URL::to("$route/create") !!}">Create new {{ $singular }}</a>

            @if ( $data instanceof \Illuminate\Pagination\LengthAwarePaginator && $data->lastPage() > 1)
                {!! $data->render() !!}
            @endif

        </div>

        <table class="table table-striped table-bordered">

            <!-- header -->
            <thead>
                <tr>
                    @foreach($fields as /** @var davestewart\laravel\crud\CrudField */ $field)
                    <td>{{ $field->label }}</td>
                    @endforeach
                    <td>Actions</td>
                </tr>
            </thead>

            <!-- data -->
            <tbody>

                @foreach($data as $item)
                <tr>

                    @foreach($fields as $field)
                    <td>{!! $field->value($item) !!}</td>
                    @endforeach

                    <td>
                        @include($views->actions)
                    </td>

                </tr>
                @endforeach

            </tbody>

        </table>

        @if ( $data instanceof \Illuminate\Pagination\LengthAwarePaginator && $data->lastPage() > 1)
            {!! $data->render() !!}
        @endif

@stop
