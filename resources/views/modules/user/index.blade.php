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

            $icon = '';
            if(action_btn_class == 'btn-primary' || action_btn_class == 'submitAdminBtn'){
                $icon = "<div class='icon-modal-default'><i class='fa-solid fa-cloud-arrow-up fa-xl Neutral-500-cl'></i></div>";
            }else{
                $icon = "<div class='icon-modal-default'><i class='fa-regular fa-trash-can fa-xl Accent-200-cl'></i></div>";
            }

            e.preventDefault();
            e.stopPropagation();

            bootbox.dialog({
                closeButton: false,
                message: "<p class='text-center'>"+message+"</p>",
                title: $icon+"<div class='modal-title-default text-center mb-0'>"+title+"</div>",
                buttons: {
                    cancel_btn: {
                        label: cancel_text,
                        className: "cancelAdminBtn position-center"
                    },
                    action_btn: {
                        label: action_text,
                        className: action_btn_class+" "+"position-center",
                        callback: function () {
                            form.submit();
                        }
                    }
                }
            });
        });
    }

    window.currentPopoverUserID = null;

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

        $('.menu-popover').popover().on('click', function (e) {
            window.currentPopoverUserID = $(this).closest('tr').find('[data-userid]').data('userid') || null;
            togglePopover.call(this, e);
        }).on('blur', hidePopover);

        $('.menu-popover').on('shown.bs.popover', function () {

            var $trigger = $(this);
            var userID = $trigger.closest('tr').find('[data-userid]').data('userid') || window.currentPopoverUserID || null;
            userID = userID ? (parseInt(userID, 10) || userID) : null;

            var $popover = $('.popover').last();
            $popover.data('userid', userID);

            $('.popover').mousedown(function () {
                click_in_process = true;
            });
            $('.popover').mouseup(function () {
                click_in_process = false;
                $(this).popover('hide');
            });
            act_confirm();
        });

        $(document).on('hidden.bs.popover', function () {
            window.currentPopoverUserID = null;
        });

    }

    function tooltip_init() {
        $('[data-bs-toggle="tooltip"]').tooltip({container: 'body'});
    }

    $(function () {
        var initComplete = function () {
            var api = this.api();
            var column = api.column(1);
            var select = $('<select id="select_role" aria-label="{{ js_escape(trans('langAllUsers')) }}">' +
                           '<option value="0">-- {{ js_escape(trans('langAllUsers')) }} --</option>' +
                           '<option value="editor">{{ js_escape(trans('langTeacher')) }}</option>' +
                           '<option value="course_reviewer">{{ js_escape(trans('langCourseReviewer')) }}</option>' +
                           '<option value="student">{{ js_escape(trans('langStudent')) }}</option>' +
                           '<option value="guest">{{ js_escape(trans('langGuestName')) }}</option>' +
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
            ajax: {
                url: '{{ $ajaxUrl }}',
                type: 'POST'
            },
            lengthMenu: [10, 15, 20 , -1],
            sPaginationType: 'full_numbers',
            aoColumnDefs: [
                { sClass: 'option-btn-cell text-end', aTargets: [ -1 ] },
                { bSortable: true, aTargets: [ 0 ] },
                { bSortable: false, aTargets: [ 1 ] },
                { sClass:'text-center', bSortable: false, aTargets: [ 2 ] },
                { bSortable: false, aTargets: [ 4 ] }
            ],
            oLanguage: {
                lengthLabels: {
                    '-1': '{{ trans('langAllOfThem') }}'
                },
                sLengthMenu: '{{ js_escape(trans('langDisplay') . ' _MENU_ ' . trans('langResults2')) }}',
                sZeroRecords: '{{ js_escape(trans('langNoResult')) }}',
                sInfo: '{{ js_escape(trans('langDisplayed') . ' _START_ ' .
                                     trans('langTill') . ' _END_ ' . trans('langFrom2') .
                                     ' _TOTAL_ ' . trans('langTotalResults')) }}',
                sInfoEmpty: '',
                sInfoFiltered: '',
                sInfoPostFix:  '',
                sSearch:       '',
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
            var self_id = $(this).closest('tr').data('self_id');

            bootbox.confirm({
                closeButton: false,
                title: "<div class='icon-modal-default'><i class='fa-regular fa-trash-can fa-xl Accent-200-cl'></i></div><div class='modal-title-default text-center mb-0'>{{ js_escape(trans('langDeleteUser')) }}</div>",
                message: "<p class='text-center'>{{ js_escape(trans('langDeleteUser')) }}&nbsp;{{ js_escape(trans('langDeleteUser2')) }}</p>",
                buttons: {
                    cancel: {
                        label: "{{ js_escape(trans('langCancel')) }}",
                        className: "cancelAdminBtn position-center"
                    },
                    confirm: {
                        label: "{{ js_escape(trans('langDelete')) }}",
                        className: "deleteAdminBtn position-center",
                    }
                },
                callback: function (result) {
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
                                if (self_id) {
                                    window.location.href = '{{ $urlServer }}';
                                } else {
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
                                }
                            },
                            error: function(xhr, textStatus, error) {
                                console.log(xhr.statusText);
                                console.log(textStatus);
                                console.log(error);
                            }
                        });
                    }
                }
            });
        });
        $('.dt-search input')
            .attr({ style: 'width: 200px',
                    class: 'form-control input-sm mb-3',
                    placeholder: '{{ js_escape(trans('langName') . ', Username, Email') }}' });
        $('.dt-search label').attr('aria-label', '{{ trans('langSearch') }}');
        $('.success').delay(3000).fadeOut(1500);

        // .rights_menu
        $(document).on('click', '.rights_menu', function (e) {
            e.preventDefault();

            let userID = window.currentPopoverUserID
            userID = userID ? (parseInt(userID, 10) || userID) : null;

            $.ajax({
                type: 'POST',
                url: '{{ $ajaxUrl }}',
                datatype: 'json',
                data: {
                    action: 'getUserRights',
                    userID: userID
                },
                success: function(data) {
                    let resp = (typeof data === 'string') ? (data ? JSON.parse(data) : {}) : (data || {});

                    let @if (get_config('allow_teacher_clone_course'))
                        checklist = "<form class='form-horizontal' role='form' method='post'>" +
                            "<div class='d-flex gap-2'><input type='checkbox' id='userRights_CourseAdminTools' name='userRights_CourseAdminTools'><label for='userRights_CourseAdminTools'>{{ js_escape(trans('langCourseAdminTools')) }}</label></div>" +
                            "<div class='d-flex gap-2'><input type='checkbox' id='userRights_AdminUsers' name='userRights_AdminUsers'><label for='userRights_AdminUsers'>{{ js_escape(trans('langAdminUsers')) }}</label></div>" +
                            "<div class='d-flex gap-2'><input type='checkbox' id='userRights_ArchiveCourse' name='userRights_ArchiveCourse'><label for='userRights_ArchiveCourse'>{{ js_escape(trans('langArchiveCourse')) }}</label></div>" +
                            "<div class='d-flex gap-2'><input type='checkbox' id='userRights_CloneCourse' name='userRights_CloneCourse'><label for='userRights_CloneCourse'>{{ js_escape(trans('langCloneCourse')) }}</label></div>" +
                            "</form>";
                    @else
                        checklist = "<form class='form-horizontal' role='form' method='post'>" +
                            "<div class='d-flex gap-2'><input type='checkbox' id='userRights_CourseAdminTools' name='userRights_CourseAdminTools'><label for='userRights_CourseAdminTools'>{{ js_escape(trans('langCourseAdminTools')) }}</label></div>" +
                            "<div class='d-flex gap-2'><input type='checkbox' id='userRights_AdminUsers' name='userRights_AdminUsers'><label for='userRights_AdminUsers'>{{ js_escape(trans('langAdminUsers')) }}</label></div>" +
                            "<div class='d-flex gap-2'><input type='checkbox' id='userRights_ArchiveCourse' name='userRights_ArchiveCourse'><label for='userRights_ArchiveCourse'>{{ js_escape(trans('langArchiveCourse')) }}</label></div>" +
                            "</form>";
                    @endif

                    bootbox.confirm({
                        closeButton: false,
                        title: "<div class='icon-modal-default'><i class='fa-solid fa-list-check fa-xl'></i></div><div class='modal-title-default text-center mb-0'>{{ js_escape(trans('langUserPermissions')) }}</div>",
                        message: "<p class='text-center'>"+checklist+"</p>",
                        buttons: {
                            cancel: {
                                label: "{{ js_escape(trans('langCancel')) }}",
                                className: "cancelAdminBtn position-center"
                            },
                            confirm: {
                                label: "{{ js_escape(trans('langAnalyticsConfirm')) }}",
                                className: "submitAdminBtn position-center",
                            }
                        },
                        callback: function (result) {
                            if (result) {

                                let userRights_CourseAdminTools = $('#userRights_CourseAdminTools').is(':checked') ? 1 : 0;
                                let userRights_AdminUsers = $('#userRights_AdminUsers').is(':checked') ? 1 : 0;
                                let userRights_ArchiveCourse = $('#userRights_ArchiveCourse').is(':checked') ? 1 : 0;
                                let userRights_CloneCourse = $('#userRights_CloneCourse').is(':checked') ? 1 : 0;

                                $.ajax({
                                    type: 'POST',
                                    url: '{{ $ajaxUrl }}',
                                    datatype: 'json',
                                    data: {
                                        action: 'updateRights',
                                        userID: userID,
                                        userRights_CourseAdminTools: userRights_CourseAdminTools,
                                        userRights_AdminUsers: userRights_AdminUsers,
                                        userRights_ArchiveCourse: userRights_ArchiveCourse,
                                        userRights_CloneCourse: userRights_CloneCourse
                                    },
                                    success: function(data) {
                                        location.reload();
                                    },
                                    error: function(xhr, textStatus, error) {
                                        console.log(xhr.statusText);
                                        console.log(textStatus);
                                        console.log(error);
                                    }
                                });
                            }
                        }
                    });

                    $('.modal').last().one('shown.bs.modal', function () {
                        $('#userRights_CourseAdminTools').prop('checked', resp.admin_modules == 1);
                        $('#userRights_AdminUsers').prop('checked', resp.admin_users == 1);
                        $('#userRights_ArchiveCourse').prop('checked', resp.course_backup == 1);
                        $('#userRights_CloneCourse').prop('checked', resp.course_clone == 1);
                    });

                },
                error: function(xhr, textStatus, error) {
                    console.log(xhr.statusText);
                    console.log(textStatus);
                    console.log(error);
                }
            });

        });

    });
</script>
@endpush

@section('content')

<div class="col-12 main-section">
    <div class='{{ $container }} module-container py-lg-0'>
        <div class="course-wrapper d-lg-flex align-items-lg-strech w-100">

            @include('layouts.partials.left_menu')

            <div class="col_maincontent_active">

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

                    {!! $action_bar !!}

                    @include('layouts.partials.show_alert')

                    <div class='col-12'>
                        <div class='table-responsive'>
                            <table id='users_table_{{ $course_code }}' class='table-default'>
                                <thead>
                                    <tr class="list-header">
                                        <th>{{ trans('langSurnameName') }}</th>
                                        <th>{{ trans('langRole') }}</th>
                                        <th>{{ trans('langGroup') }}</th>
                                        <th>{{ trans('langRegistrationDate') }}</th>
                                        <th class='text-end' aria-label="{{ trans('langSettingSelect') }}">{!! icon('fa-cogs') !!}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th aria-label="{{ trans('langSurnameName') }}"></th>
                                        <th aria-label="{{ trans('langRole') }}"></th>
                                        <th aria-label="{{ trans('langGroup') }}"></th>
                                        <th aria-label="{{ trans('langRegistrationDate') }}"></th>
                                        <th class='text-end' aria-label="{{ trans('langSettingSelect') }}"></th>
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
