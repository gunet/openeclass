@extends('layouts.default')

@section('content')
    {!! $action_bar !!}

    <div class="row">
        <div class="col-xs-12">
            <div class="table">
                <table id='request_table_{{ $course_id }}' class='table'>
                    <thead>
                        <tr class='list-header'>
                            <th>{{ trans('langRequest') }}</th>
                            <th>{{ trans('langNewBBBSessionStatus') }}</th>
                            <th>{{ trans('langOpenedOn') }}</th>
                            <th>{{ trans('langUpdatedOn') }}</th>
                            <th class='text-center'><span class='fa fa-cogs'></span></th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                    <tfoot>
                        <tr>
                            <th colspan='5'>
                                <div class='form-inline'>
                                    <label>{{ trans('langShowClosedRequests') }}:
                                        <input type='checkbox' class='form-control' id='closedRequests'>
                                    </label>
                                </div>
                            </th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
    <script>
        $(function() {
           var oTable = $('#request_table_{{ $course_id }}').DataTable({
               ajax: {
                   url: '{{ $listUrl }}',
                   data: function (data) {
                        data.show_closed = $('#closedRequests').prop('checked');
                   }
               },
               order: [[0, 'desc']],
               lengthMenu: [
                   [10, 25, 100 , -1],
                   [10, 25, 100, '{{ trans('langAllOfThem') }}']
               ],
               columns: [
                   { searchable: true },
                   { searchable: true, orderable: true },
                   { searchable: false, orderable: true },
                   { searchable: false, orderable: true },
                   { searchable: false, orderable: false }
               ],
               stateSave: true,
               processing: true,
               serverSide: true,
               scrollX: true,
               drawCallback: function(settings) {
                   tooltip_init();
                   popover_init();
               },
               paginationType: 'full_numbers',
               language: {
                   lengthMenu: '{{ trans('langDisplay') . ' _MENU_ ' . trans('langResults2') }}',
                   zeroRecords: '{{ trans('langNoResult') }}',
                   info: '{{ trans('langDisplayed') . ' _START_ ' . trans('langTill') .
                             ' _END_ ' . trans('langFrom2') . ' _TOTAL_ ' . trans('langTotalResults') }}',
                   infoEmpty: '{{ trans('langDisplayed') . ' 0 ' . trans('langTill') . ' 0 ' .
                                  trans('langFrom2') . ' 0 ' . trans('langResults2') }}',
                   infoFiltered: '',
                   infoPostFix: '',
                   search: '{{ trans('langSearch') . ': ' }}',
                   searchPlaceholder: '{{ trans('langTitle') . ', ' . trans('langUser') }}',
                   paginate: {
                       first:    '&laquo;',
                       previous: '&lsaquo;',
                       next:     '&rsaquo;',
                       last:     '&raquo;'
                   }
               }
            });
            $('.dataTables_filter input').attr({
                style: 'width: 200px',
                class:'form-control input-sm'
            });
            $(document).on('change', '#closedRequests', function (e) {
                oTable.ajax.reload();
            });
        });
    </script>
@endsection
