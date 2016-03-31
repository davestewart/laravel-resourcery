@section('head')
    <title>{{ $Action }} {{ $Singular }}</title>
@endsection

@section('content')

    <header>
        <h1>{{ $Action }} {{ $singular }}: {{ $title }}</h1>
    </header>

    <!--
    @if( count($errors->all()) )
        <div class="alert alert-danger">
        {!! HTML::ul($errors->all()) !!}
            The form has errors
        </div>
    @endif
    -->

    <?php
		$attr = ['class' => 'form-horizontal', 'url' => $route, 'data-action' => $action, 'autocomplete' => 'off'];
		if($action == 'edit')
		{
			$attr = array_merge($attr, ['url' => "$route/$data->id", 'method' => 'PUT']);
		}
    ?>

    <!-- start form -->
    {!! Form::open( $attr ) !!}

        <!-- autocomplete hack for chrome -->
        <span style="display:none"><input><input type="password"></span>

        @include($views->fields)

        <div class="form-group">
            <div class="col-sm-offset-2 col-sm-10">
                {!! Form::submit('Submit', ['class' => 'btn btn-primary']) !!}
                {!! HTML::link($redirect, 'Cancel', ['class' => 'btn btn-secondary', 'onclick' => 'return confirm("Are you sure you want to cancel?");']) !!}
            </div>
        </div>

    {!! Form::close() !!}

@stop

