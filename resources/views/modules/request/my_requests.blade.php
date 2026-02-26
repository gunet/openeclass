@extends('layouts.default')

@section('content')

<div class="col-12 main-section">
<div class='{{ $container }} main-container'>
        <div class="row m-auto">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    @include('layouts.partials.legend_view')

                    {!! $action_bar !!}

                    @include('layouts.partials.show_alert') 
                    
                    <div class="col-12">
                        <div class="table-responsive">
                            <table id='request_table_{{ $course_id }}' class='table table-default'>
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
                                            <label class='label-container' aria-label="{{ trans('langSelect') }}">
                                                
                                                    <input type='checkbox' class='form-control' id='closedRequests'>
                                                    <span class='checkmark'></span>
                                                    {{ trans('langShowClosedRequests') }}
                                                
                                            </label>
                                        </th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                    
                
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
        $('.dataTables_filter input mb-3').attr({
            style: 'width: 200px',
            class:'form-control input-sm'
        });
        $('.dataTables_filter label').attr('aria-label', '{{ trans('langSearch') }}'); 
        $(document).on('change', '#closedRequests', function (e) {
            oTable.ajax.reload();
        });
        $(document).on( 'click','.delete_btn', function (e) {
            e.preventDefault();
            var row_id = $(this).data('id');

            // bootbox.confirm('{{ js_escape(trans('langConfirmDelete')) }}', function(result) {
            //     if (result) {
            //         $.ajax({
            //             type: 'POST',
            //             url: '{{ $deleteUrl }}',
            //             datatype: 'json',
            //             data: {
            //                 id: row_id
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
            //     }
            // });

            c({ 
                closeButton: false,
                title: "<div class='icon-modal-default'><i class='fa-regular fa-trash-can fa-xl Accent-200-cl'></i></div><h2 class='modal-title-default text-center mb-0'>{{ js_escape(trans('langConfirmDelete')) }}</h2>",
                message: "<p class='text-center'>{{ js_escape(trans('langConfirmDelete')) }}</p>",
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
                            url: '{{ $deleteUrl }}',
                            datatype: 'json',
                            data: {
                                id: row_id
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
                    }
                }
            });     


        });
    });
</script>

@endsection
