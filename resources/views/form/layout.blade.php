@section('head')
	<title>{{ $lang->title->$action }}</title>
@endsection

@section('content')

	<header>
		<h1>{{ $lang->title->$action }}</h1>
	</header>

	<!-- errors -->
	@include($views->errors)

	<!-- form -->
	<?php
		$form =
		[
			'url'           => $route,
			'data-action'   => $action,
			'autocomplete'  => 'off',
			'class'         => 'form-horizontal',
		];
		if ($action == 'edit')
		{
			$form = array_merge($form, ['url' => "$route/$data->id", 'method' => 'PUT']);
		}
	?>
	{!! Form::open( $form ) !!}

		<!-- autocomplete hack for chrome -->
		<span style="display:none"><input><input type="password"></span>

		<!-- fields -->
		@include($views->fields)

			<!-- submit -->
		@include($views->submit)

	{!! Form::close() !!}

@stop

