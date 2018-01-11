@extends('layouts.default')

@section('content')
    {!! $action_bar !!}

    <div class="row">
        <div class="col-xs-12">
            <div class="table-responsive">
                <table id='request_table_{{ $course_id }}' class='table-default'>
                    <thead>
                        <tr class='list-header'>
                            <th>{{ trans('langRequest') }}</th>
                            <th>{{ trans('langOpenedOn') }}</th>
                            <th>{{ trans('langUpdatedOn') }}</th>
                            <th class='text-center'><span class='fa fa-cogs'></span></th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
    <script>
        $(function() {
           var oTable = $('#request_table_{{ $course_id }}').DataTable({
                'bStateSave': true,
                'bProcessing': true,
                'bServerSide': true,
                'sScrollX': true,
                'fnDrawCallback': function(oSettings) {
                    tooltip_init();
                    popover_init();
                },
                'sAjaxSource': '{{ $listUrl }}',
                'aLengthMenu': [
                   [10, 25, 100 , -1],
                   [10, 25, 100, '{{ trans('langAllOfThem') }}']
               ],
                'sPaginationType': 'full_numbers',
                'bSort': true,
                'aaSorting': [[0, 'desc']],
                'aoColumnDefs': [{'bSortable': true, 'aTargets':[-1]}, {'bSortable': false, 'aTargets': [ 1 ] }],
                'oLanguage': {
                       'sLengthMenu':   '{{ trans('langDisplay') . ' _MENU_ ' . trans('langResults2') }}',
                       'sZeroRecords':  '{{ trans('langNoResult') }}',
                       'sInfo':         '{{ trans('langDisplayed') . ' _START_ ' . trans('langTill') .
                                            ' _END_ ' . trans('langFrom2') . ' _TOTAL_ ' . trans('langTotalResults') }}',
                       'sInfoEmpty':    '{{ trans('langDisplayed') . ' 0 ' . trans('langTill') . ' 0 ' .
                                            trans('langFrom2') . ' 0 ' . trans('langResults2') }}',
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
            $('.dataTables_filter input').attr({
                style: 'width: 200px',
                class:'form-control input-sm',
                placeholder: '{{ trans('langTitle') . ', ' . trans('langUser') }}'});
        });
    </script>
@endsection
