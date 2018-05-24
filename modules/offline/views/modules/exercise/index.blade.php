@extends('layouts.default')

@push('head_scripts')
    <script src="{{ $urlAppend }}js/datatables/media/js/jquery.dataTables.min.js"></script>
    <script type='text/javascript'>
        $(document).ready(function() {

            $('#ex').DataTable ({
                'sPaginationType': 'full_numbers',
                'bAutoWidth': true,
                'searchDelay': 1000,
                'order' : [[1, 'desc']],
                'oLanguage': {
                    'sLengthMenu':   '{{ trans('langDisplay') }} _MENU_ {{ trans('langResults2') }}',
                    'sZeroRecords':  '{{ trans('langNoResult') }}',
                    'sInfo':         '{{ trans('langDisplayed') }} _START_ {{ trans('langTill') }} _END_ {{ trans('langFrom2') }} _TOTAL_ {{ trans('langTotalResults') }}',
                    'sInfoEmpty':    '{{ trans('langDisplayed') }} 0 {{ trans('langTill') }} 0 {{ trans('langFrom2') }} 0 {{ trans('langResults2') }}',
                    'sInfoFiltered': '',
                    'sInfoPostFix':  '',
                    'sSearch':       '',
                    'sUrl':          '',
                    'oPaginate': {
                        'sFirst':    '&laquo;',
                        'sPrevious': '&lsaquo;',
                        'sNext':     '&rsaquo;',
                        'sLast':     '&raquo;'
                    }
                }
            });
            $('.dataTables_filter input').attr({
                class : 'form-control input-sm',
                placeholder : '{{ trans('langSearch') }}...'
            });

        });
    </script>
@endpush

@push('head_styles')
    <link rel='stylesheet' type='text/css' href="{{ $urlAppend }}js/datatables/media/css/jquery.dataTables.css" />
@endpush

@section('content')

    <div class="table-responsive">
        <table id='ex' class='table-default'>
            <thead>
            <tr class='list-header'>
                <th>{{ trans('langExerciseName') }}</th>
                <th class='text-center'>{{ trans('langStart') }} / {{ trans('langFinish') }}</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($exercises as $exer)
                <tr>
                    <td>
                        <a href="exercise/{{ $exer->id }}.html">{!! standard_text_escape($exer->title) !!}</a>
                        {!! standard_text_escape($exer->description) !!}
                    </td>
                    <td class='smaller' align='center'>
                        {!! nice_format(date("Y-m-d H:i", strtotime($exer->start_date)), true) !!} /
                        @if (isset($exer->end_date))
                        {!! nice_format(date("Y-m-d H:i", strtotime($exer->end_date)), true) !!}
                        @else
                            -
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

@endsection