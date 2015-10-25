@extends('vendor.crud.layout')

@section('head')
    <title>{{ $action }} {{ $singular }}</title>
@endsection

@section('content')

    <h1>{{ $Action }} {{ $singular }}</h1>

    <ul>
        @foreach($data->toArray() as $key => $value)

            <li>
                <strong>{{ $key }}: </strong>
                <span id="{{ $key }}"><?= is_object($value) ? '{object}' : $value ?></span>
            </li>

        @endforeach

    </ul>

@endsection