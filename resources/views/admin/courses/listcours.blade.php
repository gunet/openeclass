@push('head_scripts')
    <script type="text/javascript">
        $(document).ready(function() {
            $("#course_results_table").DataTable ({
                'aoColumnDefs':[{'sClass':'option-btn-cell text-end', 'aTargets':[-1]}],
                "bProcessing": true,
                "bServerSide": true,
                "sAjaxSource": "{!! $_SERVER['REQUEST_URI'] !!}",
                "aLengthMenu": [
                    [10, 15, 20 , -1],
                    [10, 15, 20, "{{ trans('langAllOfThem') }}"]
                ],
                "sPaginationType": "full_numbers",
                "bAutoWidth": false,
                "searchDelay": 1000,
                "aoColumns": [
                    {"bSortable" : true, "sWidth": "50%" },
                    {"bSortable" : false, "sClass": "text-start" },
                    {"bSortable" : false, "sWidth": "25%" },
                    {"bSortable" : false },
                ],
                "fnDrawCallback": function( oSettings ) {
                    popover_init();
                },
                "oLanguage": {
                    "sLengthMenu":   "{{ trans('langDisplay') }} _MENU_ {{ trans('langResults2') }}",
                    "sZeroRecords":  "{{ trans('langNoResult') }}",
                    "sInfo":         " {{ trans('langDisplayed') }} _START_ {{ trans('langTill') }} _END_ {{ trans('langFrom2') }} _TOTAL_ {{ trans('langToralResults') }}",
                    "sInfoEmpty":    " {{ trans('langDisplayed') }} 0 {{ trans('langTill') }} 0 {{ trans('langFrom2') }} 0 {{ trans('langResults2') }}",
                    "sInfoFiltered": '',
                    "sInfoPostFix":  '',
                    "sSearch":       '{{ trans('langSearch') }}',
                    "sUrl":          '',
                    "oPaginate": {
                    "sFirst":    '&laquo;',
                        "sPrevious": '&lsaquo;',
                        "sNext":     '&rsaquo;',
                        "sLast":     '&raquo;'
                    }
                }
        });
        $('.dataTables_filter input')
            .attr({ 'style': 'width: 200px',
                'placeholder': '{{ trans('langTitle') }}, {{ trans('langTeacher') }}'
            });
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

                    @if(isset($action_bar))
                        {!! $action_bar !!}
                    @else
                        <div class='mt-4'></div>
                    @endif

                    @include('layouts.partials.show_alert') 

                    <div class="col-12 overflow-auto">
                        <table id='course_results_table' class='table-default display'>
                            <thead>
                                <tr class='list-header'>
                                <th>{{ trans('langCourseCode') }}</th>
                                <th>{{ trans('langGroupAccess') }}</th>
                                <th>{{ trans('langFaculty') }}</th>
                                <th>{!! icon('fa-cogs') !!}</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                    @if (isset($_GET['formsearchfaculte']) and $_GET['formsearchfaculte'] and is_numeric(getDirectReference($_GET['formsearchfaculte'])))
                        <div class='col-12 mt-4'>
                            <form action='multieditcourse.php' method='post'>
                                <!--redirect all request vars towards action-->
                                @foreach ($_REQUEST as $key => $value)
                                    <input type='hidden' name='{!! q($key) !!}' value='{!! q($value) !!}'>
                                @endforeach

                                <input class='btn submitAdminBtn' type='submit' name='move_submit' value='{{ trans('langChangeDepartment') }}'>
                                {!! generate_csrf_token_form_field() !!}
                            </form>
                        </div>
                    @endif
            </div>
        </div>
    </div>

@endsection
