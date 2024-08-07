@extends('layouts.default')

@push('head_scripts')
    <script type='text/javascript'>
        $(document).ready(function() {

            $(document).on('click', '.forDelete', function(e) {
                e.preventDefault();
                idDelete = $(this).data('id');
                idOrder = $(this).data('order');
               
                // bootbox.confirm('{{ trans('langConfirmDelete') }}', function(result) {
                //     if (result) {

                //         $.ajax({
                //             type: 'post',
                //             data: {
                //                 toDelete: idDelete,
                //                 oldOrder: idOrder
                //             },
                //             success: function() {

                //                 $('.indexing').each(function (i){
                //                     $(this).html(i+1);
                //                 });

                //                 $('.tooltip').remove();


                //                 location.reload();
                //             }
                //         });
                //     }
                // });


                bootbox.confirm({ 
                    closeButton: false,
                    title: "<div class='icon-modal-default'><i class='fa-regular fa-trash-can fa-xl Accent-200-cl'></i></div><div class='modal-title-default text-center mb-0'>{{ js_escape(trans('langConfirmDelete')) }}</div>",
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

                                    $('.indexing').each(function (i){
                                        $(this).html(i+1);
                                    });

                                    $('.tooltip').remove();


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
                Sortable.create(orderTexts, {
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

                    @include('layouts.partials.show_alert') 

                    @if ($modify || $new)
                        
                        
                        <div class='col-lg-6 col-12'>
                            <div class='form-wrapper form-edit border-0 px-0'>
                                <form role='form' class='form-horizontal' method='post' action='{{ $_SERVER['SCRIPT_NAME'] }}'>

                                    @if($modify) <input type='hidden' name='id' value='{{$textModify->id}}'> @endif

                                    <div class='form-group'>
                                        <label for='question' class='col-sm-12 control-label-notes'>{{ trans('langTitle') }}</label>
                                        <div class='col-sm-12'>
                                            <input id='question' class='form-control' placeholder="{{ trans('langTitle') }}" type='text' name='textTitle' value="{{ $new ? '' : $textModify->title }}"/>
                                        </div>
                                    </div>
                                    <div class='form-group mt-4'>
                                        <label for='content' class='col-sm-12 control-label-notes'>{{ trans('langCont') }}</label>
                                        <div class='col-sm-12'>{!! $editor !!}</div>
                                    </div>
                                    <div class='form-group mt-4'>
                                        <label for='lang_selection' class='col-sm-12 control-label-notes'>{{ trans('langLanguage') }}</label>
                                        <div class='col-sm-12'>{!! $lang_select_options !!}</div>
                                    </div>


                                    <div class='form-group mt-4'>
                                        <label for='type' class='col-sm-12 control-label-notes'>{{ trans('langType') }}</label>
                                        <div class='col-sm-12'>
                                            <select class='form-select' id='type' name='type'>
                                                <option value="1" {{ $modify && $textModify->type == 1 ? 'selected' : '' }}>{{ trans('langText') }}</option>
                                                <option value="2" {{ $modify && $textModify->type == 2 ? 'selected' : '' }}>Testimonial</option>
                                            </select>
                                        </div>
                                    </div>

                                   
                                    <div class='form-group mt-5'>
                                        <div class='col-12 d-flex justify-content-end align-items-center gap-2'>
                                            <button type="submit" class="btn submitAdminBtn" name="{{ $new? "submitText" : "modifyText" }}" value="{{ trans('submitBtnValue') }}">{{ trans('langSave') }}</button>
                                            <a href="{{ $_SERVER['SCRIPT_NAME'] }}" class="btn cancelAdminBtn">{{ trans('langCancel') }}</a>

                                        </div>
                                    </div>

                                    

                                </form>
                               
                            </div>
                        </div>
                        <div class='col-lg-6 col-12 d-none d-md-none d-lg-block text-end'>
                        <img class='form-image-modules' src='{!! get_form_image() !!}' alt="{{ trans('langImgFormsDes') }}">
                        </div>
                        
                    @else
                            @if($texts)
                                <div class='col-12'>
                                    <div id='orderTexts'>
                                        @foreach($texts as $text)
                                            <div class='card panelCard px-lg-4 py-lg-3 mb-4' data-id='{{ $text->id }}'>
                                                <div class='card-header border-0 d-flex justify-content-between align-items-center gap-3 flex-wrap'>
                                                    
                                                        
                                                        <h3>
                                                            {!! $text->title !!}
                                                            @if($text->type == 2) <span>(Testimonial)</span> @endif
                                                        </h3>
                                                        
                                                        <div class='d-flex gap-3'>
                                                            <a href='{{$urlAppend}}modules/admin/homepageTexts_create.php?homepageText=modify&id={{$text->id}}' aria-label="{{trans('langEdit')}}">
                                                                <span class='fa fa-edit' data-bs-toggle='tooltip' data-bs-placement='top' title='{{trans('langEdit')}}'></span>
                                                            </a>
                                                            <a href='javascript:void(0);' aria-label='{{ trans('langReorder') }}'><span class='fa fa-arrows' data-bs-toggle='tooltip' data-bs-placement='top' title='{{ trans('langReorder') }}'></span></a>
                                                            <a class='forDelete link-delete' href='javascript:void(0);' data-id='{{ $text->id }}' data-order='{{ $text->order }}' aria-label='{{ trans('langDelete') }}'><span class='fa-solid fa-xmark' data-bs-toggle='tooltip' data-bs-placement='top' title='{{ trans('langDelete') }}'></span></a>
                                                        </div>
                                                    
                                                    
                                                </div>
                                                <div class='card-body'>
                                                    {!! $text->body !!}
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @else
                               <div class='col-12'>
                                   <div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>{{trans('langNoInfoAvailable')}}</span></div>
                               </div>
                            @endif
                        
                    @endif

                
        </div>
    </div>
  
</div>


@endsection
