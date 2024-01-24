
@extends('layouts.default')

@push('head_scripts')
    <script type='text/javascript'>
        $(document).ready(function() {

            $(document).on('click', '.expand:not(.revert)', function(e) {
                e.preventDefault();
                $('.group-section .panel-collapse').addClass('show');
                $(this).toggleClass('revert');
                $(this).children().eq(0).toggleClass('fa-plus-circle').toggleClass('fa-minus-circle');
                $(this).children().eq(1).html('{{ trans('langFaqCloseAll') }}');
            });

            $(document).on('click', '.expand.revert', function(e) {
                e.preventDefault();
                $('.group-section .panel-collapse').removeClass('show');
                $('.group-section .panel-collapse').addClass('hide');
                $(this).toggleClass('revert');
                $(this).children().eq(0).toggleClass('fa-minus-circle').toggleClass('fa-plus-circle');
                $(this).children().eq(1).html('{{ trans('langFaqExpandAll') }}');
            });

            $(document).on('click', '.forDelete', function(e) {
                e.preventDefault();
                idDelete = $(this).data('id');
                idOrder = $(this).data('order');
                elem_rem = $(this).parents('.list-group-item');
                var ids = [];
                $('.group-section .list-group-item').each(function () {
                    ids.push($(this).data('id'));
                });


                // bootbox.confirm('{{ trans('langConfirmDelete') }}', function(result) {
                //     if (result) {

                //         $.ajax({
                //             type: 'post',
                //             data: {
                //                 toDelete: idDelete,
                //                 oldOrder: idOrder
                //             },
                //             success: function() {

                //                 elem_rem.remove();

                //                 $('.indexing').each(function (i){
                //                     $(this).html(i+1);
                //                 });

                //                 $('.tooltip').remove();

                //                 moreDeletes = $('.alert-success').length;

                //                 if (moreDeletes > 0){
                //                     $('.alert-success').html('{{ trans('langFaqDeleteSuccess') }}');
                //                 } else {
                //                     $('.row.action_bar').before('<div class=\'alert alert-success\'><i class=\'fa-solid fa-circle-check fa-lg\'></i><span>{{ trans('langFaqDeleteSuccess') }}</span></div>');
                //                 }

                //                 location.reload();

                //             }
                //         });
                //     }
                // });

                bootbox.confirm({ 
                    closeButton: false,
                    title: "<div class='icon-modal-default'><i class='fa-regular fa-trash-can fa-xl Accent-200-cl'></i></div><h3 class='modal-title-default text-center mb-0'>{{ js_escape(trans('langConfirmDelete')) }}</h3>",
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
                                type: 'post',
                                data: {
                                    toDelete: idDelete,
                                    oldOrder: idOrder
                                },
                                success: function() {

                                    elem_rem.remove();

                                    $('.indexing').each(function (i){
                                        $(this).html(i+1);
                                    });

                                    $('.tooltip').remove();

                                    moreDeletes = $('.alert-success').length;

                                    if (moreDeletes > 0){
                                        $('.alert-success').html('{{ trans('langFaqDeleteSuccess') }}');
                                    } else {
                                        $('.row.action_bar').before('<div class=\'alert alert-success\'><i class=\'fa-solid fa-circle-check fa-lg\'></i><span>{{ trans('langFaqDeleteSuccess') }}</span></div>');
                                    }

                                    location.reload();

                                }
                            });
                        }
                    }
                }); 



            });
        });
    </script>
@endpush

@if (!$modify && !$new)
    @push('head_scripts')
        <script src="{{ $urlServer }}/js/sortable/Sortable.min.js"></script>
        <script type='text/javascript'>
            $(document).ready(function() {
                Sortable.create(accordion, {
                    handle: '.fa-arrows',
                    animation: 150,
                    onEnd: function (evt) {

                        var itemEl = $(evt.item);

                        var idReorder = itemEl.attr('data-id');
                        var prevIdReorder = itemEl.prev().attr('data-id');

                        $.ajax({
                            type: 'post',
                            dataType: 'text',
                            data: {
                                toReorder: idReorder,
                                prevReorder: prevIdReorder,
                            },
                            success: function(data) {
                                $('.indexing').each(function (i){
                                    $(this).html(i+1);
                                });
                            }
                        })
                    }

                });
            });
        </script>
    @endpush
@endif

@section('content')

<div class="col-12 main-section">
<div class='{{ $container }} main-container'>
<div class="row m-auto">


                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    @include('layouts.partials.legend_view')

                    {!! $action_bar !!}

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

                    

                    @if ($modify || $new)

                        
                        <div class='col-lg-6 col-12'>
                            <div class='form-wrapper form-edit border-0 px-0'>

                                <form role='form' class='form-horizontal' method='post' action='{{ $_SERVER['SCRIPT_NAME'] }}'>
                                    <input type='hidden' name='id' value='{{ $id }}'>
                                    <div class='form-group'>
                                        <label for='question' class='col-sm-12 control-label-notes'>{{ trans('langFaqQuestion') }} <sup><small>(<span class='text-danger'>*</span>)</small></sup>:</label>
                                        <div class='col-sm-12'>
                                            @if ($modify)
                                                <input class='form-control' placeholder="{{ trans('langFaqQuestion') }}..." type='text' name='question' value="{{ $faq_mod->title }}" />
                                            @else
                                                <input class='form-control' placeholder="{{ trans('langFaqQuestion') }}..." type='text' name='question' value="" />
                                            @endif
                                        </div>
                                    </div>
                                    <div class='form-group mt-4'>
                                        <label for='answer' class='col-sm-12 control-label-notes'>{{ trans('langFaqAnswer') }} <sup><small>(<span class='text-danger'>*</span>)</small></sup>:</label>
                                        <div class='col-sm-12'>{!! $editor !!}</div>
                                    </div>
                                    <div class='form-group mt-4'>
                                        <div class='col-sm-offset-2 col-sm-10'>
                                            <sup><small>(<span class='text-danger'>*</span>)</small></sup> <small class='text-muted'>{{ trans('langCPFFieldRequired') }}</small>
                                        </div>
                                    </div>
                                    <div class='form-group mt-5'>
                                        <div class='col-12 d-flex justify-content-end align-items-center'>
                                            <button type="submit" class="btn submitAdminBtn" name="{{ $new? "submitFaq" : "modifyFaq" }}" value="{{ trans('submitBtnValue') }}">{{ trans('langSave') }}</button>
                                            <a href="{{ $_SERVER['SCRIPT_NAME'] }}" class="btn cancelAdminBtn ms-1">{{ trans('langCancel') }}</a>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class='col-lg-6 col-12 d-none d-md-none d-lg-block text-end'>
                        <img class='form-image-modules' src='{{$urlAppend}}template/modern/img/form-image.png' alt='form-image'>
                        </div>
                    @else
                        <div class='col-12'>
                            <div class='panel-group group-section' id='accordion' role='tablist' aria-multiselectable='true'>
                                @if (count($faqs) == 0)
                                    <div class='panel list-group-item'>
                                        <div class='text-center text-muted'><em>{{ trans('langFaqNoEntries') }}</em> <br><br> <em>{{ trans('langFaqAddNew') }}</em></div>
                                    </div>
                                @else
                                    @foreach ($faqs as $key => $faq)
                                        <div class='bg-default mb-4 p-3 border-bottom-default' data-id='{{ $faq->id }}'>
                                            <div class='d-flex justify-content-between align-items-center' role='tab' id='heading-{{ $faq->id }}'>
                                                <div>
                                                    <a data-bs-toggle='collapse' href='#faq-{{ $faq->id }}' aria-expanded='true' aria-controls='#{{ $faq->id }}'>
                                                        {{ $key+1 }}. {!! $faq->title !!}
                                                    </a>
                                                </div>
                                                <div>
                                                    <a class='forDelete' href='javascript:void(0);' data-id='{{ $faq->id }}' data-order='{{ $faq->order }}'><span class='fa-solid fa-xmark text-danger float-end p-2' data-bs-toggle='tooltip' data-bs-placement='top' title='{{ trans('langDelete') }}'></span></a>
                                                    <a href='javascript:void(0);'><span class='fa fa-arrows float-end p-2' data-bs-toggle='tooltip' data-bs-placement='top' title='{{ trans('langReorder') }}'></span></a>
                                                    <a href='{{ $_SERVER['SCRIPT_NAME'] }}?faq=modify&id={{ $faq->id }}'><span class='fa fa-pencil-square Primary-500-cl float-end p-2' data-bs-toggle='tooltip' data-bs-placement='top' title='{{ trans('langEdit') }}'></span></a>
                                                </div>
                                            </div>
                                            <div id='faq-{{ $faq->id }}' class='panel-collapse accordion-collapse collapse' role='tabpanel' data-bs-parent='#accordion' aria-labelledby='heading{{ $faq->id }}'>
                                                <div class='accordion-body'>
                                                    <p><strong><u>{{ trans('langFaqAnswer') }}:</u></strong></p>
                                                    {!! $faq->body !!}
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    @endif

                
</div>
</div>
</div>

@endsection
