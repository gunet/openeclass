@extends('layouts.default')

@section('content')


<div class="pb-3 pt-3">

    <div class="container-fluid main-container">

        <div class="row">

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
                        
                       
                        <a class="btn btn-primary d-lg-none mr-auto" type="button" data-bs-toggle="offcanvas" href="#collapseTools" role="button" aria-controls="collapseTools" style="margin-top:-10px;">
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



                    

                    @if(Session::has('message'))
                    <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 mt-5'>
                        <p class="alert {{ Session::get('alert-class', 'alert-info') }} alert-dismissible fade show" role="alert">
                            {{ Session::get('message') }}
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
                                        <label class='control-label-notes'>{{ trans('langcreator') }}: 
                                            <span class="text-black-50 form-control-static">{{ $creatorName }}</span>

                                        </label>
                                        <!-- <p class='form-control-static'>{{ $creatorName }}</p>  -->
                                    </div>
                                </div>

                                <div class="row p-2"></div>

                                @if ($request_types)
                                    <div class='form-group'>
                                        <label for='requestType' class='col-sm-6 control-label-notes'>{{ trans('langType') }}:</label>
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

                                <div class="row p-2"></div>

                                <div class='form-group'>
                                    <label for='requestTitle' class='col-sm-6 control-label-notes'>{{ trans('langTitle') }}:</label>
                                    <div class='col-sm-12'>
                                        <input type='text' class='form-control' id='requestTitle' name='requestTitle' required>
                                    </div>
                                </div>

                                <div class="row p-2"></div>

                                <div class='form-group'>
                                    <label for='requestDescription' class='col-sm-6 control-label-notes'>{{ trans('langDescription') }}:</label>
                                    <div class='col-sm-12'>
                                        {!! $descriptionEditor !!}
                                    </div>
                                </div>

                                <div class="row p-2"></div>

                                <div class='form-group'>
                                    <label for='assignTo' class='col-sm-6 control-label-notes'>{{ trans("m['WorkAssignTo']") }}:</label>
                                    <div class='col-sm-12'>
                                        <select class='form-select' name='assignTo[]' multiple id='assignTo'>
                                            @foreach ($course_users as $cu)
                                                <option value='{{ $cu->user_id }}'>{{$cu->name}} ({{$cu->email}})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="row p-2"></div>

                                <div class='form-group'>
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

                                @if ($request_types)
                                    <div class="row p-2"></div>
                                    @foreach ($request_types as $type)
                                        @include('modules.request.extra_fields',
                                            ['type_name' => $type->name,
                                            'type_id' => $type->id,
                                            'fields_info' => $request_fields[$type->id]])
                                    @endforeach
                                @endif

                                <div class="row p-2"></div>

                                <div class='form-group'>
                                    <div class='col-sm-10 col-sm-offset-2'>
                                        <div class='checkbox'>
                                            <label>
                                                <input type='checkbox' name='send_mail' value='on' checked> {{ trans('langSendInfoMail') }}
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="row p-2"></div>

                                <div class='form-group'>
                                    <div class='col-xs-offset-2 col-xs-10'>
                                        <button class='btn btn-primary' type='submit'>{{ trans('langSubmit') }}</button>
                                        <a class='btn btn-secondary' href='{{ $backUrl }}'>{{ trans('langCancel') }}</a>
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
