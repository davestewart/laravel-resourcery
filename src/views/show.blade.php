@extends('resourcery::layout')

@section('head')
    <title>{{ $action }} {{ $singular }}</title>
@endsection

@section('content')

    <h1>{{ $Action }} {{ $singular }}: {{ $title }}</h1>

    <ul>
        @foreach($fields as $field)

            <li id="attr-{{ $field->name }}">
                <strong>{{ $field->label }}: </strong>
                <span>{{ is_object($field->value) ? print_r($field->value, 1) : $field->value }}</span>
            </li>

        @endforeach

    </ul>

@endsection