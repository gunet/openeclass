@extends('layouts.default')

@section('content')

<div class="pb-lg-3 pt-lg-3 pb-0 pt-0">

    <div class="container-fluid main-container">

        <div class="row rowMedium">

            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 justify-content-center col_maincontent_active_Homepage">

                <div class="row p-lg-5 p-md-5 ps-1 pe-2 pt-5 pb-5">


                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    @include('layouts.partials.legend_view',['is_editor' => $is_editor, 'course_code' => $course_code])

                    {!! $action_bar !!}

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

                    
                    <div class="col-12">
                        <div class="table-responsive">
                            <table id='request_table_{{ $course_id }}' class='table'>
                                <thead>
                                    <tr class='list-header'>
                                        <th class='text-white'>{{ trans('langRequest') }}</th>
                                        <th class='text-white'>{{ trans('langNewBBBSessionStatus') }}</th>
                                        <th class='text-white'>{{ trans('langOpenedOn') }}</th>
                                        <th class='text-white'>{{ trans('langUpdatedOn') }}</th>
                                        <th class='text-center text-white'><span class='fa fa-cogs'></span></th>
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
        $(document).on('change', '#closedRequests', function (e) {
            oTable.ajax.reload();
        });
        $(document).on( 'click','.delete_btn', function (e) {
            e.preventDefault();
            var row_id = $(this).data('id');
            bootbox.confirm('{{ js_escape(trans('langConfirmDelete')) }}', function(result) {
                if (result) {
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
            });
        });
    });
</script>

@endsection
