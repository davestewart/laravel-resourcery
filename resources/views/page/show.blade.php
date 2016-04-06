@extends($views->layout)

@section('head')
    <title>{{ $lang->title->show }}</title>
@endsection

@section('content')

    <h1>{{ $lang->title->show }}</h1>

    <ul>
        @foreach($fields as $field)

            <li id="field-{{ $field->name }}">
                <strong>{{ $field->label }}: </strong>
                <span>{{ is_object($field->value) ? print_r($field->value, 1) : $field->value }}</span>
            </li>

        @endforeach

    </ul>

@endsection