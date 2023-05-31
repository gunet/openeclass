@extends('layouts.default')

@push('head_scripts')
<script type='text/javascript'>

    function act_confirm() {
        $('.confirmAction').on('click', function (e) {
            var message = $(this).attr('data-message');
            var title = $(this).attr('data-title');
            var cancel_text = $(this).attr('data-cancel-txt');
            var action_text = $(this).attr('data-action-txt');
            var action_btn_class = $(this).attr('data-action-class');
            var form = $(this).closest('form').clone().appendTo('body');

            e.preventDefault();
            e.stopPropagation();

            bootbox.dialog({
                message: message,
                title: title,
                buttons: {
                    cancel_btn: {
                        label: cancel_text,
                        className: "cancelAdminBtn"
                    },
                    action_btn: {
                        label: action_text,
                        className: action_btn_class,
                        callback: function () {
                            form.submit();
                        }
                    }
                }
            });
        });
    }

    function popover_init() {
        $('[data-bs-toggle="popover"]').on('click',function(e){
            e.preventDefault();
        }).popover();
        var click_in_process = false;
        var hidePopover = function () {
            if (!click_in_process) {
                $(this).popover('hide');
            }
        }
        , togglePopover = function () {
            $(this).popover('toggle');
            $('#action_button_menu').parent().parent().addClass('menu-popover');
        };
        $('.menu-popover').popover().on('click', togglePopover).on('blur', hidePopover);
        $('.menu-popover').on('shown.bs.popover', function () {
            $('.popover').mousedown(function () {
                click_in_process = true;
            });
            $('.popover').mouseup(function () {
                click_in_process = false;
                $(this).popover('hide');
            });
            act_confirm();
        });

    }

    function tooltip_init() {
        $('[data-bs-toggle="tooltip"]').tooltip({container: 'body'});
    }

    $(function () {
        var initComplete = function () {
            var api = this.api();
            var column = api.column(1);
            var select = $('<select id="select_role">' +
                           '<option value="0">-- {{ js_escape(trans('langAllUsers')) }} --</option>' +
                           '<option value="teacher">{{ js_escape(trans('langTeacher')) }}</option>' +
                           '<option value="student">{{ js_escape(trans('langStudent')) }}</option>' +
                           '<option value="editor">{{ js_escape(trans('langEditor')) }}</option>' +
                           '<option value="tutor">{{ js_escape(trans('langTutor')) }}</option>' +
                        @if (get_config('opencourses_enable'))
                           '<option value="reviewer">{{ js_escape(trans('langOpenCoursesReviewer')) }}</option>' +
                        @endif
                           '</select>')
                .appendTo($(column.footer()).empty());
        }
        var oTable = $('#users_table_{{ $course_code }}').DataTable({
            initComplete: initComplete,
            createdRow: function(row, data, dataIndex) {
                if (data[5] == 1) {
                    $(row). addClass('not_visible');
                }
            },
            bStateSave: true,
            bProcessing: true,
            bServerSide: true,
            sScrollX: false,
            drawCallback: function(oSettings) {
                tooltip_init();
                popover_init();
            },
            sAjaxSource: '{{ $ajaxUrl }}',
            aLengthMenu: [
               [10, 15, 20 , -1],
               [10, 15, 20, '{{ js_escape(trans('langAllOfThem')) }}']
            ],
            sPaginationType: 'full_numbers',
            bSort: true,
            aaSorting: [[0, 'desc']],
            aoColumnDefs: [
                { sClass: 'option-btn-cell text-center', aTargets: [ -1 ] },
                { bSortable: false, aTargets: [ 1 ] },
                { sClass:'text-center', bSortable: false, aTargets: [ 2 ] },
                { bSortable: false, aTargets: [ 4 ] }
            ],
            oLanguage: {
                sLengthMenu: '{{ js_escape(trans('langDisplay') . ' _MENU_ ' . trans('langResults2')) }}',
                sZeroRecords: '{{ js_escape(trans('langNoResult')) }}',
                sInfo: '{{ js_escape(trans('langDisplayed') . ' _START_ ' .
                                     trans('langTill') . ' _END_ ' . trans('langFrom2') .
                                     ' _TOTAL_ ' . trans('langTotalResults')) }}',
                sInfoEmpty: '{{ js_escape(trans('langDisplayed') . ' 0 ' . trans('langTill') .
                                          ' 0 ' . trans('langFrom2') . ' 0 ' . trans('langResults2')) }}',
                sInfoFiltered: '',
                sInfoPostFix:  '',
                sSearch:       '',
                sUrl:          '',
                oPaginate: {
                    sFirst: '&laquo;',
                    sPrevious: '&lsaquo;',
                    sNext: '&rsaquo;',
                    sLast: '&raquo;'
                }
            }
        });

        // Apply the filter
        $(document).on('change', 'select#select_role', function (e) {
            oTable
                .column($(this).parent().index() + ':visible')
                .search($('select#select_role').val())
                .draw();
        });
        $(document).on('click', '.delete_btn', function (e) {
            e.preventDefault();
            var row_id = $(this).closest('tr').attr('id');
            bootbox.confirm('{{ js_escape(trans('langDeleteUser') . ' ' . trans('langDeleteUser2')) }}',
                function (result) {
                    if (result) {
                        $.ajax({
                            type: 'POST',
                            url: '',
                            datatype: 'json',
                            data: {
                                action: 'delete',
                                value: row_id
                            },
                            success: function(data) {
                                var info = oTable.page.info();
                                var per_page = info.length;
                                var page_number = info.page;
                                if (info.recordsDisplay % info.length == 1) {
                                    if (page_number != 0) {
                                        page_number--;
                                    }
                                }
                                $('#tool_title').after('<p class="success">{{ js_escape(trans('langUserDeleted')) }}</p>');
                                $('.success').delay(3000).fadeOut(1500);
                                oTable.page(page_number).draw(false);
                            },
                            error: function(xhr, textStatus, error) {
                                console.log(xhr.statusText);
                                console.log(textStatus);
                                console.log(error);
                            }
                        });
                    }
                });
        });
        $('.dataTables_filter input')
            .attr({ style: 'width: 200px',
                    class: 'form-control input-sm mb-3',
                    placeholder: '{{ js_escape(trans('langName') . ', Username, Email') }}' });
        $('.success').delay(3000).fadeOut(1500);
    });
</script>
@endpush

@section('content')

<div class="pb-lg-3 pt-lg-3 pb-0 pt-0">

    <div class="container-fluid main-container">

        <div class="row rowMedium">

            <div id="background-cheat-leftnav" class="col-xl-2 col-lg-3 col_sidebar_active d-flex justify-content-start align-items-strech ps-lg-0 pe-lg-3"> 
                <div class="d-none d-sm-block d-sm-none d-md-block d-md-none d-lg-block ContentLeftNav">
                    @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                </div>
            </div>

            <div class="col-xl-10 col-lg-9 col-12 col_maincontent_active">
                    
                <div class="row p-lg-5 p-md-5 ps-1 pe-1 pt-5 pb-5">
                    

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])


                    <div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="collapseTools" aria-labelledby="offcanvasExampleLabel">
                        <div class="offcanvas-header">
                            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                        </div>
                        <div class="offcanvas-body">
                            @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                        </div>
                    </div>


                    @include('layouts.partials.legend_view',['is_editor' => $is_editor, 'course_code' => $course_code])

                    


                    {!! $action_bar !!}

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
 
                    <div class='col-12'>
                        <div class='table-responsive'>
                            <table id='users_table_{{ $course_code }}' class='table-default w-100 ms-0'>
                                <thead>
                                    <tr class="list-header">
                                        <th>{{ trans('langSurnameName') }}</th>
                                        <th class='text-center'>{{ trans('langRole') }}</th>
                                        <th class='text-center'>{{ trans('langGroup') }}</th>
                                        <th class='text-center' width='80'>{{ trans('langRegistrationDate') }}</th>
                                        <th class='text-center'>{!! icon('fa-cogs') !!}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>


@endsection
