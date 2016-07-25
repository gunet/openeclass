@extends('layouts.default')

@push('head_scripts')
<script src="/js/datatables/media/js/jquery.dataTables.min.js"></script>
<script src="/js/trunk8.js"></script>
<script type='text/javascript'>
    $(document).ready(function() {

        var langEmptyGroupName = "{{ trans('langEmptyAnTitle') }}";

        var oTable = $('#ann_table{{ $course_id }}').DataTable ({
            @if ($is_editor)
            'aoColumnDefs':[{'sClass':'option-btn-cell',
                'aTargets':[-1]}],
            @endif
            'bStateSave': true,
            'bProcessing': true,
            'bServerSide': true,
            'sScrollX': true,
            'responsive': true,
            'searchDelay': 1000,
            'sAjaxSource': '{{ $_SERVER['REQUEST_URI'] }}',
            'aLengthMenu': [
                [10, 15, 20 , -1],
                [10, 15, 20, '{{ trans('langAllOfThem') }}'] // change per page values here
            ],
            'fnDrawCallback': function( oSettings ) {
                popover_init();
                tooltip_init();
                $('.table_td_body').each(function() {
                    $(this).trunk8({
                        lines: '3',
                        fill: '&hellip;<div class="clearfix"></div><a style="float:right;" href="{{ $_SERVER['SCRIPT_NAME'] }}?course={{ $course_code }}&an_id='+ $(this).data('id')+'">{{ trans('langMore') }}</div>'
                    })
                });
                $('#ann_table{{ $course_id }}_filter label input').attr({
                    class : 'form-control input-sm',
                    placeholder : '{{ trans('langSearch') }}...'
                });
            },
            'sPaginationType': 'full_numbers',
            'bSort': false,
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
            var row_id = $(this).data('id');
            bootbox.confirm('".js_escape($langSureToDelAnnounce)."', function(result) {
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
                        url: '{$urlAppend}/modules/search/idxasync.php'
                    });
                }
            });
        });
        $(document).on( 'click','.vis_btn', function (g) {
            g.preventDefault();
            var vis = $(this).data('vis');
            var row_id = $(this).data('id');
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
                url: '{$urlAppend}/modules/search/idxasync.php'
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
        fill: '&hellip; <a class="read-more" href="#">{{ js_escape($GLOBALS['showall']) }}</a>',
    });

        $(document).on('click', '.read-more', function (event) {
            $(this).parent().trunk8('revert').append(' <a class="read-less" href="#">{{ js_escape($GLOBALS['shownone']) }}</a>');
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
<link rel='stylesheet' type='text/css' href="/js/datatables/media/css/jquery.dataTables.css" />
@endpush

@section('content')

    {!! $action_bar !!}

    <div class="row">
        <div class="col-xs-12">
            <div class="table-responsive">
                <table id='ann_table{{ $course_id }}' class='table-default'>
                    <thead>
                        <tr class='list-header'>
                            <th>{{ trans('langAnnouncement') }}</th>
                            <th>{{ trans('langDate') }}</th>
                            @if ($is_editor)
                                <th>{{ trans('langNewBBBSessionStatus') }}</th>
                                <th class='text-center'><i class='fa fa-cogs'></i></th>
                            @endif
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
