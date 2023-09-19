@extends('layouts.default')

@section('content')

<div class="col-12 main-section">
<div class='{{ $container }} py-lg-0'>
        <div class="course-wrapper d-lg-flex align-items-lg-strech w-100">

            <div id="background-cheat-leftnav" class="col_sidebar_active d-flex justify-content-start align-items-strech ps-lg-0 pe-lg-0"> 
                <div class="d-none d-sm-block d-sm-none d-md-block d-md-none d-lg-block ContentLeftNav">
                    @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                </div>
            </div>

            <div class="col_maincontent_active">
                    
                <div class="row">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])


                    <div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="collapseTools" aria-labelledby="offcanvasExampleLabel">
                        <div class="offcanvas-header">
                            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                        </div>
                        <div class="offcanvas-body">
                            @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                        </div>
                    </div>


                    @include('layouts.partials.legend_view')

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
                            
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    </div>
                    @endif

                     {!! $action_bar !!}
                     <div class='d-lg-flex gap-4 mt-4'>
                        <div class='flex-grow-1'>
                        <div class='form-wrapper form-edit rounded'>
                           
                            <form class='form-horizontal' action='{{ $targetUrl }}' method='post'>
                                <div class='form-group'>
                                    <div class='col-sm-12'>
                                        <label class='control-label-notes'>{{ trans('langCreator') }}: 
                                            <span class="text-black-50 form-control-static">{{ $creatorName }}</span>

                                        </label>
                                    </div>
                                </div>

                                

                                <div class='row'>
                                    <div class='col-md-6 col-12'>
                                        @if ($request_types)
                                            <div class='form-group mt-4'>
                                                <label for='requestType' class='col-sm-6 control-label-notes'>{{ trans('langType') }}</label>
                                                <div class='col-sm-12'>
                                                    <select class='form-select' name='requestType' id='requestType'>
                                                        <option value='0'>{{ trans('langRequestBasicType') }}</option>
                                                        @foreach ($request_types as $type)
                                                            <option value='{{ $type->id }}'>{{ getSerializedMessage($type->name) }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                    <div class='col-md-6 col-12'>
                                        <div class='form-group mt-4'>
                                            <label for='requestTitle' class='col-sm-6 control-label-notes'>{{ trans('langTitle') }}</label>
                                            <div class='col-sm-12'>
                                                <input type='text' class='form-control' placeholder="{{ trans('langTitle') }}..." id='requestTitle' name='requestTitle' required>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                

                                <div class='form-group mt-4'>
                                    <label for='requestDescription' class='col-sm-6 control-label-notes'>{{ trans('langDescription') }}</label>
                                    <div class='col-sm-12'>
                                        {!! $descriptionEditor !!}
                                    </div>
                                </div>

                             

                                <div class='row'>
                                    <div class='col-md-6 col-12'>
                                        <div class='form-group mt-4'>
                                            <label for='assignTo' class='col-sm-6 control-label-notes'>{{ trans("m['WorkAssignTo']") }}:</label>
                                            <div class='col-sm-12'>
                                                <select class='form-select' name='assignTo[]' multiple id='assignTo'>
                                                    @foreach ($course_users as $cu)
                                                        <option value='{{ $cu->user_id }}'>{{$cu->name}} ({{$cu->email}})</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class='col-md-6 col-12'>
                                        <div class='form-group mt-4'>
                                            <label for='requestWatchers' class='col-sm-6 control-label-notes'>{{ trans('langWatchers') }}:</label>
                                            <div class='col-sm-12'>
                                                <select class='form-select' name='requestWatchers[]' multiple id='requestWatchers'>
                                                    @foreach ($course_users as $cu)
                                                        @if ($uid != $cu->user_id)
                                                            <option value='{{ $cu->user_id }}'>{{$cu->name}} ({{$cu->email}})</option>
                                                        @endif
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                @if ($request_types)
                                    <div class="mt-4"></div>
                                    @foreach ($request_types as $type)
                                        @include('modules.request.extra_fields',
                                            ['type_name' => $type->name,
                                            'type_id' => $type->id,
                                            'fields_info' => $request_fields[$type->id]])
                                    @endforeach
                                @endif

                                

                                <div class='form-group mt-4'>
                                    <div class='col-12'>
                                        <div class='checkbox'>
                                            <label class='label-container'>
                                                <input type='checkbox' name='send_mail' value='on' checked><span class='checkmark'></span> {{ trans('langSendInfoMail') }}
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                

                                <div class='form-group mt-5'>
                                    <div class='col-12 d-flex justify-content-end align-items-center'>
                                       
                                            
                                                 <button class='btn submitAdminBtn' type='submit'>{{ trans('langSubmit') }}</button>
                                          
                                          
                                                 <a class='btn cancelAdminBtn ms-1' href='{{ $backUrl }}'>{{ trans('langCancel') }}</a>
                                          
                                       
                                       
                                       
                                    </div>
                                </div>

                                {!! generate_csrf_token_form_field() !!}
                            </form>
                        </div>
                    </div><div class='d-none d-lg-block'>
                            <img class='form-image-modules' src='{{$urlAppend}}template/modern/img/form-image.png' alt='form-image'>
                        </div>
                        </div>
                        


                </div>
            </div>


        </div>
   
</div>
</div>


<script>$(function () {
    $('#requestWatchers').select2();
    $('#assignTo').select2();
    @if ($request_types)
        $('#requestType').change(function () {
            var type_id = $(this).val();
            $('.extra-fields-set').hide();
            $('#fields_' + type_id).show();
        }).change();
    @endif
})</script>
@endsection
