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

                    {!! isset($action_bar) ?  $action_bar : '' !!}
                    <div class='col-lg-6 col-12 d-none d-md-none d-lg-block'>
                        <div class='col-12 h-100 left-form'></div>
                    </div>
                    <div class='col-lg-6 col-12'>
                        <div class='form-wrapper shadow-sm p-3 rounded'>
                            
                        <form role='form' class='form-horizontal' method='post' action='autoenroll.php'>
                            <input type='hidden' name='add' value='{{ $type }}'>
                            @if (isset($_GET['edit']))
                                <input type='hidden' name='id' value='{{ $_GET['edit'] }}'>
                            @endif           
                            <fieldset>
                                <div class='form-group mt-3'>
                                    <label class='col-sm-12 control-label-notes'>{{ trans('langStatus') }}:</label>   
                                    <div class='col-sm-12'>
                                        <p class='form-control-static'>{{ $type == USER_STUDENT ? trans('langStudents') : trans('langTeachers') }}</p>
                                    </div>
                                </div>
                             
                                <div class='form-group mt-3'>
                                    <label for='title' class='col-sm-12 control-label-notes'>{{ trans('langFaculty') }}:</label>   
                                    <div class='col-sm-12 form-control-static'>
                                        {!! $htmlTree !!}
                                    </div>
                                </div>
                    
                                <div class='form-group mt-3'>
                                    <label for='title' class='col-sm-12 control-label-notes'>{{ trans('langAutoEnrollCourse') }}:</label>   
                                    <div class='col-sm-12'>
                                        {{--<input class='form-control' type='hidden' id='courses' name='courses' value=''>--}}
                                        <select id='courses-select' class='form-control' name='courses[]' multiple>{{$coursesOptions}}</select>
                                    </div>
                                </div>
                          
                                <div class='form-group mt-3'>
                                    <label for='title' class='col-sm-12 control-label-notes'>{{ trans('langAutoEnrollDepartment') }}:</label>   
                                    <div class='col-sm-12 form-control-static'>                  
                                        <div id='nodCnt2'>
                                        @foreach ($deps as $key => $dep)
                                            <p id='nc_{{ $key }}'>
                                                <input type='hidden' name='rule_deps[]' value='{{ $dep }}'>
                                                {{ $tree->getFullPath(getDirectReference($dep)) }}
                                                &nbsp;
                                                <a href='#nodCnt2'>
                                                    <span class='fa fa-times' data-bs-toggle='tooltip' data-original-title='{{ trans('langNodeDel') }}' data-bs-placement='top' title='{{ trans('langNodeDel') }}'></span>
                                                </a>
                                            </p>
                                        @endforeach
                                        </div>
                                        <div>
                                            <p>
                                                <a id='ndAdd2' href='#add'>
                                                    <span class='fa fa-plus' data-bs-toggle='tooltip' data-bs-placement='top' title='{{ trans('langNodeAdd') }}'></span>
                                                </a>
                                            </p>
                                        </div>
                                        <div class='modal fade' id='treeCourseModal' tabindex='-1' role='dialog' aria-labelledby='treeModalLabel' aria-hidden='true'>
                                            <div class='modal-dialog'>
                                                <div class='modal-content'>
                                                    <div class='modal-header'>
                                                        <button type='button' class='close treeCourseModalClose'>
                                                            <span aria-hidden='true'>&times;</span>
                                                            <span class='sr-only'>{{ trans('langCancel') }}</span>
                                                        </button>
                                                        <h4 class='modal-title' id='treeCourseModalLabel'>{{ trans('langNodeAdd') }}</h4>
                                                    </div>
                                                    <div class='modal-body'>
                                                        <div id='js-tree-course'></div>
                                                    </div>
                                                    <div class='modal-footer'>
                                                        <button type='button' class='btn btn-secondary treeCourseModalClose'>{{ trans('langCancel') }}</button>
                                                        <button type='button' class='btn btn-primary' id='treeCourseModalSelect'>{{ trans('langSelect') }}</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>                    
                                    </div>
                                </div>
                          
                                <div class='form-group mt-3'>
                                    <div class='col-sm-12 checkbox'>
                                        <label>
                                            <input type='checkbox' name='apply' id='apply' value='1' checked='1'>
                                            {{ trans('langApplyRule') }}
                                        </label>
                                    </div>
                                </div>
                                <div class='mt-3'></div>
                                {!! showSecondFactorChallenge() !!}
                                <div class='form-group mt-5'>
                                    <div class='col-12'>
                                        <div class='row'>
                                            <div class='col-6'>
                                                <input class='btn btn-primary submitAdminBtn w-100' type='submit' name='submit' value='{{ trans('langSubmit') }}'>
                                            </div>
                                            <div class='col-6'>
                                                <a href='autoenroll.php' class='btn btn-secondary cancelAdminBtn w-100'>{{ trans('langCancel') }}</a>    
                                            </div>
                                        </div>
                                        
                                        
                                    </div>
                                </div>
                            </fieldset>
                            {!! generate_csrf_token_form_field() !!}
                        </form>
                    </div></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection