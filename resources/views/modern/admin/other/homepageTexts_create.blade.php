@extends('layouts.default')

@push('head_scripts')
    <script type='text/javascript'>
        $(document).ready(function() {

            $(document).on('click', '.forDelete', function(e) {
                e.preventDefault();
                idDelete = $(this).data('id');
                idOrder = $(this).data('order');
               
                bootbox.confirm('{{ trans('langConfirmDelete') }}', function(result) {
                    if (result) {

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

<div class="pb-lg-3 pt-lg-3 pb-0 pt-0">

    <div class="container-fluid main-container">

        <div class="row rowMedium">

            <div class="col-12 justify-content-center col_maincontent_active_Homepage">
                    
                <div class="row p-lg-5 p-md-5 ps-1 pe-1 pt-5 pb-5">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    @include('layouts.partials.legend_view',['is_editor' => $is_editor, 'course_code' => $course_code])

                    @if(Session::has('message'))
                    <div class='col-12 all-alerts'>
                        <div class="alert {{ Session::get('alert-class', 'alert-info') }} alert-dismissible fade show" role="alert">
                            @if(is_array(Session::get('message')))
                                @php $messageArray = array(); $messageArray = Session::get('message'); @endphp
                                @foreach($messageArray as $message)
                                    {!! $message !!}
                                @endforeach
                            @else
                                {!! Session::get('message') !!}
                            @endif
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    </div>
                    @endif

                    {!! $action_bar !!}
                    
                    @if ($modify || $new)
                        
                        <div class='col-lg-6 col-12 d-none d-md-none d-lg-block'>
                            <div class='col-12 h-100 left-form'></div>
                        </div>
                        <div class='col-lg-6 col-12'>
                            <div class='form-wrapper form-edit rounded'>
                                <form role='form' class='form-horizontal' method='post' action='{{ $_SERVER['SCRIPT_NAME'] }}'>

                                    @if($modify) <input type='hidden' name='id' value='{{$textModify->id}}'> @endif

                                    <div class='form-group'>
                                        <label for='question' class='col-sm-12 control-label-notes'>{{ trans('langTitle') }}</label>
                                        <div class='col-sm-12'>
                                            <input class='form-control' placeholder="{{ trans('langTitle') }}" type='text' name='textTitle' value="{{ $new ? '' : $textModify->title }}"/>
                                        </div>
                                    </div>
                                    <div class='form-group mt-4'>
                                        <label for='answer' class='col-sm-12 control-label-notes'>{{ trans('langCont') }}</label>
                                        <div class='col-sm-12'>{!! $editor !!}</div>
                                    </div>
                                    <div class='form-group mt-4'>
                                        <label for='answer' class='col-sm-12 control-label-notes'>{{ trans('langLanguage') }}</label>
                                        <div class='col-sm-12'>{!! $lang_select_options !!}</div>
                                    </div>

                                   
                                    <div class='form-group mt-5'>
                                        <div class='col-12 d-flex justify-content-center align-items-center'>

                                                <button type="submit" class="btn submitAdminBtn" name="{{ $new? "submitText" : "modifyText" }}" value="{{ trans('submitBtnValue') }}">{{ trans('langSave') }}</button>
                                                <a href="{{ $_SERVER['SCRIPT_NAME'] }}" class="btn cancelAdminBtn ms-1">{{ trans('langCancel') }}</a>

                                        </div>
                                    </div>
                                </form>
                               
                            </div>
                        </div>
                        
                    @else
                            @if($texts)
                                <div class='col-12'>
                                    <div id='orderTexts'>
                                        @foreach($texts as $text)
                                            <div class='panel panel-default mb-3' data-id='{{ $text->id }}'>
                                                <div class='panel-heading'>
                                                    <div class='row'>
                                                        <div class='col-10'>
                                                            {!! $text->title !!}
                                                        </div>
                                                        <div class='col-2 text-end'>
                                                            <a href='{{$urlAppend}}modules/admin/homepageTexts_create.php?homepageText=modify&id={{$text->id}}'>
                                                                <span class='fa fa-edit text-primary pe-1' data-bs-toggle='tooltip' data-bs-placement='top' title='{{trans('langEdit')}}'></span>
                                                            </a>
                                                            <a href='javascript:void(0);'><span class='fa fa-arrows text-dark pe-1' data-bs-toggle='tooltip' data-bs-placement='top' title='{{ trans('langReorder') }}'></span></a>
                                                            <a class='forDelete' href='javascript:void(0);' data-id='{{ $text->id }}' data-order='{{ $text->order }}'><span class='fa fa-times text-danger' data-bs-toggle='tooltip' data-bs-placement='top' title='{{ trans('langDelete') }}'></span></a>
                                                        </div>
                                                    </div>
                                                    
                                                </div>
                                                <div class='panel-body'>
                                                    {!! $text->body !!}
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @else
                               <div class='col-12'>
                                   <div class='alert alert-warning'>{{trans('langNoInfoAvailable')}}</div>
                               </div>
                            @endif
                        
                    @endif

                </div>
            </div>
        </div>
    </div>
</div>


@endsection
