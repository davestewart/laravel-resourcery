<div id="control_{{ $field->id }}" class="form-group {{ $classes }}">

	{!! $label !!}

	<div class="col-sm-10">

		{!! $control !!}

		@if( $field->error )
			<p class="text-danger error small">{!! $field->error !!}</p>
		@endif
	</div>

</div>
