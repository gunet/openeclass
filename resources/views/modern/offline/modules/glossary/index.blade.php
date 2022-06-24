@extends('layouts.default_old')

@section('content')

    <div class='alphabetic_index'>
        {!! $prefixes !!}
    </div>
    <div class='table-responsive glossary-categories'>
        <table class='table-default'>
            <tr class='list-header'>
                <th class='text-left'>{{ trans('langGlossaryTerm') }}</th>
                <th class='text-left'>{{ trans('langGlossaryDefinition') }}</th>
            </tr>
            @foreach ($glossary as $data)
                <tr>
                    <td width='150'>
                        <strong>{{ $data->term }}</strong><br>
                        @if (!empty($data->category_id))
                            <span>
                                <small>
                                    <span class='text-muted'>{{ trans('langCategory') }}: {{ $categories[$data->category_id] }}</span>
                                </small>
                            </span>
                        @endif
                    </td>
                    <td>
                        @if (!empty($data->definition))
                            <em>{{ $data->definition }}</em>
                        @endif
                        @if (!empty($data->url))
                            <div>
                                <span class='term-url'>
                                    <small>
                                        <a href='{{ $data->url }}' target='_blank'>{{ $data->url }}&nbsp;&nbsp;
                                        <i class='fa fa-external-link' style='color:#444;'></i></a>
                                    </small>
                                </span>
                            </div>
                        @endif
                        @if (!empty($data->notes))
                            <br><u>{{ trans('langComments') }}:</u>
                            <div class='text-muted'>{!! standard_text_escape($data->notes) !!}</div>
                        @endif
                    </td>
                </tr>
            @endforeach
        </table>
    </div>
@endsection