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
    
                    @include('layouts.partials.show_alert') 

                    <div class='col-lg-6 col-12'>
                        <div class='form-wrapper form-edit border-0 px-0'>
                            
                            <form role='form' class='form-horizontal' action='{{ $_SERVER['SCRIPT_NAME'] }}?c={{ $course->code }}' method='post' onsubmit='return validateNodePickerForm();'>
                                <fieldset>

                                    <div class='form-group'>
                                        <p class='col-sm-12 control-label-notes'>{{ trans('langFaculty') }}</p>
                                        <div class='col-sm-12'>
                                            {!! $node_picker !!}
                                        </div>
                                    </div>
                                    <div class='form-group mt-4'>
                                        <label for='fcode' class='col-sm-12 control-label-notes'>{{ trans('langCode') }}</label>
                                        <div class='col-sm-12'>
                                            <input type='text' class='form-control' name='fcode' id='fcode' value='{{ $course->code }}'>
                                        </div>
                                    </div>
                                    <div class='form-group mt-4'>
                                        <label for='title' class='col-sm-12 control-label-notes'>{{ trans('langCourseTitle') }}</label>
                                        <div class='col-sm-12'>
                                            <input type='text' class='form-control' name='title' id='title' value='{{ $course->title }}'>
                                        </div>
                                    </div>
                                    <div class='form-group mt-4'>
                                        <label for='titulary' class='col-sm-12 control-label-notes'>{{ trans('langTeachers') }}</label>
                                        <div class='col-sm-12'>
                                            <input type='text' class='form-control' name='titulary' id='titulary' value='{{ $course->prof_name }}'>
                                        </div>
                                    </div>
                                    {!! showSecondFactorChallenge() !!}
                                    {!! generate_csrf_token_form_field() !!}    
                                    <div class='form-group mt-5'>
                                        <div class='col-12 d-flex justify-content-end aling-items-center'>
                                            <input class='btn submitAdminBtn' type='submit' name='submit' value='{{ trans('langModify') }}'>
                                        </div>
                                    </div>
                                </fieldset>
                            </form>
                        </div>  
                    </div>

                    <div class='col-lg-6 col-12 d-none d-md-none d-lg-block text-end'>
                        <img class='form-image-modules' src='{!! get_form_image() !!}' alt="{{ trans('langImgFormsDes') }}">
                    </div>
                
        </div>
    </div>
</div>              
@endsection