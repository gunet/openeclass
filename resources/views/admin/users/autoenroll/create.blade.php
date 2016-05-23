@extends('layouts.default')

@section('content')
    {!! isset($action_bar) ?  $action_bar : '' !!}
    <div class='form-wrapper'>
        <form role='form' class='form-horizontal' method='post' action='autoenroll.php'>
            <input type='hidden' name='add' value='{{ $type }}'>
            @if (isset($_GET['edit']))
                <input type='hidden' name='id' value='{{ $_GET['edit'] }}'>
            @endif           
            <fieldset>
                <div class='form-group'>
                    <label class='col-sm-3 control-label'>{{ trans('langStatus') }}:</label>   
                    <div class='col-sm-9'>
                        <p class='form-control-static'>{{ $type == USER_STUDENT ? trans('langStudents') : trans('langTeachers') }}</p>
                    </div>
                </div>
                <div class='form-group'>
                    <label for='title' class='col-sm-3 control-label'>{{ trans('langFaculty') }}:</label>   
                    <div class='col-sm-9 form-control-static'>
                        {!! $htmlTree !!}
                    </div>
                </div>
                <div class='form-group'>
                    <label for='title' class='col-sm-3 control-label'>{{ trans('langAutoEnrollCourse') }}:</label>   
                    <div class='col-sm-9'>
                        <input class='form-control' type='hidden' id='courses' name='courses' value=''>
                    </div>
                </div>
                <div class='form-group'>
                    <label for='title' class='col-sm-3 control-label'>{{ trans('langAutoEnrollDepartment') }}:</label>   
                    <div class='col-sm-9 form-control-static'>                  
                        <div id='nodCnt2'>
                        @foreach ($deps as $key => $dep)
                            <p id='nc_{{ $key }}'>
                                <input type='hidden' name='rule_deps[]' value='{{ $dep }}'>
                                {{ $tree->getFullPath(getDirectReference($dep)) }}
                                &nbsp;
                                <a href='#nodCnt2'>
                                    <span class='fa fa-times' data-toggle='tooltip' data-original-title='{{ trans('langNodeDel') }}' data-placement='top' title='{{ trans('langNodeDel') }}'></span>
                                </a>
                            </p>
                        @endforeach
                        </div>
                        <div>
                            <p>
                                <a id='ndAdd2' href='#add'>
                                    <span class='fa fa-plus' data-toggle='tooltip' data-placement='top' title='{{ trans('langNodeAdd') }}'></span>
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
                                        <button type='button' class='btn btn-default treeCourseModalClose'>{{ trans('langCancel') }}</button>
                                        <button type='button' class='btn btn-primary' id='treeCourseModalSelect'>{{ trans('langSelect') }}</button>
                                    </div>
                                </div>
                            </div>
                        </div>                    
                    </div>
                </div>
                <div class='form-group'>
                    <div class='col-sm-12 checkbox'>
                        <label>
                            <input type='checkbox' name='apply' id='apply' value='1' checked='1'>
                            {{ trans('langApplyRule') }}
                        </label>
                    </div>
                </div>
                {!! showSecondFactorChallenge() !!}
                <div class='form-group'>
                    <div class='col-sm-10 col-sm-offset-2'>
                        <input class='btn btn-primary' type='submit' name='submit' value='{{ trans('langSubmit') }}'>
                        <a href='autoenroll.php' class='btn btn-default'>{{ trans('langCancel') }}</a>    
                    </div>
                </div>
            </fieldset>
            {!! generate_csrf_token_form_field() !!}
        </form>
    </div>
@endsection