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
                          
                          <form role='form' class='form-horizontal' action="{{ $_SERVER['SCRIPT_NAME'] }}?c={{$course->code}}" method='post'> 
                              <fieldset>
                              <legend class='mb-0' aria-label="{{ trans('langForm') }}"></legend>               
                              <div class='form-group'>
                                      <label for='localize' class='col-sm-6 control-label-notes mb-2'>{{ trans('langAvailableTypes') }}</label>
                                      <div class='radio mb-4'>
                                        <label>
                                          <input class='input-StatusCourse' id='courseopen' type='radio' name='formvisible' value='2'{!! $course->visible == 2 ? ' checked': '' !!}>
                                          <label for="courseopen" aria-label="{{ trans('langPublic') }}">
                                            {!! course_access_icon(COURSE_OPEN) !!}
                                          </label>
                                          {{ trans('langPublic') }}
                                        </label>
                                      </div>
                                      
                                      <div class='radio mb-4'>
                                        <label>
                                          <input class='input-StatusCourse' id='coursewithregistration' type='radio' name='formvisible' value='1'{!! $course->visible == 1 ? ' checked': '' !!}>
                                          <label for="coursewithregistration" aria-label="{{ trans('langPrivOpen') }}">
                                            {!! course_access_icon(COURSE_REGISTRATION) !!}
                                          </label>
                                          {{ trans('langPrivOpen') }}
                                        </label>
                                      </div>


                                      <div class='radio mb-4'>
                                        <label>
                                          <input class='input-StatusCourse' id='courseclose' type='radio' name='formvisible' value='0'{!! $course->visible == 0 ? ' checked': '' !!}>
                                          <label for="courseclose" aria-label="{{ trans('langClosedCourseShort') }}">
                                            {!! course_access_icon(COURSE_CLOSED) !!}
                                          </label>
                                          {{ trans('langClosedCourseShort') }}
                                        </label>
                                      </div>


                                      <div class='radio'>
                                        <label>
                                          <input class='input-StatusCourse' id='courseinactive' type='radio' name='formvisible' value='3'{!! $course->visible == 3 ? ' checked': '' !!}>
                                          <label for="courseinactive" aria-label="{{ trans('langInactiveCourse') }}">
                                            {!!  course_access_icon(COURSE_INACTIVE) !!}
                                          </label>
                                          {{ trans('langInactiveCourse') }}
                                        </label>
                                      </div>                   
                                 
                              </div>
                              <div class='form-group mt-5'>
                                  <div class='col-12 d-flex justify-content-end align-items-center'>
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