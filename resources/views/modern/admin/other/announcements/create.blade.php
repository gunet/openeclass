@extends('layouts.default')

@section('content')

<div class="col-12 main-section">
    <div class='{{ $container }} main-container'>
        <div class="row m-auto">

            @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])


            @include('layouts.partials.legend_view')

            @if(isset($action_bar))
                {!! $action_bar !!}
            @else
                <div class='mt-4'></div>
            @endif

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


            <div class='col-lg-6 col-12'>
                <div class='form-wrapper form-edit border-0 px-0'>

                    <form role='form' class='form-horizontal' method='post' action='{{ $_SERVER['SCRIPT_NAME'] }}'>
                        @if (isset($announcement))
                            <input type='hidden' name='id' value='{{ $announcement->id }}'>
                        @endif
                        <div class='form-group{{ Session::hasError('title') ? " has-error" : "" }}'>
                            <label for='title' class='col-sm-12 control-label-notes'>{{ trans('langTitle') }}</label>
                            <div class='col-sm-12'>
                                <input class='form-control' placeholder="{{ trans('langTitle') }}" type='text' name='title' value='{{ isset($announcement) ? $announcement->title : "" }}'>
                                {!! Session::getError('title', "<span class='help-block Accent-200-cl'>:message</span>") !!}
                            </div>
                        </div>
                        <div class='mt-4 form-group'>
                            <label for='newContent' class='col-sm-12 control-label-notes'>{{ trans('langAnnouncement') }}</label>
                            <div class='col-sm-12'>{!! $newContentTextarea !!}</div>
                        </div>
                        <div class='mt-4 form-group'>
                            <label class='col-sm-12 control-label-notes'>{{ trans('langLanguage') }}</label>
                            <div class='col-sm-12'>
                                {!! lang_select_options('lang_admin_ann', "class='form-control'", isset($announcement) ? $announcement->lang : false) !!}
                            </div>
                            <small class='text-end'>
                                <span class='help-block'>{{ trans('langTipLangAdminAnn') }}</span>
                            </small>
                        </div>
                        <div class='mt-4 form-group'>
                            <label for='startdate' class='col-sm-12 control-label-notes'>{{ trans('langStartDate') }}</label>
                            <div class='col-sm-12'>
                                <div class='input-group'>
                                    <span class='input-group-addon'>
                                    <label class='label-container'>
                                        <input class='mt-0' id='startIdCheckbox' type='checkbox' name='startdate_active'{{ $start_checkbox }}>
                                        <span class='checkmark'></span></label>
                                    </span>
                                    <span class="add-on1 input-group-text h-40px input-border-color border-end-0"><i class='fa-regular fa-calendar Neutral-600-cl'></i></span>
                                    <input class='form-control mt-0 border-start-0' name='startdate' id='startdate' type='text' value='{{ $startdate }}'>
                                </div>
                            </div>
                        </div>
                        <div class='mt-4 form-group'>
                            <label for='enddate' class='col-sm-12 control-label-notes'>{{ trans('langEndDate') }}</label>
                            <div class='col-sm-12'>
                                <div class='input-group'>
                                    <span class='input-group-addon'>
                                    <label class='label-container'>
                                        <input class='mt-0' id='endIdCheckbox' type='checkbox' name='enddate_active'{{ $end_checkbox }} >
                                        <span class='checkmark'></span></label>
                                    </span>
                                    <span class='add-on2 input-group-text h-40px input-border-color border-end-0'><i class='fa-regular fa-calendar Neutral-600-cl'></i></span>
                                    <input class='form-control mt-0 border-start-0' name='enddate' id='enddate' type='text' value='{{ $enddate }}'>
                                </div>
                            </div>
                        </div>
                        <div class='mt-4 form-group'>
                            <div class='col-sm-10 col-sm-offset-2'>
                                <div class='checkbox'>
                                <label class='label-container'>
                                        <input type='checkbox' name='show_public'{{ $checked_public }}> <span class='checkmark'></span>{{ trans('langVisible') }}
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class='mt-5 form-group'>
                            <div class='col-12 d-flex justify-content-end align-items-center'>
                                <input id='submitAnnouncement' class='btn submitAdminBtn' type='submit' name='submitAnnouncement' value='{{ trans('langSubmit') }}'>
                            </div>
                        </div>
                        {!! generate_csrf_token_form_field() !!}
                    </form>
                </div>
            </div>
            <div class='col-lg-6 col-12 d-none d-md-none d-lg-block text-end'>
                <img class='form-image-modules' src='{!! get_form_image() !!}' alt='form-image'>
            </div>
        </div>
    </div>
</div>

@endsection
