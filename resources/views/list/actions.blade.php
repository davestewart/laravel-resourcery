
	<a class="btn btn-xs btn-success" href="{!! URL::to("$route/$item->id") !!}">{{ $lang->action->View }}</a>
    <a class="btn btn-xs btn-info" href="{!! URL::to("$route/$item->id/edit") !!}">{{ $lang->action->Edit }}</a>

    {!! Form::open(['url' => "$route/$item->id", 'style' => 'display:inline-block', 'id' => "form-$item->id"]) !!}
        {!! Form::hidden('_method', 'DELETE') !!}
        {!! Form::submit($lang->action->Delete, array('class' => 'btn btn-xs btn-warning')) !!}
    <script>$('#form-{{ $item->id }}').on('submit', function(){ return confirm('{!! $lang->confirm->delete !!}'); })</script>
    {!! Form::close() !!}
