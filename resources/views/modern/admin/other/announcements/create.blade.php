@extends('layouts.default')

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

                    {!! isset($action_bar) ?  $action_bar : '' !!}

                    <div class='col-lg-6 col-12 d-none d-md-none d-lg-block'>
                        <div class='col-12 h-100 left-form'></div>
                    </div>

                    <div class='col-lg-6 col-12'>
                        <div class='form-wrapper form-edit rounded'> 
                            
                            <form role='form' class='form-horizontal' method='post' action='{{ $_SERVER['SCRIPT_NAME'] }}'>
                                @if (isset($announcement))
                                    <input type='hidden' name='id' value='{{ $announcement->id }}'>
                                @endif    
                                <div class='form-group{{ Session::hasError('title') ? " has-error" : "" }}'>
                                    <label for='title' class='col-sm-12 control-label-notes'>{{ trans('langTitle') }}</label>
                                    <div class='col-sm-12'>
                                        <input class='form-control' placeholder="{{ trans('langTitle') }}" type='text' name='title' value='{{ isset($announcement) ? $announcement->title : "" }}'>
                                        {!! Session::getError('title', "<span class='help-block'>:message</span>") !!}
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
                                                <input type='checkbox' name='startdate_active'{{ $start_checkbox }}>
                                            </span>
                                            <input class='form-control' name='startdate' id='startdate' type='text' value='{{ $startdate }}'>
                                        </div>
                                    </div>
                                </div>
                                <div class='mt-4 form-group'>
                                    <label for='enddate' class='col-sm-12 control-label-notes'>{{ trans('langEndDate') }}</label>
                                    <div class='col-sm-12'>
                                        <div class='input-group'>
                                            <span class='input-group-addon'>
                                                <input type='checkbox' name='enddate_active'{{ $end_checkbox }} {{ $end_disabled}}>
                                            </span>
                                            <input class='form-control' name='enddate' id='enddate' type='text' value='{{ $enddate }}'>
                                        </div>
                                    </div>
                                </div>
                                <div class='mt-4 form-group'>
                                    <div class='col-sm-10 col-sm-offset-2'>
                                        <div class='checkbox'>
                                            <label>
                                                <input type='checkbox' name='show_public'{{ $checked_public }}> {{ trans('langVisible') }}
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class='mt-5 form-group'>
                                    <div class='col-12 d-flex justify-content-center align-items-center'>
                                        <input id='submitAnnouncement' class='btn submitAdminBtn' type='submit' name='submitAnnouncement' value='{{ trans('langSubmit') }}'>
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
@endsection