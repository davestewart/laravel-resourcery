
<div class="form-group">
	<div class="col-sm-offset-2 col-sm-10">
		<button type="submit" class="btn btn-primary">{{ $lang->action->submit }}</button>
		<a href="{{ $redirect }}"
		   class="btn btn-secondary"
		   onclick="return confirm(' {{ $lang->confirm->cancel  }} ');">{{ $lang->action->cancel }}</a>
	</div>
</div>
