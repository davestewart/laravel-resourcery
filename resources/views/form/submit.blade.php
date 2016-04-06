
<div class="form-group">
	<div class="col-sm-offset-2 col-sm-10">
		{!! Form::submit($lang->prompt->submit, ['class' => 'btn btn-primary']) !!}
		{!! HTML::link($redirect, $lang->prompt->cancel, ['class' => 'btn btn-secondary', 'onclick' => 'return confirm("' .$lang->confirm->cancel. '");']) !!}
	</div>
</div>
