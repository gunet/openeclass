@extends('layouts.default')

@push('head_styles')
<link href="{{ $urlAppend }}js/bootstrap-datepicker/css/bootstrap-datepicker3.min.css" type='text/css' rel='stylesheet'>
@endpush

@push('head_scripts')
<script type='text/javascript' src='{{ $urlAppend }}js/tools.js'></script>
<script type='text/javascript' src='{{ $urlAppend }}js/bootstrap-datepicker/js/bootstrap-datepicker.min.js'></script>
<script type='text/javascript' src='{{ $urlAppend }}js/bootstrap-datepicker/locales/bootstrap-datepicker.{{ $language }}.min.js'></script>

<script type='text/javascript'>    
$(function() {
    $('#reg_date').datepicker({
            format: 'dd-mm-yyyy',
            language: '{{ $language }}',
            autoclose: true
        });
});
</script>

@endpush

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
                       
                        @if (!isset($_GET['from_user']))
                            <div class='alert alert-info'>
                                {{ trans('langRefreshInfo') }} {{ trans('langRefreshInfo_A') }}
                            </div>
                             
                            <form class='form-horizontal' role='form' action='{{ $form_url }}' method='post'>
                                @else
                                    <form class='form-horizontal' role='form' action='{{ $form_url_from_user }}' method='post'>
                                @endif
                                <fieldset>

                       

                                    {{--<div class='form-group mt-3'>
                                        <label for='delusers' class='col-sm-6 control-label-notes'>{{ trans('langUsers') }}</label>
                                        <div class='col-sm-12 checkbox'>
                                            <label><input type='checkbox' name='delusers'> {{ trans('langUserDelCourse') }}:</label>
                                        </div>
                                        <div class='col-sm-12'>
                                            {!! $selection_date !!}
                                        </div>
                                        <div class='col-sm-12 mt-3'>
                                            <input type='text' name='reg_date' id='reg_date' value='{{ $date_format }}'>
                                        </div>                
                                    </div>--}}


                                    <div class='form-group mt-3 text-center'>
                                        <label class='col-sm-6 control-label-notes'>{{trans('langUsers')}}</label>
                                        <div class='col-sm-12'>
                                            <p class='form-control-static'>{{trans('langUserDelCourse')}}</p>
                                        </div>
                                    </div>
                                    <div class='form-group mt-3'>
                                        <div class='col-sm-12 checkbox'>
                                            <label><input type='checkbox' name='delusersinactive'>{{trans('langInactiveUsers')}}</label>
                                        </div>
                                    </div>
                                    <div class='form-group mt-3'>
                                        <div class='col-sm-12 checkbox'>
                                            <label><input type='checkbox' name='delusersdate'>{{trans('langWithRegistrationDate')}}:</label>
                                        </div>
                                        <div class='row'>
                                            <div class='col-sm-6'>
                                                {!! selection(array('before' => trans('langBefore'), 'after' => trans('langAfter')), 'reg_flag', $reg_flag) !!}
                                            </div>
                                            <div class='col-sm-6 mt-md-0 mt-2'>
                                                <input class='w-75' type='text' name='reg_date' id='reg_date' value='{!! date("d-m-Y", time()) !!}'>
                                                <span class="fa fa-calendar" aria-hidden="true"></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class='form-group mt-3'>
                                        <div class='col-sm-12 checkbox'>
                                            <label><input type='checkbox' name='delusersdept'>{{trans('langWho')}}</label>
                                        </div>
                                        <div class='row'>
                                            <div class='col-sm-6'>
                                                {!! selection(array('yes' => trans('langWithDepartment'), 'no' => trans('langWithoutDepartment')), 'dept_flag', 'yes') !!}
                                            </div>
                                            <div class='col-sm-6 mt-md-0 mt-2'>
                                                {!! $buildusernode !!}
                                            </div>
                                        </div>
                                    </div>
                                    <div class='form-group MT-3'>
                                        <div class='col-sm-12 checkbox'>
                                            <label><input type='checkbox' name='delusersid'>{{trans('langWith')}}</label>
                                        </div>
                                        <div class='row'>
                                            <div class='col-sm-6'>
                                                {!! selection(array('am' => trans('langWithStudentId'), 'uname' => trans('langWithUsernames')), 'id_flag', 'am') !!}
                                            </div>
                                            <div class='col-sm-6 mt-md-0 mt-2'>
                                                <textarea name='idlist' class='form-control' rows='5'></textarea>
                                            </div>
                                        </div>
                                    </div>



                                    @if (!isset($_GET['from_user']))

                                        <div class='form-group mt-3'>
                                            <label for='delannounces' class='col-sm-6 control-label-notes'>{{ trans('langAnnouncements') }}</label>
                                            <div class='col-sm-12 checkbox'>
                                                <label><input type='checkbox' name='delannounces'> {{ trans('langAnnouncesDel') }}</label>
                                            </div>
                                        </div>

                            

                                        <div class='form-group mt-3'>
                                            <label for='delagenda' class='col-sm-6 control-label-notes'>{{ trans('langAgenda') }}</label>
                                            <div class='col-sm-12 checkbox'>
                                                <label><input type='checkbox' name='delagenda'> {{ trans('langAgendaDel') }}</label>
                                            </div>
                                        </div>

                                        

                                        <div class='form-group mt-3'>
                                            <label for='hideworks' class='col-sm-6 control-label-notes'>{{ trans('langWorks') }}</label>
                                            <div class='col-sm-12 checkbox'>
                                                <label><input type='checkbox' name='hideworks'> {{ trans('langHideWork') }}</label>
                                            </div>
                                            <div class='col-sm-offset-2 col-sm-10 checkbox'>
                                                <label><input type='checkbox' name='delworkssubs'> {{ trans('langDelAllWorkSubs') }}</label>
                                            </div>
                                        </div>

                            

                                        <div class='form-group mt-3'>
                                            <label for='purgeexercises' class='col-sm-6 control-label-notes'>{{ trans('langExercises') }}</label>
                                            <div class='col-sm-12 checkbox'>
                                                <label><input type='checkbox' name='purgeexercises'> {{ trans('langPurgeExercisesResults') }}</label>
                                            </div>
                                        </div>

                                        

                                        <div class='form-group mt-3'>
                                            <label for='clearstats' class='col-sm-6 control-label-notes'>{{ trans('langUsage') }}</label>
                                            <div class='col-sm-12 checkbox'>
                                                <label><input type='checkbox' name='clearstats'> {{ trans('langClearStats') }}</label>
                                            </div>
                                        </div>

                        

                                        <div class='form-group mt-3'>
                                            <label for='delblogposts' class='col-sm-6 control-label-notes'>{{ trans('langBlog') }}</label>
                                            <div class='col-sm-12 checkbox'>
                                                <label><input type='checkbox' name='delblogposts'> {{ trans('langDelBlogPosts') }}</label>
                                            </div>
                                        </div>

                                    

                                        <div class='form-group mt-3'>
                                            <label for='delwallposts' class='col-sm-6 control-label-notes'>{{ trans('langWall') }}</label>
                                            <div class='col-sm-12 checkbox'>
                                                <label><input type='checkbox' name='delwallposts'> {{ trans('langDelWallPosts') }}</label>
                                            </div>
                                        </div>
                                    @endif

                                    <div class='mt-3'></div>

                                    {{ showSecondFactorChallenge() }}

                               
                                    <div class='col-sm-offset-2 col-sm-10 mt-3'>
                                        <input class='btn btn-primary' type='submit' value='{{ trans('langSubmitActions') }}' name='submit'>
                                    </div>

                                </fieldset>
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