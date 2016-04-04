
<div class="form-group">
	<div class="col-sm-offset-2 col-sm-10">
		{!! Form::submit('Submit', ['class' => 'btn btn-primary']) !!}
		{!! HTML::link($redirect, 'Cancel', ['class' => 'btn btn-secondary', 'onclick' => 'return confirm("Are you sure you want to cancel?");']) !!}
	</div>
</div>
