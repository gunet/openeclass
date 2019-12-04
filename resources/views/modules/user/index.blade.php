@extends('layouts.default')

@push('head_scripts')
<script type='text/javascript'>
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
            sScrollX: true,
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
                { sClass: 'option-btn-cell', aTargets: [ -1 ] },
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
            var row_id = $(this).data('id');
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
                    class: 'form-control input-sm',
                    placeholder: '{{ js_escape(trans('langName') . ', Username, Email') }}' });
        $('.success').delay(3000).fadeOut(1500);
    });
</script>
@endpush

@section('content')

    {!! $action_bar !!}

    <table id='users_table_{{ $course_code }}' class='table-default'>
        <thead>
            <tr>
              <th>{{ trans('langSurnameName') }}</th>
              <th class='text-center'>{{ trans('langRole') }}</th>
              <th class='text-center'>{{ trans('langGroup') }}</th>
              <th class='text-center' width='80'>{{ trans('langRegistrationDate') }}</th>
              <th class='text-center'>{!! icon('fa-gears') !!}</th>
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

@endsection
