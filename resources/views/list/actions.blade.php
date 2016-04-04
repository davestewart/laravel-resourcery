
	<a class="btn btn-xs btn-success" href="{!! URL::to("$route/$item->id") !!}">View {{ $singular }}</a>
    <a class="btn btn-xs btn-info" href="{!! URL::to("$route/$item->id/edit") !!}">Edit {{ $singular }}</a>

    {!! Form::open(['url' => "$route/$item->id", 'style' => 'display:inline-block', 'id' => "form-$item->id"]) !!}
        {!! Form::hidden('_method', 'DELETE') !!}
        {!! Form::submit("Delete $singular", array('class' => 'btn btn-xs btn-warning')) !!}
    <script>$('#form-{{ $item->id }}').on('submit', function(){ return confirm('Are you sure you want to delete this {{ $singular }}?'); })</script>
    {!! Form::close() !!}
