@section('head')
    <title>{{ $Action }} {{ $Singular }}</title>
@endsection

@section('content')

    <header>
        <h1>{{ $Action }} {{ $singular }}: {{ $title }}</h1>
    </header>

    <!-- errors -->
    @include($views->errors)

    <!-- form -->
    <?php
		$attr = ['class' => 'form-horizontal', 'url' => $route, 'data-action' => $action, 'autocomplete' => 'off'];
		if($action == 'edit')
		{
			$attr = array_merge($attr, ['url' => "$route/$data->id", 'method' => 'PUT']);
		}
    ?>
    {!! Form::open( $attr ) !!}

        <!-- autocomplete hack for chrome -->
        <span style="display:none"><input><input type="password"></span>

        <!-- fields -->
        @include($views->fields)

        <!-- submit -->
        @include($views->submit)

    {!! Form::close() !!}

@stop

