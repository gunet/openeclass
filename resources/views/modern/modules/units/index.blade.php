@extends('layouts.default')

@section('content')

<div class="pb-3 pt-3 mobile_width">

    <div class="container-fluid main-container my_course_info_container">

        <div class="row">

            <div class="col-xl-2 col-lg-2 col-md-0 col-sm-0 col-0 justify-content-center col_sidebar_active"> 
                <div class="d-none d-sm-block d-sm-none d-md-block d-md-none d-lg-block">
                    @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                </div>
            </div>

            <div class="col-xl-10 col-lg-10 col-md-12 col-sm-12 col-12 justify-content-center col_maincontent_active">


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


                    <div class="col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                        <div class="row p-2"></div><div class="row p-2"></div>
                        <legend class="float-none w-auto py-2 px-4 notes-legend"><span class="pos_TitleCourse"><i class="fas fa-university" aria-hidden="true"></i> {{$toolName}} του μαθήματος <strong>{{$currentCourseName}} <small>({{$course_code}})</small></strong></span>
                            <div class="manage-course-tools"style="float:right">
                                @if ($is_course_admin)
                                    
                                        @include('layouts.partials.manageCourse',[$urlAppend => $urlAppend,'coursePrivateCode' => $course_code])
                                    
                                @endif
                            </div>
                        </legend>
                    </div>
                    <div class="row p-2"></div>
                    <small>Καθηγητής: {{course_id_to_prof($course_id)}}</small> 

                    <div class="row p-2"></div>


                    @if ($is_editor)
                                {!! action_bar(array(
                                    array('title' => trans('langEditUnitSection'),
                                        'url' => $editUrl,
                                        'icon' => 'fa fa-edit',
                                        'level' => 'primary-label',
                                        'button-class' => 'btn-success'),
                                    array('title' => trans('langAdd') . ' ' . trans('langInsertExercise'),
                                        'url' => $insertBaseUrl . 'exercise',
                                        'icon' => 'fa fa-pencil-square-o',
                                        'level' => 'secondary',
                                        'show' => !is_module_disable(MODULE_ID_EXERCISE)),
                                    array('title' => trans('langAdd') . ' ' . trans('langInsertDoc'),
                                        'url' => $insertBaseUrl . 'doc',
                                        'icon' => 'fa fa-folder-open-o',
                                        'level' => 'secondary',
                                        'show' => !is_module_disable(MODULE_ID_DOCS)),
                                    array('title' => trans('langAdd') . ' ' . trans('langInsertText'),
                                        'url' => $insertBaseUrl . 'text',
                                        'icon' => 'fa fa-file-text-o',
                                        'level' => 'secondary'),
                                    array('title' => trans('langAdd') . ' ' . trans('langInsertLink'),
                                        'url' => $insertBaseUrl . 'link',
                                        'icon' => 'fa fa-link',
                                        'level' => 'secondary',
                                        'show' => !is_module_disable(MODULE_ID_LINKS)),
                                    array('title' => trans('langAdd') . ' ' . trans('langLearningPath1'),
                                        'url' => $insertBaseUrl . 'lp',
                                        'icon' => 'fa fa-ellipsis-h',
                                        'level' => 'secondary',
                                        'show' => !is_module_disable(MODULE_ID_LP)),
                                    array('title' => trans('langAdd') . ' ' . trans('langInsertVideo'),
                                        'url' => $insertBaseUrl . 'video',
                                        'icon' => 'fa fa-film',
                                        'level' => 'secondary',
                                        'show' => !is_module_disable(MODULE_ID_VIDEO)),
                                    array('title' => trans('langAdd') . ' ' . trans('langInsertForum'),
                                        'url' => $insertBaseUrl . 'forum',
                                        'icon' => 'fa fa-comments',
                                        'level' => 'secondary'),
                                    array('title' => trans('langAdd') . ' ' . trans('langInsertEBook'),
                                        'url' => $insertBaseUrl . 'ebook',
                                        'icon' => 'fa fa-book',
                                        'level' => 'secondary',
                                        'show' => !is_module_disable(MODULE_ID_EBOOK)),
                                    array('title' => trans('langAdd') . ' ' . trans('langInsertWork'),
                                        'url' => $insertBaseUrl . 'work',
                                        'icon' => 'fa fa-flask',
                                        'level' => 'secondary',
                                        'show' => !is_module_disable(MODULE_ID_ASSIGN)),
                                    array('title' => trans('langAdd') . ' ' . trans('langInsertPoll'),
                                        'url' => $insertBaseUrl . 'poll',
                                        'icon' => 'fa fa-question-circle',
                                        'level' => 'secondary',
                                        'show' => !is_module_disable(MODULE_ID_QUESTIONNAIRE)),
                                    array('title' => trans('langAdd') . ' ' . trans('langInsertWiki'),
                                        'url' => $insertBaseUrl . 'wiki',
                                        'icon' => 'fa fa-wikipedia-w',
                                        'level' => 'secondary',
                                        'show' => !is_module_disable(MODULE_ID_WIKI)),
                                    array('title' => trans('langAdd') . ' ' . trans('langInsertChat'),
                                        'url' => $insertBaseUrl . 'chat',
                                        'icon' => 'fa fa-exchange',
                                        'level' => 'secondary',
                                        'show' => !is_module_disable(MODULE_ID_CHAT)),
                                    array('title' => trans('langAdd') . ' ' . trans('langInsertTcMeeting'),
                                        'url' => $insertBaseUrl . 'tc',
                                        'icon' => 'fa fa-exchange',
                                        'level' => 'secondary',
                                        'show' => (!is_module_disable(MODULE_ID_TC) && is_configured_tc_server()))
                                    )) !!}
                            
                    @endif

                    @if(Session::has('message'))
                    <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 mt-5'>
                        <p class="alert {{ Session::get('alert-class', 'alert-info') }} alert-dismissible fade show" role="alert">
                            {{ Session::get('message') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </p>
                    </div>
                    @endif

<<<<<<< local
                            @if ($previousLink or $nextLink)
                                
                                <div class='row'>
                                    <div class='col-md-12'>
                                        <div class='form-wrapper course_units_pager clearfix'>
                                            @if ($previousLink)
                                                <a class='pull-left' title='{{ $previousTitle }}' href='{{ $previousLink}}'>
                                                    <span class='fa fa-arrow-left space-after-icon'></span>
                                                    {{ ellipsize($previousTitle, 30) }}
                                                </a>
                                            @endif
                                            @if ($nextLink)
                                                <a class='pull-right' title='{{ $nextTitle }}' href='{{ $nextLink}}'>
                                                    {{ ellipsize($nextTitle, 30) }}
                                                    <span class='fa fa-arrow-right space-before-icon'></span>
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="row p-2"></div>
                            @endif
=======
                    @if ($previousLink or $nextLink)
                        
                        <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 mt-3'>
                            <div class='form-wrapper course_units_pager clearfix'>
                                @if ($previousLink)
                                    <a class='pull-left' title='{{ $previousTitle }}' href='{{ $previousLink}}'>
                                        <span class='fa fa-arrow-left space-after-icon'></span>
                                        {{ ellipsize($previousTitle, 30) }}
                                    </a>
                                @endif
                                @if ($nextLink)
                                    <a class='pull-right' title='{{ $nextTitle }}' href='{{ $nextLink}}'>
                                        {{ ellipsize($nextTitle, 30) }}
                                        <span class='fa fa-arrow-right space-before-icon'></span>
                                    </a>
                                @endif
                            </div>
                        </div>
                        
                    @endif
>>>>>>> graft

<<<<<<< local
                            <div class='row'>
                                <div class='col-md-12'>
                                    <div class='panel panel-default'>
                                        <div class='panel-heading'>
                                            <label class='col-sm-8 control-label-notes'>Τίτλος</label>
                                            <div class='panel-title h5'>{{ $pageName }}
                                                <h6 class='text-muted'>
                                                    {{ $course_start_week }}
                                                    {{ $course_finish_week }}
                                                </h6>
                                            </div>
                                        </div>
                                        <div class="row p-2"></div>
                                        <div class='panel-body'>
                                            <label class='col-sm-8 control-label-notes'>Περιεχόμενο</label>
                                            <div>
                                                {!! standard_text_escape($comments) !!}
                                            </div>
                                            @if ($tags_list)
                                                <div>
                                                    <small><span class='text-muted'>{{ trans('langTags') }}:</span> {!! $tags_list !!}</small>
                                                </div>
                                            @endif
                                            <div class='unit-resources'>
                                                {!! show_resources($unitId) !!}
                                            </div>
                                        </div>
                                    </div>
=======
                            
                    <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 mt-3'>
                        <div class='panel panel-default shadow-lg p-3 mb-5 bg-body rounded bg-primary'>
                            <div class='panel-heading'>
                                <label class='col-sm-8 control-label-notes'>Τίτλος</label>
                                <div class='panel-title h5'>{{ $pageName }}
                                    <h6 class='text-muted'>
                                        {{ $course_start_week }}
                                        {{ $course_finish_week }}
                                    </h6>
>>>>>>> graft
                                </div>
                            </div>
<<<<<<< local

                            <div class="row p-2"></div>
=======
                            <div class="row p-2"></div>
                            <div class='panel-body'>
                                <label class='col-sm-8 control-label-notes'>Περιεχόμενο</label>
                                <div>
                                    {!! standard_text_escape($comments) !!}
                                </div>
                                @if ($tags_list)
                                    <div>
                                        <small><span class='text-muted'>{{ trans('langTags') }}:</span> {!! $tags_list !!}</small>
                                    </div>
                                @endif
                                <div class='unit-resources'>
                                    {!! show_resources($unitId) !!}
                                </div>
                            </div>
                        </div>
                    </div>
                            
>>>>>>> graft

<<<<<<< local
                            <div class='row'>
                                <div class='col-md-12'>
                                    <div class='panel panel-default'>
                                        <div class='panel-body'>
                                            <form class='form-horizontal' name='unitselect' action='{{ $urlAppend }}modules/units/index.php' method='get'>
                                                <input type='hidden' name='course' value='{{ $course_code }}'>
                                                <div class='form-group'>
                                                    <label class='col-sm-8 control-label-notes'>{{ trans('langCourseUnits') }}</label>
                                                    <div class='col-sm-4'>
                                                        <label class='sr-only' for='id'>{{ trans('langCourseUnits') }}</label>
                                                        <select name='id' id='id' class='form-control' onchange='document.unitselect.submit()'>
                                                            @foreach ($units as $unit)
                                                                <option value='{{ $unit->id }}' {{ $unit->id == $unitId ? 'selected' : '' }}>
                                                                    {{ ellipsize($unit->title, 50) }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                            </form>
=======
                            
                    <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 mt-3'>
                        <div class='panel panel-default shadow-lg p-3 mb-5 bg-body rounded bg-primary'>
                            <div class='panel-body'>
                                <form class='form-horizontal' name='unitselect' action='{{ $urlAppend }}modules/units/index.php' method='get'>
                                    <input type='hidden' name='course' value='{{ $course_code }}'>
                                    <div class='form-group'>
                                        <label class='col-sm-8 control-label-notes'>{{ trans('langCourseUnits') }}</label>
                                        <div class='col-sm-12'>
                                            <label class='sr-only' for='id'>{{ trans('langCourseUnits') }}</label>
                                            <select name='id' id='id' class='form-select' onchange='document.unitselect.submit()'>
                                                @foreach ($units as $unit)
                                                    <option value='{{ $unit->id }}' {{ $unit->id == $unitId ? 'selected' : '' }}>
                                                        {{ ellipsize($unit->title, 50) }}
                                                    </option>
                                                @endforeach
                                            </select>
>>>>>>> graft
                                        </div>
                                    </div>
                                </form>
                            </div>
<<<<<<< local
                </div>

=======
                        </div>
                    </div>
                       
            
                </div>
>>>>>>> graft
            </div>
        </div>
    </div>
</div>
@endsection

