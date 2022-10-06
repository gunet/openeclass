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
                        
                       
                        <a class="btn btn-primary btn-sm d-lg-none" type="button" data-bs-toggle="offcanvas" href="#collapseTools" role="button" aria-controls="collapseTools">
                            <i class="fas fa-tools"></i>
                        </a>
                    </nav>

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])


                    <div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="collapseTools" aria-labelledby="offcanvasExampleLabel">
                        <div class="offcanvas-header">
                            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                        </div>
                        <div class="offcanvas-body">
                            @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                        </div>
                    </div>


                    @include('layouts.partials.legend_view',['is_editor' => $is_editor, 'course_code' => $course_code])



                    

                    @if(Session::has('message'))
                    <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 all-alerts'>
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

                     {!! $action_bar !!}
                    <div class='col-12'>
                        <div class='form-wrapper shadow-sm p-3 rounded'>
                           
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
                                            <div class='form-group mt-3'>
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
                                        <div class='form-group mt-3'>
                                            <label for='requestTitle' class='col-sm-6 control-label-notes'>{{ trans('langTitle') }}</label>
                                            <div class='col-sm-12'>
                                                <input type='text' class='form-control' placeholder="{{ trans('langTitle') }}..." id='requestTitle' name='requestTitle' required>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                

                                <div class='form-group mt-3'>
                                    <label for='requestDescription' class='col-sm-6 control-label-notes'>{{ trans('langDescription') }}:</label>
                                    <div class='col-sm-12'>
                                        {!! $descriptionEditor !!}
                                    </div>
                                </div>

                             

                                <div class='row'>
                                    <div class='col-md-6 col-12'>
                                        <div class='form-group mt-3'>
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
                                        <div class='form-group mt-3'>
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
                                    <div class="mt-3"></div>
                                    @foreach ($request_types as $type)
                                        @include('modules.request.extra_fields',
                                            ['type_name' => $type->name,
                                            'type_id' => $type->id,
                                            'fields_info' => $request_fields[$type->id]])
                                    @endforeach
                                @endif

                                

                                <div class='form-group mt-3'>
                                    <div class='col-sm-10 col-sm-offset-2'>
                                        <div class='checkbox'>
                                            <label>
                                                <input type='checkbox' name='send_mail' value='on' checked> {{ trans('langSendInfoMail') }}
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                

                                <div class='form-group mt-3'>
                                    <div class='col-12'>
                                        <div class='row'>
                                            <div class='col-6'>
                                                 <button class='btn btn-primary btn-sm submitAdminBtn w-100' type='submit'>{{ trans('langSubmit') }}</button>
                                            </div>
                                            <div class='col-6'>
                                                 <a class='btn btn-secondary btn-sm cancelAdminBtn w-100' href='{{ $backUrl }}'>{{ trans('langCancel') }}</a>
                                            </div>
                                        </div>
                                       
                                       
                                    </div>
                                </div>

                                {!! generate_csrf_token_form_field() !!}
                            </form>
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