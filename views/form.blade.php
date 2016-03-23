<?php
    $attrs = ['class' => 'form-control', 'autocomplete' => 'off'];
?>

@section('head')
    <title>{{ $Action }} {{ $Singular }}</title>
@endsection

@section('content')

    <h1>{{ $Action }} {{ $singular }}</h1>

    <!-- show errors -->
    @if( count($errors->all()) )
        <div class="alert alert-danger">
        {{{ HTML::ul($errors->all()) }}}
        </div>
    @endif

    <!-- start form -->
    @if($view == 'create')
    {{{ Form::open( ['url' => $route, 'autocomplete' => 'off'] ) }}}
    @else
    {{{ Form::model($data, ['url' => "$route/$data->id", 'method' => 'PUT', 'autocomplete' => 'off'] ) }}}
    @endif

        <!-- autocomplete hack for chrome -->
        <span style="display:none"><input><input type="password"></span>

        <!-- loop over actual fields -->
        @foreach($fields as $key)
        <div class="form-group">

            {{{ Form::label($key, ucwords(str_replace('_', ' ', $key))) }}}

            @if( @$controls[$key] == 'textarea')
                {{{ Form::textarea($key, Input::old($key), $attrs) }}}
            @elseif( @$controls[$key] == 'password')
                {{{ Form::password($key, Input::old($key), $attrs) }}}
            @elseif( @$controls[$key] == 'checkbox')
                {{{ Form::checkbox($key, Input::old($key), $attrs) }}}
            @else
                {{{ Form::text($key, Input::old($key), $attrs) }}}
            @endif

        </div>
        @endforeach

        {{{ Form::submit("Submit", array('class' => 'btn btn-primary')) }}}

    {{{ Form::close() }}}

@stop

