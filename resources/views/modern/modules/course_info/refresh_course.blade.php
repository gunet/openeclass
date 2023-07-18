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


<div class="col-12 main-section">
<div class='{{ $container }}'>
        <div class="row rowMargin">

            <div id="background-cheat-leftnav" class="col-xl-2 col-lg-3 col_sidebar_active d-flex justify-content-start align-items-strech ps-lg-0 pe-lg-0"> 
                <div class="d-none d-sm-block d-sm-none d-md-block d-md-none d-lg-block ContentLeftNav">
                    @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                </div>
            </div>

            <div class="col-xl-10 col-lg-9 col-12 col_maincontent_active p-lg-5">
                    
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


                    @include('layouts.partials.legend_view',['is_editor' => $is_editor, 'course_code' => $course_code])

                    
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
                            
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    </div>
                    @endif
                    
                     
                    <div class='col-12'>
                    <div class='form-wrapper form-edit rounded'>
                       
                        @if (!isset($_GET['from_user']))
                            <div class='alert alert-info'><i class='fa-solid fa-circle-info fa-lg'></i><span>
                                {{ trans('langRefreshInfo') }} {{ trans('langRefreshInfo_A') }}</span>
                            </div>
                             
                            <form class='form-horizontal' role='form' action='{{ $form_url }}' method='post'>
                                @else
                                    <form class='form-horizontal' role='form' action='{{ $form_url_from_user }}' method='post'>
                                @endif
                                <fieldset>


                                    <div class='form-group text-center'>
                                        <label class='col-sm-6 control-label-notes'>{{trans('langUsers')}}</label>
                                        <div class='col-sm-12'>
                                            <p class='form-control-static'>{{trans('langUserDelCourse')}}</p>
                                        </div>
                                    </div>
                                    <div class='form-group mt-4'>
                                        <div class='col-sm-12 checkbox'>
                                            <label><input type='checkbox' name='delusersinactive'>{{trans('langInactiveUsers')}}</label>
                                        </div>
                                    </div>
                                    <div class='form-group mt-4'>
                                        <div class='col-sm-12 checkbox'>
                                            <label><input type='checkbox' name='delusersdate'>{{trans('langWithRegistrationDate')}}:</label>
                                        </div>
                                        <div class='row'>
                                            <div class='col-sm-6'>
                                                {!! selection(array('before' => trans('langBefore'), 'after' => trans('langAfter')), 'reg_flag', $reg_flag) !!}
                                            </div>
                                            <div class='col-sm-6 mt-md-0 mt-2'>
                                                <div class='input-group'>
                                                    <input class='form-control mt-0' type='text' name='reg_date' id='reg_date' value='{!! date("d-m-Y", time()) !!}'>
                                                    <div class='input-group-text h-40px input-bg-color input-border-color'>
                                                        <span class="fa-solid fa-calendar text-white" aria-hidden="true"></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class='form-group mt-4'>
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
                                    <div class='form-group mt-4'>
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

                                        <div class='row'>
                                            <div class='col-md-6 col-12'>
                                                <div class='form-group mt-4'>
                                                    <label for='delannounces' class='col-sm-6 control-label-notes mb-1'>{{ trans('langAnnouncements') }}</label>
                                                    <div class='col-sm-12 checkbox'>
                                                        <label><input type='checkbox' name='delannounces'> {{ trans('langAnnouncesDel') }}</label>
                                                    </div>
                                                </div>
                                            </div>

                                
                                            <div class='col-md-6 col-12'>
                                                <div class='form-group mt-4'>
                                                    <label for='delagenda' class='col-sm-6 control-label-notes mb-1'>{{ trans('langAgenda') }}</label>
                                                    <div class='col-sm-12 checkbox'>
                                                        <label><input type='checkbox' name='delagenda'> {{ trans('langAgendaDel') }}</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        
                                        <div class='row'>
                                            <div class='col-md-6 col-12'>
                                                <div class='form-group mt-4'>
                                                    <label for='hideworks' class='col-sm-6 control-label-notes mb-1'>{{ trans('langWorks') }}</label>
                                                    <div class='col-sm-12 checkbox'>
                                                        <label><input type='checkbox' name='hideworks'> {{ trans('langHideWork') }}</label>
                                                    </div>
                                                    <div class='col-sm-offset-2 col-sm-10 checkbox'>
                                                        <label><input type='checkbox' name='delworkssubs'> {{ trans('langDelAllWorkSubs') }}</label>
                                                    </div>
                                                </div>
                                            </div>

                                
                                            <div class='col-md-6 col-12'>
                                                <div class='form-group mt-4'>
                                                    <label for='purgeexercises' class='col-sm-6 control-label-notes mb-1'>{{ trans('langExercises') }}</label>
                                                    <div class='col-sm-12 checkbox'>
                                                        <label><input type='checkbox' name='purgeexercises'> {{ trans('langPurgeExercisesResults') }}</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        
                                        <div class='row'>
                                            <div class='col-md-6 col-12'>
                                                <div class='form-group mt-4'>
                                                    <label for='clearstats' class='col-sm-6 control-label-notes mb-1'>{{ trans('langUsage') }}</label>
                                                    <div class='col-sm-12 checkbox'>
                                                        <label><input type='checkbox' name='clearstats'> {{ trans('langClearStats') }}</label>
                                                    </div>
                                                </div>
                                            </div>

                        
                                            <div class='col-md-6 col-12'>
                                                <div class='form-group mt-4'>
                                                    <label for='delblogposts' class='col-sm-6 control-label-notes mb-1'>{{ trans('langBlog') }}</label>
                                                    <div class='col-sm-12 checkbox'>
                                                        <label><input type='checkbox' name='delblogposts'> {{ trans('langDelBlogPosts') }}</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    

                                        <div class='form-group mt-4'>
                                            <label for='delwallposts' class='col-sm-6 control-label-notes'>{{ trans('langWall') }}</label>
                                            <div class='col-sm-12 checkbox'>
                                                <label><input type='checkbox' name='delwallposts'> {{ trans('langDelWallPosts') }}</label>
                                            </div>
                                        </div>
                                    @endif

                                    <div class='mt-4'></div>

                                    {{ showSecondFactorChallenge() }}

                               
                                    <div class='col-12 mt-5 d-flex justify-content-center align-items-center'>
                                        <input class='btn submitAdminBtn' type='submit' value='{{ trans('langSubmitActions') }}' name='submit'>
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