@push('head_scripts')
    <script type="text/javascript">
        $(document).ready(function() {
            $("#course_results_table").DataTable ({
                'aoColumnDefs':[{'sClass':'option-btn-cell text-center', 'aTargets':[-1]}],
                "bProcessing": true,
                "bServerSide": true,
                "sAjaxSource": "{{ $_SERVER['REQUEST_URI'] }}",
                "aLengthMenu": [
                    [10, 15, 20 , -1],
                    [10, 15, 20, "{{ trans('langAllOfThem') }}"]
                ],
                "sPaginationType": "full_numbers",
                "bAutoWidth": false,
                "searchDelay": 1000,
                "aoColumns": [
                    {"bSortable" : true, "sWidth": "50%" },
                    {"bSortable" : false, "sClass": "text-center" },
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

    <div class="pb-lg-3 pt-lg-3 pb-0 pt-0">

        <div class="container-fluid main-container">

            <div class="row rowMedium">

                <div class="col-12 justify-content-center col_maincontent_active_Homepage">

                    <div class="row p-lg-5 p-md-5 ps-1 pe-1 pt-5 pb-5">

                        @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                        @include('layouts.partials.legend_view',['is_editor' => $is_editor, 'course_code' => $course_code])

                        @if(Session::has('message'))
                            <div class='col-12 all-alerts'>
                                <div class="alert {{ Session::get('alert-class', 'alert-info') }} alert-dismissible fade show" role="alert">
                                    @if(is_array(Session::get('message')))
                                        @php $messageArray = array(); $messageArray = Session::get('message'); @endphp
                                        @foreach($messageArray as $message)
                                            {!! $message !!}
                                        @endforeach
                                    @else
                                        {!! Session::get('message') !!}
                                    @endif
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            </div>
                        @endif

                        {!! isset($action_bar) ?  $action_bar : '' !!}

                        <div class="overflow-auto">
                            <table id='course_results_table' class='table-default display'>
                                <thead>
                                    <tr class='list-header'>
                                    <th>{{ trans('langCourseCode') }}</th>
                                    <th>{{ trans('langGroupAccess') }}</th>
                                    <th>{{ trans('langFaculty') }}</th>
                                    <th class='text-center'>{!! icon('fa-cogs') !!}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                        @if (isset($_GET['formsearchfaculte']) and $_GET['formsearchfaculte'] and is_numeric(getDirectReference($_GET['formsearchfaculte'])))
                            <div class='col-12 mt-3'>
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
        </div>
    </div>

@endsection
