@extends('layouts.default')

@section('content')

<div class="col-12 main-section">
  <div class='{{ $container }}'>

        <div class="row rowMargin">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    @include('layouts.partials.legend_view',['is_editor' => $is_editor, 'course_code' => $course_code])

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
                            
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    </div>
                    @endif
                    
                    
                    <div class='col-lg-6 col-12 d-none d-md-none d-lg-block'>
                        <div class='col-12 h-100 left-form'></div>
                    </div>

                    <div class='col-lg-6 col-12'>
                      <div class='form-wrapper form-edit rounded'>
                          
                          <form role='form' class='form-horizontal' action="{{ $_SERVER['SCRIPT_NAME'] }}?c={{$course->code}}" method='post'>                
                              <div class='form-group'>
                                      <label for='localize' class='col-sm-6 control-label-notes mb-2'>{{ trans('langAvailableTypes') }}</label>
                                      <div class='radio mb-4'>
                                        <label>
                                          <input class='input-StatusCourse' id='courseopen' type='radio' name='formvisible' value='2'{!! $course->visible == 2 ? ' checked': '' !!}>
                                          <label for="courseopen">
                                            {!! course_access_icon(COURSE_OPEN) !!}
                                          </label>
                                          {{ trans('langPublic') }}
                                        </label>
                                      </div>
                                      
                                      <div class='radio mb-4'>
                                        <label>
                                          <input class='input-StatusCourse' id='coursewithregistration' type='radio' name='formvisible' value='1'{!! $course->visible == 1 ? ' checked': '' !!}>
                                          <label for="coursewithregistration">
                                            {!! course_access_icon(COURSE_REGISTRATION) !!}
                                          </label>
                                          {{ trans('langPrivOpen') }}
                                        </label>
                                      </div>


                                      <div class='radio mb-4'>
                                        <label>
                                          <input class='input-StatusCourse' id='courseclose' type='radio' name='formvisible' value='0'{!! $course->visible == 0 ? ' checked': '' !!}>
                                          <label for="courseclose">
                                            {!! course_access_icon(COURSE_CLOSED) !!}
                                          </label>
                                          {{ trans('langClosedCourseShort') }}
                                        </label>
                                      </div>


                                      <div class='radio'>
                                        <label>
                                          <input class='input-StatusCourse' id='courseinactive' type='radio' name='formvisible' value='3'{!! $course->visible == 3 ? ' checked': '' !!}>
                                          <label for="courseinactive">
                                            {!!  course_access_icon(COURSE_INACTIVE) !!}
                                          </label>
                                          {{ trans('langInactiveCourse') }}
                                        </label>
                                      </div>                   
                                 
                              </div>
                              <div class='form-group mt-5'>
                                  <div class='col-12 d-flex justify-content-center align-items-center'>
                                      <input class='btn submitAdminBtn' type='submit' name='submit' value='{{ trans('langModify') }}'>
                                  </div>
                              </div>
                          </form>
                      </div>
                    </div>
              
      </div>
    </div>
</div>
@endsection