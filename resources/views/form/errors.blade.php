
@if( count($errors->all()) )
	<div class="alert alert-danger alert-dismissable">
		<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
		<p>The form has errors</p>
		{!! HTML::ul($errors->all()) !!}
	</div>
@endif
