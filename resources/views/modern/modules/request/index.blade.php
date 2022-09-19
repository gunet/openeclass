@extends('layouts.default')

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
                        
                       
                        <a class="btn btn-primary btn-sm d-lg-none mr-auto" type="button" data-bs-toggle="offcanvas" href="#collapseTools" role="button" aria-controls="collapseTools" style="margin-top:-10px;">
                            <i class="fas fa-tools"></i>
                        </a>
                    </nav>

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])


                    <div class="offcanvas offcanvas-start d-lg-none mr-auto" tabindex="-1" id="collapseTools" aria-labelledby="offcanvasExampleLabel">
                        <div class="offcanvas-header">
                            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                        </div>
                        <div class="offcanvas-body">
                            @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                        </div>
                    </div>


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

                    <div class='col-sm-12'>
                        <div class="table-responsive">
                            <table id='request_table_{{ $course_id }}' class='table table-default'>
                                <thead>
                                    <tr class='notes_thead'>
                                        <th class='text-white'>{{ trans('langRequest') }}</th>
                                        <th class='text-white'>{{ trans('langNewBBBSessionStatus') }}</th>
                                        <th class='text-white'>{{ trans('langOpenedOn') }}</th>
                                        <th class='text-white'>{{ trans('langUpdatedOn') }}</th>
                                        <th class='text-white text-center'><span class='fa fa-cogs'></span></th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan='5'>
                                            <div class='d-inline-flex align-items-center'>
                                                <label class='pe-2'>{{ trans('langShowClosedRequests') }}:</label>
                                                <input type='checkbox' id='closedRequests'>
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
        $('.menu-popover').popover({html:true}).on('click', togglePopover).on('blur', hidePopover);
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
            if($(this).is(":checked")){
                 $('#closedRequests').attr( 'checked', true );
            }else{
                $('#closedRequests').attr( 'checked', false );
            }
            oTable.ajax.reload();
        });
        $(document).on( 'click','.delete_btn', function (e) {
            e.preventDefault();
            var row_id = this.id;
            console.log(row_id);
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
