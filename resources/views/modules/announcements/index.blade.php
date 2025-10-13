@extends('layouts.default')

@push('head_scripts')
    <script src="{{ $urlServer }}/js/trunk8.js"></script>
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

    /*
        Ref: https://datatables.net/forums/discussion/77095/bootstrap-5-tooltips-stay-on-screen-when-datatable-reloads
     */
    function tooltip_init() {
        let tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        let tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl, {
                boundary: document.body,
                container: 'body',
                trigger: 'hover'
            }
        ));
        tooltipList.forEach((tooltip) => { $('.tooltip').hide(); });
    }

    let checkboxStates = [];

    $(document).ready(function(){
        $('li.bulk-processing a').on('click', function(event) {
            event.preventDefault();
            $('.table-responsive').toggleClass('checkboxes-on');
            $('.bulk_select').toggleClass('d-none');
            $('.show-announcement-id').toggleClass('d-none');
            $('.bulk-processing-box').toggleClass('d-none');

            if ($(this).find('span.fa-solid.fa-check').length) {
                $(this).find('span.fa-solid.fa-check').remove();
            } else {
                $(this).append('<span class="fa-solid fa-check text-success"></span>');
            }
        });


        $('#ann_table{{ $course_id }}').on('change', 'input[type="checkbox"]', function() {
            let cbid = $(this).attr('cbid');
            checkboxStates[cbid] = this.checked;

            let selectedCbidValues = $('#selectedcbids').val().split(',');
            let cbidIndex = selectedCbidValues.indexOf(cbid.toString());
            if (this.checked && cbidIndex === -1) {
                selectedCbidValues.push(cbid);
            } else if (!this.checked && cbidIndex !== -1) {
                selectedCbidValues.splice(cbidIndex, 1);
            }
            $('#selectedcbids').val(selectedCbidValues.filter(Boolean).join(','));

        });

        function restoreCheckboxStates() {
            $('#ann_table{{ $course_id }} tbody tr').each(function(index) {
                let checkbox = $(this).find('input[type="checkbox"]');
                let cbid = checkbox.attr('cbid');
                if (cbid in checkboxStates) {
                    checkbox.prop('checked', checkboxStates[cbid]);
                } else {
                    checkbox.prop('checked', false);
                }
            });
        }

        function checkCheckboxes() {
            if ($('.table-responsive').hasClass('checkboxes-on')) {
                $('.bulk_select').removeClass('d-none');
                $('.show-announcement-id').removeClass('d-block');
            }
        }

        var langEmptyGroupName = "{{ trans('langEmptyAnTitle') }}";

        var oTable = $('#ann_table{{ $course_id }}').DataTable ({
            @if ($is_editor)
                'aoColumnDefs':[
                    {'sClass':'option-btn-cell text-end','aTargets':[-1]},
                    {'sClass':'td-bulk-select','aTargets':[0]},
                ],
                'autoWidth': false,
            @endif
            bStateSave: true,
            bProcessing: true,
            bServerSide: true,
            sScrollX: true,
            fixedHeader: true,
            responsive: true,
            searchDelay: 1000,
            ajax: {
                url: '{{ $_SERVER['REQUEST_URI'] }}',
                type: 'POST'
            },
            lengthMenu: [10, 15, 20 , -1],
            fnDrawCallback: function( oSettings ) {
                popover_init();
                tooltip_init();
                $('.table_td_body').each(function() {
                    $(this).trunk8({
                        lines: '3',
                        fill: '&hellip;<div class="clearfix"></div><a style="float:right;" href="{{ $_SERVER['SCRIPT_NAME'] }}?course={{ $course_code }}&an_id='+ $(this).data('id')+'">{{ trans('langMore') }}</div>'
                    })
                });
                restoreCheckboxStates();
                checkCheckboxes();
                $('#ann_table{{ $course_id }}_wrapper .dt-search input').attr({
                    class : 'form-control input-sm mb-3 ms-0',
                    placeholder : '{{ trans('langSearch') }}...'
                });
                $('#ann_table{{ $course_id }}_wrapper .dt-search label').attr('aria-label', '{{ trans('langSearch') }}');
            },
            sPaginationType: 'full_numbers',
            bSortable: true,
            oLanguage: {
                lengthLabels: {
                    '-1': '{{ trans('langAllOfThem') }}'
                },
                sLengthMenu:   '{{ trans('langDisplay') }} _MENU_ {{ trans('langResults2') }}',
                zeroRecords:  '{{ trans('langNoResult') }}',
                sInfo:         '{{ trans('langDisplayed') }} _START_ {{ trans('langTill') }} _END_ {{ trans('langFrom2') }} _TOTAL_ {{ trans('langTotalResults') }}',
                sInfoEmpty:    '{{ trans('langDisplayed') }} 0 {{ trans('langTill') }} 0 {{ trans('langFrom2') }} 0 {{ trans('langResults2') }}',
                sInfoFiltered: '',
                sInfoPostFix:  '',
                sSearch:       '',
                oPaginate: {
                    sFirst:    '&laquo;',
                    sPrevious: '&lsaquo;',
                    sNext:     '&rsaquo;',
                    sLast:     '&raquo;'
                }
            }
        });

        $(document).on( 'click', '.reorder', function(e) {
            e.preventDefault();
            var link = $(this).attr('href');
            var tr_affected = $(this).closest('tr');

            $.ajax({
                type: 'POST',
                url: link,
                data: {
                    pin_announce: 1
                },
                beforeSend: function(){
                    tr_affected.css('backgroundColor','rgba(100,100,100,0.3)');
                },
                success: function(data){
                    oTable.ajax.reload(null, false);
                }
            });
        });

        $(document).on( 'click','.delete_btn', function (e) {
            e.preventDefault();
            var row_id = (this.id);
            bootbox.confirm({
                closeButton: false,
                title: "<div class='icon-modal-default'><i class='fa-regular fa-trash-can fa-xl Accent-200-cl'></i></div><div class='modal-title-default text-center mb-0'>{{ js_escape(trans('langConfirmDelete')) }}</div>",
                message: "<p class='text-center'>{{ js_escape(trans('langSureToDelAnnounce')) }}</p>",
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
                    if(result) {
                        $.ajax({
                            type: 'POST',
                            url: '',
                            datatype: 'json',
                            data: {
                                action: 'delete',
                                value: row_id
                            },
                            success: function(data){
                                var info = oTable.page.info();
                                var page_number = info.page;
                                oTable.draw(false);
                            },
                            error: function(xhr, textStatus, error){
                                console.log(xhr.statusText);
                                console.log(textStatus);
                                console.log(error);
                            }
                        });
                        $.ajax({
                            type: 'POST',
                            url: '{{$urlAppend}}modules/search/idxasync.php'
                        });
                    }
                }
            });
        });

        $(document).on( 'click','.vis_btn', function (g) {
            g.preventDefault();
            var temp_id = this.id;
            var row_id = temp_id.split("|")[0];
            var vis = temp_id.split("|")[1];

            $.ajax({
                type: 'POST',
                url: '',
                datatype: 'json',
                data: {
                    action: 'visible',
                    value: row_id,
                    visible: vis
                },
                success: function(data){
                    oTable.draw(false);
                },
                error: function(xhr, textStatus, error){
                    console.log(xhr.statusText);
                    console.log(textStatus);
                    console.log(error);
                }
            });
            $.ajax({
                type: 'POST',
                url: '{{$urlAppend}}modules/search/idxasync.php'
            });
        });
        $('.success').delay(3000).fadeOut(1500);
    });


</script>
<script>
    var readMore = '".js_escape($langReadMore)."';
    var readLess = '".js_escape($langReadLess)."';
    $(function () { $('.trunk8').trunk8({
        lines: 3,
        fill: '&hellip; <a class="read-more" href="#">{{ js_escape($GLOBALS['langViewShow']) }}</a>',
    });

        $(document).on('click', '.read-more', function (event) {
            $(this).parent().trunk8('revert').append(' <a class="read-less" href="#">{{ js_escape($GLOBALS['langViewHide']) }}</a>');
            event.preventDefault();
        });

        $(document).on('click', '.read-less', function (event) {
            $(this).parent().trunk8();
            event.preventDefault();
        });

    });
</script>
@endpush

@section('content')

<div class="col-12 main-section">
    <div class='{{ $container }} module-container announcement-index py-lg-0'>
        <div class="course-wrapper d-lg-flex align-items-lg-strech w-100">

            @include('layouts.partials.left_menu')

            <div class="col_maincontent_active">

                <div class="row">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    @include('layouts.partials.legend_view')

                    @if(isset($action_bar))
                        {!! $action_bar !!}
                    @else
                        <div class='mt-4'></div>
                    @endif

                    <div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="collapseTools">
                        <div class="offcanvas-header">
                            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="{{ trans('langClose') }}"></button>
                        </div>
                        <div class="offcanvas-body">
                            @if($course_code)
                                @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                            @else
                                @include('layouts.partials.sidebarAdmin')
                            @endif
                        </div>
                    </div>

                    @include('layouts.partials.show_alert')

                    @if ($showSubscribeWarning)
                        <div class='col-sm-12'>
                            <div class='alert alert-warning'>
                            <i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>
                                {{ trans('langNoUserEmailNotification') }}
                                (<a href='{{ $subscribeUrl }}'>{{ trans('langModify') }}</a>)</span>
                            </div>
                        </div>
                    @endif

                    <div class='col-12'>

                        @if ($is_editor)
                            <div class="bulk-processing-box d-none my-4">
                                <div class='@if(isset($module_id) and $module_id) d-lg-flex gap-4 @else row m-auto @endif mt-4'>
                                    <div class='@if(isset($module_id) and $module_id) flex-grow-1 @else col-lg-6 col-12 px-0 @endif'>
                                        <div class='form-wrapper form-edit'>
                                            <form method='post' action="{{ $_SERVER['SCRIPT_NAME'] }}">
                                                <label for='bulk-actions-announce' class='control-label-notes mb-2'>{{ trans('langBulkProcessing') }}</label>
                                                <select class='form-select' name="bulk_action" id='bulk-actions-announce'>
                                                    <option value="delete">{{ trans('langDelete') }}</option>
                                                    <option value="visible">{{ trans('langNewBBBSessionStatus') }}: {{ trans('langVisible') }}</option>
                                                    <option value="invisible">{{ trans('langNewBBBSessionStatus') }}: {{ trans('langInvisible') }}</option>
                                                </select>
                                                <div class='d-flex justify-content-end align-items-center gap-2 mt-4'>
                                                    <a href='index.php?course={{ $course_code }}' class='btn cancelAdminBtn'>{{ trans('langCancel') }}</a>
                                                    <input type="submit" class="btn submitAdminBtn" name="bulk_submit" value="{{ trans('langSubmit') }}">
                                                    <input type="hidden" id="selectedcbids" name="selectedcbids" value="">
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                    <div class='@if(isset($module_id) and $module_id) form-content-modules @else col-lg-6 col-12 @endif d-none d-lg-block'>
                                        <img class='form-image-modules' src='{!! get_form_image() !!}' alt="{{ trans('langImgFormsDes') }}">
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="table-responsive">
                            <table id='ann_table{{ $course_id }}' class='table-default table-announcements-indexes'>
                                <thead>
                                    <tr>
                                        @if ($is_editor)
                                            <th>#</th>
                                        @endif

                                        <th class='@if($is_editor) announceContent @else announceContentStudent @endif'>{{ trans('langAnnouncement') }}</th>
                                        <th>{{ trans('langDate') }}</th>
                                        @if ($is_editor)
                                            <th>{{ trans('langNewBBBSessionStatus') }}</th>
                                            <th class='btn-cell-content'></th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>


                </div>
            </div>

        </div>
    </div>
</div>
@endsection
