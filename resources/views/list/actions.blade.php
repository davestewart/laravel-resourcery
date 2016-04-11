
	<a href="{!! URL::to("$route/$item->id") !!}" class="btn btn-xs btn-success">{{ $lang->action('view') }}</a>
    <a href="{!! URL::to("$route/$item->id/edit") !!}" class="btn btn-xs btn-info">{{ $lang->action('edit') }}</a>
    <form method="POST" action="{!! URL::to("$route/$item->id") !!}" style="display:inline-block" onsubmit="onDelete(event, this)">
        <input name="method" type="hidden" value="DELETE">
        <input type="submit" value="{{ $lang->action('delete') }}" class="btn btn-xs btn-warning">
    </form>
