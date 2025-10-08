@extends('layouts.default')

@push('head_scripts')
    <script src="{{ $urlAppend }}js/datatables/datatables.min.js"></script>
    <script type='text/javascript'>
        $(document).ready(function() {
            $('#ex').DataTable ({
                'sPaginationType': 'full_numbers',
                'bAutoWidth': true,
                'searchDelay': 1000,
                'lengthMenu': [10, 20, 30, -1],
                'oLanguage': {
                    'lengthLabels': {
                        '-1': '{{ trans('langAllOfThem') }}'
                    },
                    'sLengthMenu':   '{{ trans('langDisplay') }} _MENU_ {{ trans('langResults2') }}',
                    'zeroRecords':  '{{ trans('langNoResult') }}',
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
            $('.dt-search input').attr({
                class : 'form-control input-sm ms-0 mb-3',
                placeholder : '{{ trans('langSearch') }}...'
            });
            $('.dt-search label').attr('aria-label', '{{ trans('langSearch') }}');

        });
    </script>
@endpush

@push('head_styles')
    <link rel='stylesheet' type='text/css' href="{{ $urlAppend }}js/datatables/datatables.min.css" />
@endpush

@section('content')
    <div class="col-12 main-section">
        <div class='container module-container py-lg-0'>
            <div class="course-wrapper d-lg-flex align-items-lg-strech w-100">

                @include('layouts.partials.left_menu')

                <div class="col_maincontent_active col_maincontent_active_module">

                    <div class="row">

                        @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                        <div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="collapseTools">
                            <div class="offcanvas-header">
                                <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="{{ trans('langClose') }}"></button>
                            </div>
                            <div class="offcanvas-body">
                                @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                            </div>
                        </div>

                        @include('layouts.partials.legend_view')

                        <div class='col-12'>
                            <div class="table-responsive">
                                <table id='ex' class='table-default'>
                                    <thead>
                                    <tr class='list-header'>
                                        <th>{{ trans('langExerciseName') }}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach ($exercises as $exer)
                                        <tr>
                                            <td>
                                                <a href="exercise/{{ $exer->id }}.html">{!! standard_text_escape($exer->title) !!}</a>
                                                {!! standard_text_escape($exer->description) !!}
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
