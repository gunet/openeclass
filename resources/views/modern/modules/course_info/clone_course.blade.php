@extends('layouts.default')

@push('head_styles')
<link href="{{ $urlAppend }}js/jstree3/themes/proton/style.min.css" type='text/css' rel='stylesheet'>
@endpush

@push('head_scripts')
<script type='text/javascript' src='{{ $urlAppend }}js/jstree3/jstree.min.js'></script>
<script type='text/javascript' src='{{ $urlAppend }}js/tools.js'></script>
@endpush

@section('content')

<div class="p-xl-5 py-lg-3 pb-0 pt-0">

    <div class="container-fluid main-container">

        <div class="row rowMedium">

            <div id="background-cheat-leftnav" class="col-xl-2 col-lg-3 col_sidebar_active d-flex justify-content-start align-items-strech ps-lg-0 pe-lg-3"> 
                <div class="d-none d-sm-block d-sm-none d-md-block d-md-none d-lg-block ContentLeftNav">
                    @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                </div>
            </div>

            <div class="col-xl-10 col-lg-9 col-12 col_maincontent_active">
                
                <div class="row p-lg-5 p-md-5 ps-1 pe-1 pt-5 pb-5">


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


                          

                    @if (isset($_POST['create_restored_course']))

                        {!! $new_action_bar !!}

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

                        @if (!empty($restore_users))
                            <div class='col-sm-12'>
                                <div class='alert alert-info'>
                                    {!! $restore_users !!}
                                </div>
                            </div>
                        @endif
                        <div class='col-sm-12'>
                            <div class='alert alert-info'>
                                {{ trans('langCopyFiles') }} {{ $coursedir}}
                            </div>
                        </div>

                    @else

                        {!! $action_bar !!}

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

                        <div class='col-sm-12'>
                            <div class='alert alert-info'>{{ trans('langInfo1') }} <br> {{ trans('langInfo2') }}</div>
                        </div>

                       
                        <div class='col-sm-12'>
                            <div class='form-wrapper form-edit rounded' >
                                <form class='form-horizontal' role='form' action='{{ $formAction }}' method='post' onsubmit='return validateNodePickerForm();' >

                                <div class='form-group'>
                                    <label for='course_code' class='col-sm-6 control-label-notes'>{{ trans('langCourseCode') }}:</label>
                                    <div class='col-sm-12'>
                                        <input type='text' class='form-control' id='course_code' name='course_code' value='{{ $code }}'>
                                    </div>
                                </div>
                                <div class='form-group mt-4'>
                                    <label for='course_code' class='col-sm-6 control-label-notes'>{{ trans('langLanguage') }}:</label>
                                    <div class='col-sm-12'>
                                        {!! $lang_selection !!}
                                    </div>
                                </div>
                                <div class='form-group mt-4'>
                                    <label for='course_title' class='col-sm-6 control-label-notes'>{{ trans('langTitle') }}:</label>
                                    <div class='col-sm-12'>
                                        <input class='form-control' type='text' id='course_title' name='course_title' value='{{ $title }}'>
                                    </div>
                                </div>

                                <div class='form-group mt-4'>
                                    <label class='col-sm-6 control-label-notes'>{{ trans('langCourseDescription') }}:</label>
                                    <div class='col-sm-12'>
                                        {!! $rich_text_editor !!}
                                    </div>
                                </div>
                                <div class='form-group mt-4'>
                                    <label class='col-sm-6 control-label-notes'>{{ trans('langFaculty') }}:</label>
                                    <div class='col-sm-12'>
                                        {!! $course_node_picker !!} <br>{{ trans('langOldValue') }}: <i>{{ $old_faculty }}</i>
                                    </div>
                                </div>
                                <div class='form-group mt-4'>
                                    <label class='col-sm-6 control-label-notes'>{{ trans('langCourseVis') }}:</label>
                                    <div class='col-sm-12'>
                                        {!! $visibility_select !!}
                                    </div>
                                </div>
                                <div class='form-group mt-4'>
                                    <label for='course_prof' class='col-sm-6 control-label-notes'>{{ trans('langTeacher') }}:</label>
                                    <div class='col-sm-12'>
                                        <input class='form-control' type='text' id='course_prof' name='course_prof' value='{{ $prof }}' size='50' />
                                    </div>
                                </div>
                                <div class='form-group mt-4'>
                                    <label class='col-sm-12 control-label-notes'>{{ trans('langUsersWillAdd') }}:</label>
                                    
                                    <div class='radio mb-2'>
                                        <label>
                                            <input type='radio' name='add_users' value='all' id='add_users_all' checked='checked'>
                                            {{ trans('langAll') }}
                                        </label>
                                    </div>
                                        <input type='radio' name='add_users' value='prof' id='add_users_prof'>
                                        {{ trans('langsTeachers') }}<br>
                                        <input type='radio' name='add_users' value='none' id='add_users_none'>
                                        {{ trans('langNone') }}
                                    
                                </div>
                                <div class='form-group mt-4'>
                                    <label class='col-sm-6 control-label-notes'>{{ trans('langMultiRegType') }}:</label>
                                    <div class='col-sm-12'>
                                        <input type='checkbox' name='create_users' value='1' id='create_users' checked='checked'>
                                    </div>
                                </div>
                                <div class='form-group mt-5'>
                                    <div class='col-12 d-flex justify-content-center align-items-center'>
                                        
                                        <input class='btn submitAdminBtn' type='submit' name='create_restored_course' value='{{ trans('langOk') }}'>
                                        <input type='hidden' name='restoreThis' value='" . q($_POST['restoreThis']) . "' />
                                    </div>
                                </div>
                                {!! generate_csrf_token_form_field() !!}
                                </form>
                            </div>
                        </div>

                    @endif
@endsection



