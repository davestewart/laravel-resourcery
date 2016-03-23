@extends('vendor.crud.layout')

@section('head')
    <title>All {{ $plural }}</title>
@stop

@section('content')

        <h1>All {{ $plural }}</h1>

        <!-- will be used to show any messages -->
        @if (Session::has('message'))
        <div class="alert alert-info">{{{ Session::get('message') }}}</div>
        <script>
            $('.alert')
                .delay(2000)
                .animate({opacity:0}, 300)
                .slideUp(300);
        </script>
        @endif

        <div class="pagination">
            @if (method_exists($data, 'total'))
            {{{ $data->render() }}}
            @endif
        </div>

        <table class="table table-striped table-bordered">

            <thead>
                <tr>
                    @foreach($fields as $value)
                    <td>{{ $value }}</td>
                    @endforeach
                    <td>Actions</td>
                </tr>
            </thead>

            <tbody>

                @foreach($data as $key => $value)
                <tr>
                    @foreach($fields as $key)
                    <td>{{ $value->$key }}</td>
                    @endforeach
                    <td>
                        <a class="btn btn-small btn-success" href="{{{ URL::to("$route/$value->id") }}}">Show {{ $singular }}</a>
                        <a class="btn btn-small btn-info" href="{{{ URL::to("$route/$value->id/edit") }}}">Edit {{ $singular }}</a>

                        {{{ Form::open(array('url' => "$route/$value->id", 'style' => 'display:inline-block')) }}}
                            {{{ Form::hidden('_method', 'DELETE') }}}
                            {{{ Form::submit("Delete $singular", array('class' => 'btn btn-warning')) }}}
                        {{{ Form::close() }}}
                    </td>
                </tr>
                @endforeach

            </tbody>

        </table>

        <div class="pagination">
            @if (method_exists($data, 'total'))
            {{{ $data->render() }}}
            @endif
        </div>



@stop
