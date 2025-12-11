@push('head_scripts')
    <script type='text/javascript'>
        $(document).ready(function() {
            $('#bbb_courses').DataTable({
                'sPaginationType': 'full_numbers',
                'bAutoWidth': true,
                'searchDelay': 1000,
                'order': [[1, 'desc']],
                'oLanguage': {
                    'sLengthMenu': '{{ trans('langDisplay') }} _MENU_ {{ trans('langResults2') }}',
                    'sZeroRecords': '{{ trans('langNoResult') }}',
                    'sInfo': '{{ trans('langDisplayed') }} _START_ {{ trans('langTill') }} _END_ {{ trans('langFrom2') }} _TOTAL_ {{ trans('langTotalResults') }}',
                    'sInfoEmpty': '',
                    'sInfoFiltered': '',
                    'sInfoPostFix': '',
                    'sSearch': '',
                    'sUrl': '',
                    'oPaginate': {
                        'sFirst': '&laquo;',
                        'sPrevious': '&lsaquo;',
                        'sNext': '&rsaquo;',
                        'sLast': '&raquo;'
                    }
                }
            });
            $('.dt-search input').attr({
                'class': 'form-control input-sm ms-0 mb-3',
                'placeholder': '{{ trans('langSearch') }}...'
            });
            $('.dt-search label').attr('aria-label', '{{ trans('langSearch') }}');
        });

    </script>
@endpush

@extends('layouts.default')

@section('content')

    <div class="col-12 main-section">
        <div class='{{ $container }} main-container'>
            <div class="row m-auto">

                @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                @include('layouts.partials.legend_view')
                {!! $action_bar !!}

                <div class='table-responsive'>
                 <table class='table-default' id='bbb_courses'>
                     <thead>
                     <th>{{ trans('langCourse') }}</th>
                     <th>{{ trans('langFaculty') }}</th>
                     <th><span class='fa fa-cogs'></span></th>
                     </thead>
                     <tbody>
                        {!! $tbl_cnt !!}
                     </tbody>
                 </table>
                </div>
            </div>
        </div>
    </div>

@endsection
