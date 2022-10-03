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

            e.preventDefault();
            e.stopPropagation();

            bootbox.dialog({
                message: message,
                title: title,
                buttons: {
                    cancel_btn: {
                        label: cancel_text,
                        className: "btn-secondary"
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

    $(document).ready(function(){
        var langEmptyGroupName = "{{ trans('langEmptyAnTitle') }}";

        var oTable = $('#ann_table{{ $course_id }}').DataTable ({
            @if ($is_editor)
            'aoColumnDefs':[{'sClass':'option-btn-cell text-center',
                'aTargets':[-1]}],
            @endif
            bStateSave: true,
            bProcessing: true,
            bServerSide: true,
            sScrollX: true,
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
                $('#ann_table{{ $course_id }}_filter label input').attr({
                    class : 'form-control input-sm mb-3 ms-0',
                    placeholder : '{{ trans('langSearch') }}...'
                });
            },
            sPaginationType: 'full_numbers',
            bSort: false,
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
            bootbox.confirm('{{ js_escape(trans('langSureToDelAnnounce')) }}', function(result) {
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

<div class="pb-lg-3 pt-lg-3 pb-0 pt-0">

    <div class="container-fluid main-container">

        <div class="row rowMedium">

            <div id="background-cheat-leftnav" class="col-xl-2 col-lg-3 col-md-0 col-sm-0 col-0 justify-content-center col_sidebar_active"> 
                <div class="d-none d-sm-block d-sm-none d-md-block d-md-none d-lg-block">
                    @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                </div>
            </div>

            <div class="col-xl-10 col-lg-9 col-md-12 col-sm-12 col-12 justify-content-center col_maincontent_active">

                <div class="row p-lg-5 p-md-5 ps-1 pe-2 pt-5 pb-5">

                    <nav class="navbar navbar-expand-lg navrbar_menu_btn">
                        <button type="button" id="menu-btn" class="d-none d-sm-block d-sm-none d-md-block d-md-none d-lg-block btn btn-primary menu_btn_button">
                            <i class="fas fa-align-left"></i>
                            <span></span>
                        </button>
                        <a class="btn btn-primary btn-sm d-lg-none" type="button" data-bs-toggle="offcanvas" href="#collapseTools" role="button" aria-controls="collapseTools">
                            <i class="fas fa-tools"></i>
                        </a>
                    </nav>

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])


                    @include('layouts.partials.legend_view',['is_editor' => $is_editor, 'course_code' => $course_code])

                    <div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="collapseTools" aria-labelledby="offcanvasExampleLabel">
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
                    <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 mt-5'>
                        <p class="alert {{ Session::get('alert-class', 'alert-info') }} alert-dismissible fade show" role="alert">
                            @if(is_array(Session::get('message')))
                                @php $messageArray = array(); $messageArray = Session::get('message'); @endphp
                                @foreach($messageArray as $message)
                                    {!! $message !!}
                                @endforeach
                            @else
                                {!! Session::get('message') !!}
                            @endif
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </p>
                    </div>
                    @endif

                    {!! $action_bar !!}

                    @if ($showSubscribeWarning)
                        <div class='col-sm-12'>
                            <div class='alert alert-warning'>
                                {{ trans('langNoUserEmailNotification') }}
                                (<a href='{{ $subscribeUrl }}'>{{ trans('langModify') }}</a>)
                            </div>
                        </div>
                    @endif

                    
                    <div class="table-responsive">
                        <table id='ann_table{{ $course_id }}' class='table-default announcements_table'>
                            <thead>
                                <tr class='notes_thead'>
                                    <th class='text-white'>{{ trans('langAnnouncement') }}</th>
                                    <th class='text-white'>{{ trans('langDate') }}</th>
                                    @if ($is_editor)
                                        <th class='text-white'>{{ trans('langNewBBBSessionStatus') }}</th>
                                        <th class='text-white text-center'><i class='fa fa-cogs'></i></th>
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
@endsection
