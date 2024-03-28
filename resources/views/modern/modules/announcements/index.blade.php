@extends('layouts.default')

@push('head_scripts')
<script src="{{ $urlServer }}/js/datatables/media/js/jquery.dataTables.min.js"></script>
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
                title: $icon+"<h3 class='modal-title-default text-center mb-0'>"+title+"</h3>",
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

    function tooltip_init() {
        $('[data-bs-toggle="tooltip"]').tooltip({container: 'body'});
    }

    let checkboxStates = [];

    $(document).ready(function(){


        $('li.bulk-processing a').on('click', function(event) {
            event.preventDefault();
            $('.table-responsive').toggleClass('checkboxes-on');
            $('.td-bulk-select').toggleClass('d-none');
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
                $('.td-bulk-select').removeClass('d-none');
            }
        }

        var langEmptyGroupName = "{{ trans('langEmptyAnTitle') }}";

        var oTable = $('#ann_table{{ $course_id }}').DataTable ({
            @if ($is_editor)
            'aoColumnDefs':[
                {'sClass':'option-btn-cell text-end','aTargets':[-1]},
                {'sClass':'d-none td-bulk-select','aTargets':[0]},
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
            sAjaxSource: '{{ $_SERVER['REQUEST_URI'] }}',
            aLengthMenu: [
                [10, 15, 20 , -1],
                [10, 15, 20, '{{ trans('langAllOfThem') }}'] // change per page values here
            ],
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
                $('#ann_table{{ $course_id }}_filter label input').attr({
                    class : 'form-control input-sm mb-3 ms-0',
                    placeholder : '{{ trans('langSearch') }}...'
                });
            },
            sPaginationType: 'full_numbers',
            bSortable: true,
            oLanguage: {
                sLengthMenu:   '{{ trans('langDisplay') }} _MENU_ {{ trans('langResults2') }}',
                sZeroRecords:  '{{ trans('langNoResult') }}',
                sInfo:         '{{ trans('langDisplayed') }} _START_ {{ trans('langTill') }} _END_ {{ trans('langFrom2') }} _TOTAL_ {{ trans('langTotalResults') }}',
                sInfoEmpty:    '{{ trans('langDisplayed') }} 0 {{ trans('langTill') }} 0 {{ trans('langFrom2') }} 0 {{ trans('langResults2') }}',
                sInfoFiltered: '',
                sInfoPostFix:  '',
                sSearch:       '',
                sUrl:          '',
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
                    console.log(tr_affected);
                    tr_affected.css('backgroundColor','rgba(100,100,100,0.3)');
                },
                success: function(data){
                    oTable.ajax.reload(null, false);
                }
            });
        });

        $(document).on( 'click','.delete_btn', function (e) {
            e.preventDefault();
            //var row_id = $(this).data('id');
            var row_id = (this.id);
            // bootbox.confirm('{{ js_escape(trans('langSureToDelAnnounce')) }}', function(result) {
            //     if(result) {
            //         $.ajax({
            //             type: 'POST',
            //             url: '',
            //             datatype: 'json',
            //             data: {
            //                 action: 'delete',
            //                 value: row_id
            //             },
            //             success: function(data){
            //                 var info = oTable.page.info();
            //                 var page_number = info.page;
            //                 oTable.draw(false);
            //             },
            //             error: function(xhr, textStatus, error){
            //                 console.log(xhr.statusText);
            //                 console.log(textStatus);
            //                 console.log(error);
            //             }
            //         });
            //         $.ajax({
            //             type: 'POST',
            //             url: '{{$urlAppend}}modules/search/idxasync.php'
            //         });
            //     }
            // });
            bootbox.confirm({
                closeButton: false,
                title: "<div class='icon-modal-default'><i class='fa-regular fa-trash-can fa-xl Accent-200-cl'></i></div><h3 class='modal-title-default text-center mb-0'>{{ js_escape(trans('langConfirmDelete')) }}</h3>",
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
            // var vis = $(this).data('vis');
            // var row_id = $(this).data('id');

            var temp_id = this.id;
            var row_id = temp_id.split("|")[0];
            var vis = temp_id.split("|")[1];

            console.log('the id:'+temp_id);
            console.log('the row_id:'+row_id);
            console.log('the vis:'+vis);

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

@push('head_styles')
<link rel='stylesheet' type='text/css' href="{{ $urlServer }}/js/datatables/media/css/jquery.dataTables.css" />
@endpush

@section('content')

<div class="col-12 main-section">
<div class='{{ $container }} module-container py-lg-0'>
        <div class="course-wrapper d-lg-flex align-items-lg-strech w-100">

            <div id="background-cheat-leftnav" class="col_sidebar_active d-flex justify-content-start align-items-strech ps-lg-0 pe-lg-0">
                <div class="d-none d-sm-block d-sm-none d-md-block d-md-none d-lg-block ContentLeftNav">
                    @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                </div>
            </div>

            <div class="col_maincontent_active">

                <div class="row">


                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    @include('layouts.partials.legend_view')

                    {!! $action_bar !!}

                    <div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="collapseTools">
                        <div class="offcanvas-header">
                            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                        </div>
                        <div class="offcanvas-body">
                            @if($course_code)
                                @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                            @else
                                @include('layouts.partials.sidebarAdmin')
                            @endif
                        </div>
                    </div>

                    @if(Session::has('message'))
                    <div class='col-12 all-alerts'>
                        <div class="alert {{ Session::get('alert-class', 'alert-info') }} alert-dismissible fade show" role="alert">
                            @php
                                $alert_type = '';
                                if(Session::get('alert-class', 'alert-info') == 'alert-success'){
                                    $alert_type = "<i class='fa-solid fa-circle-check fa-lg'></i>";
                                }elseif(Session::get('alert-class', 'alert-info') == 'alert-info'){
                                    $alert_type = "<i class='fa-solid fa-circle-info fa-lg'></i>";
                                }elseif(Session::get('alert-class', 'alert-info') == 'alert-warning'){
                                    $alert_type = "<i class='fa-solid fa-triangle-exclamation fa-lg'></i>";
                                }else{
                                    $alert_type = "<i class='fa-solid fa-circle-xmark fa-lg'></i>";
                                }
                            @endphp

                            @if(is_array(Session::get('message')))
                                @php $messageArray = array(); $messageArray = Session::get('message'); @endphp
                                {!! $alert_type !!}<span>
                                @foreach($messageArray as $message)
                                    {!! $message !!}
                                @endforeach</span>
                            @else
                                {!! $alert_type !!}<span>{!! Session::get('message') !!}</span>
                            @endif

                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    </div>
                    @endif



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
                                                <div class='d-flex justify-content-end align-items-center'>
                                                    <input type="submit" class="btn submitAdminBtn mt-4" name="bulk_submit" value="{{ trans('langSubmit') }}">
                                                    <input type="hidden" id="selectedcbids" name="selectedcbids" value="">
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                    <div class='@if(isset($module_id) and $module_id) form-content-modules @else col-lg-6 col-12 @endif d-none d-lg-block'>
                                        <img class='form-image-modules' src='{!! get_form_image() !!}' alt='form-image'>
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
