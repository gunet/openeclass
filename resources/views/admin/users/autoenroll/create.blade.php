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
                            
                        <form role='form' class='form-horizontal' method='post' action='autoenroll.php'>
                            <input type='hidden' name='add' value='{{ $type }}'>
                            @if (isset($_GET['edit']))
                                <input type='hidden' name='id' value='{{ $_GET['edit'] }}'>
                            @endif           
                            <fieldset>
                                <legend class='mb-0' aria-label="{{ trans('langForm') }}"></legend>
                                <div class='form-group'>
                                    <div class='col-sm-12 control-label-notes'>{{ trans('langStatus') }}:</div>   
                                    <div class='col-sm-12'>
                                        <p class='form-control-static'>{{ $type == USER_STUDENT ? trans('langStudents') : trans('langTeachers') }}</p>
                                    </div>
                                </div>
                             
                                <div class='form-group mt-4'>
                                    <div class='col-sm-12 control-label-notes'>{{ trans('langFaculty') }}:</div>   
                                    <div class='col-sm-12 form-control-static'>
                                        {!! $htmlTree !!}
                                    </div>
                                </div>
                    
                                <div class='form-group mt-4'>
                                    <label for='courses-select' class='col-sm-12 control-label-notes'>{{ trans('langAutoEnrollCourse') }}:</label>   
                                    <div class='col-sm-12'>
                                        <select id='courses-select' class='form-control' name='courses[]' multiple>{{$coursesOptions}}</select>
                                    </div>
                                </div>
                          
                                <div class='form-group mt-4'>
                                    <div class='col-sm-12 control-label-notes'>{{ trans('langAutoEnrollDepartment') }}:</div>   
                                    <div class='col-sm-12 form-control-static'>                  
                                        <div id='nodCnt2'>
                                        @foreach ($deps as $key => $dep)
                                            <p id='nc_{{ $key }}'>
                                                <input type='hidden' name='rule_deps[]' value='{{ $dep }}'>
                                                {{ $tree->getFullPath(getDirectReference($dep)) }}
                                                &nbsp;
                                                <a href='#nodCnt2' aria-label="{{ trans('langNodeDel') }}">
                                                    <span class='fa-solid fa-xmark' data-bs-toggle='tooltip' data-original-title='{{ trans('langNodeDel') }}' data-bs-placement='top' title='{{ trans('langNodeDel') }}'></span>
                                                </a>
                                            </p>
                                        @endforeach
                                        </div>
                                        <div>
                                            <p>
                                                <a id='ndAdd2' href='#add' aria-label="{{ trans('langNodeAdd') }}">
                                                    <span class='fa fa-plus' data-bs-toggle='tooltip' data-bs-placement='top' title='{{ trans('langNodeAdd') }}'></span>
                                                </a>
                                            </p>
                                        </div>
                                        <div class='modal fade' id='treeCourseModal' tabindex='-1' role='dialog' aria-labelledby='treeModalLabel' aria-hidden='true'>
                                            <div class='modal-dialog'>
                                                <div class='modal-content'>
                                                    <div class='modal-header'>
                                                        <div class='modal-title' id='treeCourseModalLabel'>{{ trans('langNodeAdd') }}</div>
                                                        <button type='button' class='close treeCourseModalClose' aria-label="{{ trans('langClose') }}">
                                                        </button>
                                                        
                                                    </div>
                                                    <div class='modal-body'>
                                                        <div id='js-tree-course'></div>
                                                    </div>
                                                    <div class='modal-footer'>
                                                        <button type='button' class='btn cancelAdminBtn treeCourseModalClose'>{{ trans('langCancel') }}</button>
                                                        <button type='button' class='btn submitAdminBtn ms-1' id='treeCourseModalSelect'>{{ trans('langSelect') }}</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>                    
                                    </div>
                                </div>
                          
                                <div class='form-group mt-4'>
                                    <div class='col-sm-12 checkbox mb-1'>
                                    <label class='label-container' aria-label="{{ trans('langSelect') }}">
                                            <input type='checkbox' name='apply' id='apply' value='1' checked='1'>
                                            <span class='checkmark'></span>{{ trans('langApplyRule') }}
                                        </label>
                                    </div>
                                </div>
                                <div class='mt-4'></div>
                                {!! showSecondFactorChallenge() !!}
                                <div class='form-group mt-5'>
                                    <div class='col-12 d-flex justify-content-end align-items-center gap-2'>
                                        <input class='btn submitAdminBtn' type='submit' name='submit' value='{{ trans('langSubmit') }}'>
                                        <a href='autoenroll.php' class='btn cancelAdminBtn'>{{ trans('langCancel') }}</a>    
                                    </div>
                                </div>
                            </fieldset>
                            {!! generate_csrf_token_form_field() !!}
                        </form>
                    </div></div>
                    <div class='col-lg-6 col-12 d-none d-md-none d-lg-block text-end'>
                    <img class='form-image-modules' src='{!! get_form_image() !!}' alt="{{ trans('langImgFormsDes') }}">
                    </div>
               
        </div>
</div>
</div>
@endsection