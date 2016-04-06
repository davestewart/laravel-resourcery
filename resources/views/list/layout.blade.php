
	<table class="table table-striped table-bordered">

	    <!-- header -->
	    <thead>
	        <tr>
	            @foreach($fields as /** @var davestewart\resourcery\classes\forms\Field */ $field)
	            <td>{{ $field->label }}</td>
	            @endforeach
	            <td>Actions</td>
	        </tr>
	    </thead>

	    <!-- data -->
	    <tbody>

	        @foreach($data as $item)
			<?php $lang->setModel($item); ?>
	        <tr>

	            @foreach($fields as $field)
	            <td>{!! $field->value($item) !!}</td>
	            @endforeach

	            <td>
	                @include($views->actions)
	            </td>

	        </tr>
	        @endforeach

	    </tbody>

	</table>
