@if ( $data instanceof \Illuminate\Pagination\LengthAwarePaginator && $data->lastPage() > 1)
	{!! $data->render() !!}
@endif
