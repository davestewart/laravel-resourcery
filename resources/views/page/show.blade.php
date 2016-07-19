@extends($views->layout)

@section('head')
    <title>{{ $lang->title->show }}</title>
@endsection

@section('content')

    <h1>{{ $lang->title->show }}</h1>

    <table class="table table-striped table-bordered">

        <!-- header -->
        <thead>
            <tr>
                <th>{{ $lang->text->field }}</th>
                <th>{{ $lang->text->value }}</th>
            </tr>
        </thead>

        <!-- data -->
        <tbody>

        @foreach($fields as $field)

            <tr id="field-{{ $field->name }}">
                <th>{{ $field->label }}</th>

                @if(is_scalar($field->value) || is_null($field->value))
                <td>{{ $field->value }}</td>
                @else
                <td class="pre">{!! print_r($field->value, 1) !!}</td>
                @endif
            </tr>

        @endforeach

        </tbody>

    </table>

    <style type="text/css">
        .pre{
            white-space:pre;
        }
    </style>

@endsection