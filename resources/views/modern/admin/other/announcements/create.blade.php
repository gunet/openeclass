@extends('layouts.default')

@section('content')

<div class="pb-lg-3 pt-lg-3 pb-0 pt-0">

    <div class="container-fluid main-container">

        <div class="row rowMedium">

            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 justify-content-center col_maincontent_active_Homepage">
                    
                <div class="row p-lg-5 p-md-5 ps-1 pe-1 pt-5 pb-5">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    @include('layouts.partials.legend_view',['is_editor' => $is_editor, 'course_code' => $course_code])

                    @if(Session::has('message'))
                    <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 mt-5'>
                        <p class="alert {{ Session::get('alert-class', 'alert-info') }} alert-dismissible fade show" role="alert">
                            {{ Session::get('message') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </p>
                    </div>
                    @endif

                    {!! isset($action_bar) ?  $action_bar : '' !!}
                    <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12'>
                        <div class='form-wrapper shadow-sm p-3 mt-5 rounded'> 
                            
                            <form role='form' class='form-horizontal' method='post' action='{{ $_SERVER['SCRIPT_NAME'] }}'>
                                @if (isset($announcement))
                                    <input type='hidden' name='id' value='{{ $announcement->id }}'>
                                @endif    
                                <div class='mt-3 form-group{{ Session::hasError('title') ? " has-error" : "" }}'>
                                    <label for='title' class='col-sm-6 control-label-notes'>{{ trans('langTitle') }}:</label>
                                    <div class='col-sm-12'>
                                        <input class='form-control' type='text' name='title' value='{{ isset($announcement) ? $announcement->title : "" }}'>
                                        {!! Session::getError('title', "<span class='help-block'>:message</span>") !!}
                                    </div>
                                </div>
                                <div class='mt-3 form-group'>
                                    <label for='newContent' class='col-sm-6 control-label-notes'>{{ trans('langAnnouncement') }}:</label>
                                    <div class='col-sm-12'>{!! $newContentTextarea !!}</div>
                                </div>
                                <div class='mt-3 form-group'>
                                    <label class='col-sm-6 control-label-notes'>{{ trans('langLanguage') }}:</label>    
                                    <div class='col-sm-12'>
                                        {!! lang_select_options('lang_admin_ann', "class='form-control'", isset($announcement) ? $announcement->lang : false) !!}
                                    </div>
                                    <small class='text-right'>
                                        <span class='help-block'>{{ trans('langTipLangAdminAnn') }}</span>
                                    </small>
                                </div>
                                <div class='mt-3 form-group'>
                                    <label for='startdate' class='col-sm-6 control-label-notes'>{{ trans('langStartDate') }} :</label>
                                    <div class='col-sm-12'>
                                        <div class='input-group'>
                                            <span class='input-group-addon'>
                                                <input type='checkbox' name='startdate_active'{{ $start_checkbox }}>
                                            </span>
                                            <input class='form-control' name='startdate' id='startdate' type='text' value='{{ $startdate }}' disabled>
                                        </div>
                                    </div>
                                </div>
                                <div class='mt-3 form-group'>
                                    <label for='enddate' class='col-sm-6 control-label-notes'>{{ trans('langEndDate') }} :</label>
                                    <div class='col-sm-12'>
                                        <div class='input-group'>
                                            <span class='input-group-addon'>
                                                <input type='checkbox' name='enddate_active'{{ $end_checkbox }} disabled>
                                            </span>
                                            <input class='form-control' name='enddate' id='enddate' type='text' value='{{ $enddate }}' disabled>
                                        </div>
                                    </div>
                                </div>
                                <div class='mt-3 form-group'>
                                    <div class='col-sm-10 col-sm-offset-2'>
                                        <div class='checkbox'>
                                            <label>
                                                <input type='checkbox' name='show_public'{{ $checked_public }}> {{ trans('langVisible') }}
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class='mt-3 form-group'>
                                    <div class='col-sm-offset-2 col-sm-10'>
                                        <input id='submitAnnouncement' class='btn btn-primary' type='submit' name='submitAnnouncement' value='{{ trans('langSubmit') }}'>
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