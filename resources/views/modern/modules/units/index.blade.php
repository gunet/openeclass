@extends('layouts.default')

@section('content')

<div class="col-12 main-section">
    <div class='{{ $container }} module-container py-lg-0'>
        <div class="course-wrapper d-lg-flex align-items-lg-strech w-100">

            <div id="background-cheat-leftnav" class="col_sidebar_active d-flex justify-content-start align-items-strech ps-lg-0 pe-lg-0">
                <div class="d-none d-sm-block d-sm-none d-md-block d-md-none d-lg-block ContentLeftNav">
                    @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                </div>
            </div>

            <div class="col_maincontent_active">

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

                    @include('layouts.partials.legend_view')

                    @if ($is_editor and $q->flipped_flag != 2)
                                {!! action_bar(array(
                                    array('title' => trans('langEditUnitSection'),
                                        'url' => $editUrl,
                                        'icon' => 'fa fa-edit',
                                        'level' => 'primary-label',
                                        'button-class' => 'btn-success'),
                                    array('title' => trans('langUnitCompletion'),
                                        'url' => $manageUrl,
                                        'icon' => 'fa fa-gear',
                                        'button-class' => 'btn-success'),
                                    array('title' => trans('langAdd') . ' ' . trans('langInsertText'),
                                        'url' => $insertBaseUrl . 'text',
                                        'icon' => 'fa fa-file-lines',
                                        'level' => 'secondary'),
                                    array('title' => trans('langAdd') . ' ' . trans('langInsertExercise'),
                                        'url' => $insertBaseUrl . 'exercise',
                                        'icon' => 'fa fa-file-pen',
                                        'level' => 'secondary',
                                        'show' => !is_module_disable(MODULE_ID_EXERCISE)),
                                    array('title' => trans('langAdd') . ' ' . trans('langInsertDoc'),
                                        'url' => $insertBaseUrl . 'doc',
                                        'icon' => 'fa fa-folder',
                                        'level' => 'secondary',
                                        'show' => !is_module_disable(MODULE_ID_DOCS)),
                                    array('title' => trans('langAdd') . ' ' . trans('langInsertLink'),
                                        'url' => $insertBaseUrl . 'link',
                                        'icon' => 'fa fa-link',
                                        'level' => 'secondary',
                                        'show' => !is_module_disable(MODULE_ID_LINKS)),
                                    array('title' => trans('langAdd') . ' ' . trans('langLearningPath1'),
                                        'url' => $insertBaseUrl . 'lp',
                                        'icon' => 'fa fa-timeline',
                                        'level' => 'secondary',
                                        'show' => !is_module_disable(MODULE_ID_LP)),
                                    array('title' => trans('langAdd') . ' ' . trans('langInsertVideo'),
                                        'url' => $insertBaseUrl . 'video',
                                        'icon' => 'fa fa-film',
                                        'level' => 'secondary',
                                        'show' => !is_module_disable(MODULE_ID_VIDEO)),
                                    array('title' => trans('langAdd') .' '. trans('langOfH5p'),
                                        'url' => $insertBaseUrl . 'h5p',
                                        'icon' => 'fa fa-tablet',
                                        'level' => 'secondary',
                                        'show' => !is_module_disable(MODULE_ID_H5P)),
                                    array('title' => trans('langAdd') . ' ' . trans('langInsertForum'),
                                        'url' => $insertBaseUrl . 'forum',
                                        'icon' => 'fa-regular fa-comment',
                                        'level' => 'secondary'),
                                    array('title' => trans('langAdd') . ' ' . trans('langInsertEBook'),
                                        'url' => $insertBaseUrl . 'ebook',
                                        'icon' => 'fa fa-book-atlas',
                                        'level' => 'secondary',
                                        'show' => !is_module_disable(MODULE_ID_EBOOK)),
                                    array('title' => trans('langAdd') . ' ' . trans('langInsertWork'),
                                        'url' => $insertBaseUrl . 'work',
                                        'icon' => 'fa fa-upload',
                                        'level' => 'secondary',
                                        'show' => !is_module_disable(MODULE_ID_ASSIGN)),
                                    array('title' => trans('langAdd') . ' ' . trans('langInsertPoll'),
                                        'url' => $insertBaseUrl . 'poll',
                                        'icon' => 'fa fa-question',
                                        'level' => 'secondary',
                                        'show' => !is_module_disable(MODULE_ID_QUESTIONNAIRE)),
                                    array('title' => trans('langAdd') . ' ' . trans('langInsertWiki'),
                                        'url' => $insertBaseUrl . 'wiki',
                                        'icon' => 'fa fa-w',
                                        'level' => 'secondary',
                                        'show' => !is_module_disable(MODULE_ID_WIKI)),
                                    array('title' => trans('langAdd') . ' ' . trans('langInsertChat'),
                                        'url' => $insertBaseUrl . 'chat',
                                        'icon' => 'fa-regular fa-comment-dots',
                                        'level' => 'secondary',
                                        'show' => !is_module_disable(MODULE_ID_CHAT)),
                                    array('title' => trans('langAdd') . ' ' . trans('langInsertTcMeeting'),
                                        'url' => $insertBaseUrl . 'tc',
                                        'icon' => 'fa fa-exchange',
                                        'level' => 'secondary',
                                        'show' => (!is_module_disable(MODULE_ID_TC) && is_enabled_tc_server($course_id)))
                                    ))
                                !!}

                    @endif
                    @if ($is_editor and $q->flipped_flag == 2)
                        {!! action_bar(array(
                            array('title' => trans('langEdit'),
                                'url' => $editUrl,
                                'icon' => 'fa fa-edit',
                                'level' => 'primary-label',
                                'button-class' => 'btn-success')
                        )) !!}

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

                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    </div>
                    @endif



                    @if(count($units) > 0)
                        <div class='col-12'>
                            <div class='card panelCard px-lg-4 py-lg-3'>
                                <div class='card-header border-0 bg-default d-flex justify-content-between align-items-center'>
                                    <h3 class='mb-0'>{{ trans('langUnits')}}</h3>
                                </div>
                                <div class='card-body'>
                                    <div id='UnitsControls' class='carousel slide' data-bs-ride='carousel'>

                                        <div class='carousel-indicators h-auto mb-1'>
                                            @php $counterIndicator = 0; @endphp
                                            <div class='carousel-indicators h-auto mb-1'>
                                                @foreach ($units as $cu)
                                                    @if($counterIndicator == 0)
                                                        <button type='button' data-bs-target='#UnitsControls' data-bs-slide-to='{{ $counterIndicator }}' class='active' aria-current='true'></button>
                                                    @else
                                                        <button type='button' data-bs-target='#UnitsControls' data-bs-slide-to='{{ $counterIndicator }}' aria-current='true'></button>
                                                    @endif
                                                    @php $counterIndicator++; @endphp
                                                @endforeach
                                            </div>
                                        </div>

                                        <div class='carousel-inner'>
                                            @php $counterUnits = 0; @endphp
                                            @foreach ($units as $cu)
                                                @if($counterUnits == 0)
                                                    <div class='carousel-item active'>
                                                @else
                                                    <div class='carousel-item'>
                                                @endif
                                                        <div id='unit_{{ $cu->id }}' class='col-12'>
                                                            <div class='panel clearfix'>
                                                                <div class='col-12'>
                                                                    <div class='item-content'>
                                                                        <div class='item-header clearfix'>
                                                                            <div class='item-title h4'>
                                                                                <a class='TextBold' href='{{ $urlServer }}modules/units/index.php?course={{ $course_code }}&amp;id={{ $cu->id }}'>
                                                                                    {{ $cu->title }}
                                                                                </a>
                                                                                <br>
                                                                                @if (!is_null($cu->start_week))
                                                                                    <small>
                                                                                        <span class='help-block'>
                                                                                            {!! format_locale_date(strtotime($cu->start_week), 'short', false) !!}
                                                                                        </span>
                                                                                    </small>
                                                                                @endif
                                                                            </div>
                                                                        </div>
                                                                        <div class='item-body'>
                                                                            <div style='height:1px;' class='border-top-default mt-1 mb-3'></div>
                                                                            <div class='col-sm-12 bg-transparent'>

                                                                                <button class='carousel-prev-btn' type='button' data-bs-target='#UnitsControls' data-bs-slide='prev'>
                                                                                    <i class='fa-solid fa-chevron-left Neutral-700-cl settings-icon'></i>
                                                                                </button>

                                                                                <button class='carousel-next-btn float-end' type='button' data-bs-target='#UnitsControls' data-bs-slide='next'>
                                                                                    <i class='fa-solid fa-chevron-right Neutral-700-cl settings-icon'></i>
                                                                                </button>

                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @php $counterUnits++; @endphp
                                            @endforeach
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif


                    <div class='col-12 mt-4'>
                        <div class="card panelCard px-lg-4 py-lg-3">
                            <div class='card-header border-0 bg-default d-flex justify-content-between align-items-center gap-3 flex-wrap'>
                                <h3>{{ $pageName }}</h3>
                                @if($course_start_week or $course_finish_week)
                                    <div>
                                        <small>{{ $course_start_week }}&nbsp;{{ $course_finish_week }}</small>
                                    </div>
                                @endif
                                @if ($previousLink or $nextLink)
                                    <div class='col-12'>
                                        <div class='card panelCard px-lg-0 py-lg-0 border-0'>
                                            <div class="card-body d-flex justify-content-between align-items-center p-3 bg-light Borders gap-3 flex-wrap">
                                                @if ($previousLink)
                                                    <a class='pull-left' title='{{ $previousTitle }}' href='{{ $previousLink}}'>
                                                        <span class='fa fa-arrow-left space-after-icon'></span>
                                                        {{ ellipsize($previousTitle, 30) }}
                                                    </a>
                                                @endif
                                                @if ($nextLink)
                                                    <a class='float-end' title='{{ $nextTitle }}' href='{{ $nextLink}}'>
                                                        {{ ellipsize($nextTitle, 30) }}
                                                        <span class='fa fa-arrow-right space-before-icon'></span>
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <div class="card-body">
                                <div>
                                    {!! $comments !!}
                                </div>
                                <div class='unit-resources mt-3'>
                                    {!! $tool_content_units !!}
                                </div>
                            </div>
                            @if ($tags_list)
                                <div class='card-footer small-text bg-default border-0'>
                                    <small><span class='text-muted'>{{ trans('langTags') }}:</span> {!! $tags_list !!}</small>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class='col-12 mt-4'>
                        <div class='form-wrapper form-edit rounded'>
                            <form class='form-horizontal' name='unitselect' action='{{ $urlAppend }}modules/units/index.php' method='get'>
                                <input type='hidden' name='course' value='{{ $course_code }}'>
                                <div class='mb-0'>
                                    <div class="d-inline-flex align-items-center">
                                        <label class='control-label-notes' for='id' style="min-width: 130px;"></span>&nbsp;{{ trans('langGoTo') }}:</label>
                                        <select name='id' id='id' class='form-select' onchange='document.unitselect.submit()'>
                                            @foreach ($units as $unit)
                                                <option value='{{ $unit->id }}' {{ $unit->id == $unitId ? 'selected' : '' }}>
                                                    {{ ellipsize($unit->title, 50) }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    @if ($is_editor and $q->flipped_flag == 2)
                        <div class='col-12 mt-4'>
                            <div class='card panelCard px-lg-4 py-lg-3'>
                                <div class='card-header border-0 bg-default d-flex justify-content-between align-items-center'>
                                    <h3>
                                        <div class='d-inline-flex align-items-top gap-3 flex-wrap'>
                                            {{ trans('langActivities')}}
                                            <a href="{{ $urlAppend }}modules/create_course/course_units_activities.php?course={{ $course_code }}&edit_act={{ $id }}">
                                                <span class="fa fa-pencil ms-2 mt-1" data-bs-toggle="tooltip" data-bs-placement="bottom" title data-bs-original-title="{{ trans('langEdit') }}"></span>
                                            </a>
                                        </div>
                                    </h3>
                                </div>
                                <div class='card-body'>
                                    <table class='table-default'>
                                        <tbody>
                                        @if ($q_in_home)
                                            </tr><tr><th scope='row'><label class='col-12 control-label'>{{ trans('langActInHome') }}</label></th>
                                            @foreach ($q_in_home as $in_home)
                                                @php
                                                    $act_title = q($activities[$in_home->activity_id]['title']);
                                                    $vis = $in_home->visible;
                                                    $class_vis = $vis == 0 ? 'not_visible not_visible_unit' : '';
                                                    $act_indirect = $in_home->ID;
                                                @endphp

                                                <td><span class='col-sm-12 {{$class_vis}} '>{!! $act_title !!}</span></td>
                                                <td class='text-center'>
                                                    {!! action_button(array(
                                                        array('title' => trans('langAdd') . ' ' . trans('langInsertExercise'),
                                                            'url' => $base_url . 'exercise&fc_type=0&act_name=' . $act_title . '&act_id=' . $in_home->activity_id,
                                                            'icon' => 'fa fa-file-pen',
                                                            'level' => 'secondary',
                                                            'show' => !is_module_disable_FC(MODULE_ID_EXERCISE, $course_code, $id, $in_home->activity_id)),

                                                        array('title' => trans('langAdd') . ' ' . trans('langInsertDoc'),
                                                            'url' => $base_url . 'doc&fc_type=0&act_name=' . $act_title . '&act_id=' . $in_home->activity_id,
                                                            'icon' => 'fa fa-folder',
                                                            'level' => 'secondary',
                                                            'show' => !is_module_disable_FC(MODULE_ID_DOCS, $course_code, $id, $in_home->activity_id)),

                                                        array('title' =>trans('langAdd') . ' ' . trans('langInsertLink'),
                                                            'url' => $base_url . 'link&fc_type=0&act_name=' . $act_title . '&act_id=' . $in_home->activity_id,
                                                            'icon' => 'fa fa-link',
                                                            'level' => 'secondary',
                                                            'show' => !is_module_disable_FC(MODULE_ID_LINKS, $course_code, $id, $in_home->activity_id)),

                                                        array('title' => trans('langAdd') . ' ' . trans('langLearningPath1'),
                                                            'url' => $base_url . 'lp&fc_type=0&act_name=' . $act_title . '&act_id=' . $in_home->activity_id,
                                                            'icon' => 'fa fa-timeline',
                                                            'level' => 'secondary',
                                                            'show' => !is_module_disable_FC(MODULE_ID_LP, $course_code, $id, $in_home->activity_id)),

                                                        array('title' => trans('langAdd') . ' ' . trans('langInsertVideo'),
                                                            'url' => $base_url . 'video&fc_type=0&act_name=' . $act_title . '&act_id=' . $in_home->activity_id,
                                                            'icon' => 'fa fa-film',
                                                            'level' => 'secondary',
                                                            'show' => !is_module_disable_FC(MODULE_ID_VIDEO, $course_code, $id, $in_home->activity_id)),

                                                        array('title' => trans('langAdd') . ' ' . trans('langInsertForum'),
                                                            'url' => $base_url . 'forum&fc_type=0&act_name=' . $act_title . '&act_id=' . $in_home->activity_id,
                                                            'icon' => 'fa-regular fa-comment',
                                                            'level' => 'secondary',
                                                            'show' => !is_module_disable_FC(MODULE_ID_FORUM, $course_code, $id, $in_home->activity_id)),

                                                        array('title' => trans('langAdd') . ' ' . trans('langInsertEBook'),
                                                            'url' => $base_url . 'ebook&fc_type=0&act_name=' . $act_title . '&act_id=' . $in_home->activity_id,
                                                            'icon' => 'fa fa-upload',
                                                            'level' => 'secondary',
                                                            'show' => !is_module_disable_FC(MODULE_ID_EBOOK, $course_code, $id, $in_home->activity_id)),

                                                        array('title' => trans('langAdd') . ' ' . trans('langInsertWork'),
                                                            'url' => $base_url . 'work&fc_type=0&act_name=' . $act_title . '&act_id=' . $in_home->activity_id,
                                                            'icon' => 'fa fa-upload',
                                                            'level' => 'secondary',
                                                            'show' => !is_module_disable_FC(MODULE_ID_ASSIGN, $course_code, $id, $in_home->activity_id)),

                                                        array('title' =>trans('langAdd') . ' ' . trans('langInsertPoll'),
                                                            'url' => $base_url . 'poll&fc_type=0&act_name=' . $act_title . '&act_id=' . $in_home->activity_id,
                                                            'icon' => 'fa fa-question',
                                                            'level' => 'secondary',
                                                            'show' => !is_module_disable_FC(MODULE_ID_QUESTIONNAIRE, $course_code, $id, $in_home->activity_id)),

                                                        array('title' =>trans('langAdd') . ' ' . trans('langInsertWiki'),
                                                            'url' => $base_url . 'wiki&fc_type=0&act_name=' . $act_title . '&act_id=' . $in_home->activity_id,
                                                            'icon' => 'fa fa-w',
                                                            'level' => 'secondary',
                                                            'show' => !is_module_disable_FC(MODULE_ID_WIKI, $course_code, $id, $in_home->activity_id)),

                                                        array('title' => trans('langAdd') . ' ' . trans('langInsertChat'),
                                                            'url' => $base_url . 'chat&fc_type=0&act_name=' . $act_title . '&act_id=' . $in_home->activity_id,
                                                            'icon' => 'fa-regular fa-comment-dots',
                                                            'level' => 'secondary',
                                                            'show' => !is_module_disable_FC(MODULE_ID_CHAT, $course_code, $id, $in_home->activity_id)),

                                                        array('title' => trans('langAdd') . ' ' . trans('langInsertTcMeeting'),
                                                            'url' => $base_url . 'tc&fc_type=0&act_name=' . $act_title . '&act_id=' . $in_home->activity_id,
                                                            'icon' => 'fa fa-exchange',
                                                            'level' => 'secondary',
                                                            'show' => !is_module_disable_FC(MODULE_ID_TC, $course_code, $id, $in_home->activity_id) && is_enabled_tc_server($course_id)),

                                                        array('title' => trans('langAdd') . ' ' . trans('langGlossary'),
                                                            'url' => $base_url . 'glossary&fc_type=0&act_name=' . $act_title . '&act_id=' . $in_home->activity_id,
                                                            'icon' => 'fa fa-list',
                                                            'level' => 'secondary',
                                                            'show' => !is_module_disable_FC(MODULE_ID_GLOSSARY, $course_code, $id, $in_home->activity_id)),

                                                        array('title' => trans('langAdd') . ' ' . trans('langWall'),
                                                            'url' => $base_url . 'wall&fc_type=0&act_name=' . $act_title . '&act_id=' . $in_home->activity_id,
                                                            'icon' => 'fa-solid fa-quote-left',
                                                            'level' => 'secondary',
                                                            'show' => !is_module_disable_FC(MODULE_ID_WALL, $course_code, $id, $in_home->activity_id)),

                                                        array('title' => trans('langAdd') . ' ' . trans('langMindmap'),
                                                            'url' => $base_url . 'mindmap&fc_type=0&act_name=' . $act_title . '&act_id=' . $in_home->activity_id,
                                                            'icon' => 'fa fa-sitemap',
                                                            'level' => 'secondary',
                                                            'show' => !is_module_disable_FC(MODULE_ID_MINDMAP, $course_code, $id, $in_home->activity_id)),

                                                        array('title' => trans('langAdd') . ' ' . trans('langBlog'),
                                                            'url' => $base_url . 'blog&fc_type=0&act_name=' . $act_title . '&act_id=' . $in_home->activity_id,
                                                            'icon' => 'fa-solid fa-globe',
                                                            'level' => 'secondary',
                                                            'show' => !is_module_disable_FC(MODULE_ID_BLOG, $course_code, $id, $in_home->activity_id)),

                                                        array('title' => trans('langAdd') . ' ' . trans('langComments'),
                                                            'url' => $base_url . 'comments&fc_type=0&act_name=' . $act_title . '&act_id=' . $in_home->activity_id,
                                                            'icon' => 'fa fa-comments',
                                                            'level' => 'secondary',
                                                            'show' => !is_module_disable_FC(MODULE_ID_COMMENTS, $course_code, $id, $in_home->activity_id)),

                                                        array('title' => trans('langAdd') . ' ' . trans('langGradebook'),
                                                            'url' => $base_url . 'gradebook&fc_type=0&act_name=' . $act_title . '&act_id=' . $in_home->activity_id,
                                                            'icon' => 'fa-solid fa-a',
                                                            'level' => 'secondary',
                                                            'show' => !is_module_disable_FC(MODULE_ID_GRADEBOOK, $course_code, $id, $in_home->activity_id)),

                                                        array('title' => trans('langAdd') . ' ' . trans('langProgress'),
                                                            'url' => $base_url . 'progress&fc_type=0&act_name=' . $act_title . '&act_id=' . $in_home->activity_id,
                                                            'icon' => 'fa-solid fa-arrow-trend-up',
                                                            'level' => 'secondary',
                                                            'show' => !is_module_disable_FC(MODULE_ID_PROGRESS, $course_code, $id, $in_home->activity_id))

                                                        ),'',true)
                                                    !!}
                                                </td>
                                                <td class='text-center'>
                                                    {!!
                                                        action_button(array(
                                                            array('title' => $vis == 1 ? trans('langViewHide') : trans('langViewShow'),
                                                                'url' => "$_SERVER[REQUEST_URI]&vis_act=$in_home->ID",
                                                                'icon' => $vis == 1 ? 'fa-eye-slash' : 'fa-eye'),
                                                            array('title' => trans('langDelete'),
                                                                'url' => "$_SERVER[REQUEST_URI]&del_act=$in_home->ID&actid=" . $in_home->activity_id,
                                                                'icon' => 'fa-xmark',
                                                                'class' => 'delete',
                                                                'confirm' => trans('langUnitActivityDeleteConfirm'))))
                                                    !!}
                                                </td>
                                            </tr><tr><td></td>
                                            @endforeach
                                        @endif

                                        @if($q_in_class)
                                            <tr><th scope='row'><label class='col-12 control-label'>{{ trans('langActInClass') }}</label></th>


                                            @foreach($q_in_class as $in_class)

                                                @php
                                                    $act_title = q($activities[$in_class->activity_id]['title']);
                                                    $vis = $in_class->visible;
                                                    $class_vis = $vis == 0  ? 'not_visible not_visible_unit' : '';
                                                    $act_indirect = $in_class->activity_id;
                                                @endphp

                                                <td><span class='col-sm-12  {!! $class_vis !!} '>{!! $act_title !!}</span></td>
                                                @if($is_editor)
                                                    <td class='text-center'>
                                                    {!! action_button(array(
                                                            array('title' => trans('langAdd').' '.trans('langInsertExercise'),
                                                                'url' => $base_url . 'exercise&fc_type=1&act_name='. $act_title. '&act_id='.$in_class->activity_id,
                                                                'icon' => 'fa fa-square-pen',
                                                                'level' => 'secondary',
                                                                'show' => !is_module_disable_FC(MODULE_ID_EXERCISE,$course_code,$id,$in_class->activity_id)),

                                                            array('title' => trans('langAdd').' '.trans('langInsertDoc'),
                                                                'url' => $base_url . 'doc&fc_type=1&act_name='. $act_title. '&act_id='.$in_class->activity_id,
                                                                'icon' => 'fa fa-folder-open',
                                                                'level' => 'secondary',
                                                                'show' => !is_module_disable_FC(MODULE_ID_DOCS,$course_code,$id,$in_class->activity_id)),

                                                            array('title' => trans('langAdd').' '.trans('langInsertLink'),
                                                                'url' => $base_url . 'link&fc_type=1&act_name='. $act_title. '&act_id='.$in_class->activity_id,
                                                                'icon' => 'fa fa-link',
                                                                'level' => 'secondary',
                                                                'show' => !is_module_disable_FC(MODULE_ID_LINKS,$course_code,$id,$in_class->activity_id)),

                                                            array('title' => trans('langAdd').' '.trans('langLearningPath1'),
                                                                'url' => $base_url . 'lp&fc_type=1&act_name='. $act_title. '&act_id='.$in_class->activity_id,
                                                                'icon' => 'fa fa-timeline',
                                                                'level' => 'secondary',
                                                                'show' => !is_module_disable_FC(MODULE_ID_LP,$course_code,$id,$in_class->activity_id)),

                                                            array('title' => trans('langAdd').' '.trans('langInsertVideo'),
                                                                'url' => $base_url . 'video&fc_type=1&act_name='. $act_title. '&act_id='.$in_class->activity_id,
                                                                'icon' => 'fa fa-film',
                                                                'level' => 'secondary',
                                                                'show' => !is_module_disable_FC(MODULE_ID_VIDEO,$course_code,$id,$in_class->activity_id)),

                                                            array('title' =>trans('langAdd').' '.trans('langInsertForum'),
                                                                'url' => $base_url . 'forum&fc_type=1&act_name='. $act_title. '&act_id='.$in_class->activity_id,
                                                                'icon' => 'fa fa-tablet',
                                                                'level' => 'secondary',
                                                                'show' => !is_module_disable_FC(MODULE_ID_FORUM,$course_code,$id,$in_class->activity_id)),

                                                            array('title' => trans('langAdd').' '. trans('langInsertEBook'),
                                                                'url' => $base_url . 'ebook&fc_type=1&act_name='. $act_title. '&act_id='.$in_class->activity_id,
                                                                'icon' => 'fa fa-book-atlas',
                                                                'level' => 'secondary',
                                                                'show' =>  !is_module_disable_FC(MODULE_ID_EBOOK,$course_code,$id,$in_class->activity_id)),

                                                            array('title' => trans('langAdd').' '.trans('langInsertWork'),
                                                                'url' => $base_url . 'work&fc_type=1&act_name='. $act_title. '&act_id='.$in_class->activity_id,
                                                                'icon' => 'fa fa-upload',
                                                                'level' => 'secondary',
                                                                'show' => !is_module_disable_FC(MODULE_ID_ASSIGN,$course_code,$id,$in_class->activity_id)),

                                                            array('title' => trans('langAdd').' '. trans('langInsertPoll'),
                                                                'url' => $base_url . 'poll&fc_type=1&act_name='. $act_title. '&act_id='.$in_class->activity_id,
                                                                'icon' => 'fa fa-question',
                                                                'level' => 'secondary',
                                                                'show' => !is_module_disable_FC(MODULE_ID_QUESTIONNAIRE,$course_code,$id,$in_class->activity_id)),

                                                            array('title' => trans('langAdd').' '. trans('langInsertWiki'),
                                                                'url' => $base_url . 'wiki&fc_type=1&act_name='. $act_title. '&act_id='.$in_class->activity_id,
                                                                'icon' => 'fa fa-w',
                                                                'level' => 'secondary',
                                                                'show' => !is_module_disable_FC(MODULE_ID_WIKI,$course_code,$id,$in_class->activity_id)),

                                                            array('title' => trans('langAdd').' '. trans('langInsertChat'),
                                                                'url' => $base_url . 'chat&fc_type=1&act_name='. $act_title. '&act_id='.$in_class->activity_id,
                                                                'icon' => 'fa fa-exchange',
                                                                'level' => 'secondary',
                                                                'show' => !is_module_disable_FC(MODULE_ID_CHAT,$course_code,$id,$in_class->activity_id)),

                                                            array('title' => trans('langAdd').' '.trans('langInsertTcMeeting'),
                                                                'url' => $base_url . 'tc&fc_type=1&act_name='. $act_title. '&act_id='.$in_class->activity_id,
                                                                'icon' => 'fa fa-exchange',
                                                                'level' => 'secondary',
                                                                'show' => !is_module_disable_FC(MODULE_ID_TC,$course_code,$id,$in_class->activity_id) && is_enabled_tc_server($course_id)),

                                                            array('title' => trans('langAdd').' '.trans('langGlossary'),
                                                                'url' => $base_url . 'glossary&fc_type=1&act_name='. $act_title. '&act_id='.$in_class->activity_id,
                                                                'icon' => 'fa fa-list',
                                                                'level' => 'secondary',
                                                                'show' => !is_module_disable_FC(MODULE_ID_GLOSSARY,$course_code,$id,$in_class->activity_id)),

                                                            array('title' => trans('langAdd').' '.trans('langWall'),
                                                                'url' => $base_url . 'wall&fc_type=1&act_name='. $act_title. '&act_id='.$in_class->activity_id,
                                                                'icon' => 'fa-solid fa-quote-left',
                                                                'level' => 'secondary',
                                                                'show' => !is_module_disable_FC(MODULE_ID_WALL,$course_code,$id,$in_class->activity_id)),

                                                            array('title' => trans('langAdd').' '.trans('langMindmap'),
                                                                'url' => $base_url . 'mindmap&fc_type=1&act_name='. $act_title. '&act_id='.$in_class->activity_id,
                                                                'icon' => 'fa fa-sitemap',
                                                                'level' => 'secondary',
                                                                'show' => !is_module_disable_FC(MODULE_ID_MINDMAP,$course_code,$id,$in_class->activity_id)),

                                                            array('title' => trans('langAdd').' '.trans('langGroups'),
                                                                'url' => $base_url . 'group&fc_type=1&act_name='. $act_title. '&act_id='.$in_class->activity_id,
                                                                'icon' => 'fa fa-users',
                                                                'level' => 'secondary',
                                                                'show' => !is_module_disable_FC(MODULE_ID_GROUPS,$course_code,$id,$in_class->activity_id)),

                                                            array('title' => trans('langAdd').' '.trans('langBlog'),
                                                                'url' => $base_url . 'blog&fc_type=1&act_name='. $act_title. '&act_id='.$in_class->activity_id,
                                                                'icon' => 'fa-solid fa-globe',
                                                                'level' => 'secondary',
                                                                'show' => !is_module_disable_FC(MODULE_ID_BLOG,$course_code,$id,$in_class->activity_id)),

                                                            array('title' => trans('langAdd').' '.trans('langComments'),
                                                                'url' => $base_url . 'comments&fc_type=1&act_name='. $act_title. '&act_id='.$in_class->activity_id,
                                                                'icon' => 'fa fa-comments',
                                                                'level' => 'secondary',
                                                                'show' => !is_module_disable_FC(MODULE_ID_COMMENTS,$course_code,$id,$in_class->activity_id)),

                                                            array('title' => trans('langAdd').' '.trans('langGradebook'),
                                                                'url' => $base_url . 'gradebook&fc_type=1&act_name='. $act_title. '&act_id='.$in_class->activity_id,
                                                                'icon' => 'fa-solid fa-a',
                                                                'level' => 'secondary',
                                                                'show' => !is_module_disable_FC(MODULE_ID_GRADEBOOK,$course_code,$id,$in_class->activity_id)),

                                                            array('title' => trans('langAdd').' '.trans('langProgress'),
                                                                'url' => $base_url . 'progress&fc_type=1&act_name='. $act_title. '&act_id='.$in_class->activity_id,
                                                                'icon' => 'fa-solid fa-arrow-trend-up',
                                                                'level' => 'secondary',
                                                                'show' => !is_module_disable_FC(MODULE_ID_PROGRESS,$course_code,$id,$in_class->activity_id))

                                                        ),'',true) !!}
                                                    </td>
                                                    <td class='text-center'>

                                                    {!!
                                                        action_button(array(
                                                            array('title' => $vis == 1? trans('langViewHide') : trans('langViewShow'),
                                                            'url' => "$_SERVER[REQUEST_URI]&vis_act=$in_class->ID",
                                                            'icon' => $vis == 1? 'fa-eye-slash' : 'fa-eye'),

                                                            array('title' => trans('langDelete'),
                                                                'url' => "$_SERVER[REQUEST_URI]&del_act=$in_class->ID&actid=".$in_class->activity_id,
                                                                'icon' => 'fa-xmark',
                                                                'class' => 'delete',
                                                                'confirm' => trans('langUnitActivityDeleteConfirm'))))
                                                    !!}

                                                @endif
                                                </td></tr><tr><td></td>
                                            @endforeach
                                        @endif


                                        @if($q_after_class)
                                            <tr><th scope='row'><label class='col-md-auto control-label'>{{ trans('langActAfterClass') }}</label></th>

                                            @foreach($q_after_class as $after_class)

                                                @php
                                                    $act_title = q($activities[$after_class->activity_id]['title']);
                                                    $vis = $after_class->visible;
                                                    $class_vis = $vis == 0  ? 'not_visible not_visible_unit' : '';
                                                    $act_indirect = $after_class->ID;
                                                @endphp


                                                <td><span class='col-sm-12 {!! $class_vis !!} '>{!! $act_title !!}</span></td>
                                                <td class='text-center'> {!! action_button(array(
                                                    array('title' => trans('langAdd').' '.trans('langInsertExercise'),
                                                        'url' => $base_url . 'exercise&fc_type=2&act_name='. $act_title,
                                                        'icon' => 'fa fa-square-pen',
                                                        'level' => 'secondary',
                                                        'show' => !is_module_disable_FC(MODULE_ID_EXERCISE,$course_code,$id,$after_class->activity_id)),

                                                    array('title' => trans('langAdd').' '.trans('langInsertDoc'),
                                                        'url' => $base_url . 'doc&fc_type=2&act_name='. $act_title. '&act_id='.$after_class->activity_id,
                                                        'icon' => 'fa fa-folder-open',
                                                        'level' => 'secondary',
                                                        'show' => !is_module_disable_FC(MODULE_ID_DOCS,$course_code,$id,$after_class->activity_id)),

                                                    array('title' => trans('langAdd').' '.trans('langInsertLink'),
                                                        'url' => $base_url . 'link&fc_type=2&act_name='. $act_title. '&act_id='.$after_class->activity_id,
                                                        'icon' => 'fa fa-link',
                                                        'level' => 'secondary',
                                                        'show' => !is_module_disable_FC(MODULE_ID_LINKS,$course_code,$id,$after_class->activity_id)),

                                                    array('title' => trans('langAdd').' '.trans('langLearningPath1'),
                                                        'url' => $base_url . 'lp&fc_type=2&act_name='. $act_title. '&act_id='.$after_class->activity_id,
                                                        'icon' => 'fa fa-ellipsis-h',
                                                        'level' => 'secondary',
                                                        'show' => !is_module_disable_FC(MODULE_ID_LP,$course_code,$id,$after_class->activity_id)),

                                                    array('title' => trans('langAdd').' '.trans('langInsertVideo'),
                                                        'url' => $base_url . 'video&fc_type=2&act_name='. $act_title. '&act_id='.$after_class->activity_id,
                                                        'icon' => 'fa fa-film',
                                                        'level' => 'secondary',
                                                        'show' => !is_module_disable_FC(MODULE_ID_VIDEO,$course_code,$id,$after_class->activity_id)),

                                                    array('title' => trans('langAdd').' '.trans('langInsertForum'),
                                                        'url' => $base_url . 'forum&fc_type=2&act_name='. $act_title. '&act_id='.$after_class->activity_id,
                                                        'icon' => 'fa fa-comments',
                                                        'level' => 'secondary',
                                                        'show' => !is_module_disable_FC(MODULE_ID_FORUM,$course_code,$id,$after_class->activity_id)),

                                                    array('title' => trans('langAdd').' '.trans('langInsertEBook'),
                                                        'url' => $base_url . 'ebook&fc_type=2&act_name='. $act_title. '&act_id='.$after_class->activity_id,
                                                        'icon' => 'fa fa-book',
                                                        'level' => 'secondary',
                                                        'show' =>  !is_module_disable_FC(MODULE_ID_EBOOK,$course_code,$id,$after_class->activity_id)),

                                                    array('title' => trans('langAdd').' '.trans('langInsertWork'),
                                                        'url' => $base_url . 'work&fc_type=2&act_name='. $act_title. '&act_id='.$after_class->activity_id,
                                                        'icon' => 'fa fa-flask',
                                                        'level' => 'secondary',
                                                        'show' => !is_module_disable_FC(MODULE_ID_ASSIGN,$course_code,$id,$after_class->activity_id)),

                                                    array('title' => trans('langAdd').' '.trans('langInsertPoll'),
                                                        'url' => $base_url . 'poll&fc_type=2&act_name='. $act_title. '&act_id='.$after_class->activity_id,
                                                        'icon' => 'fa fa-question-circle',
                                                        'level' => 'secondary',
                                                        'show' => !is_module_disable_FC(MODULE_ID_QUESTIONNAIRE,$course_code,$id,$after_class->activity_id)),

                                                    array('title' => trans('langAdd').' '.trans('langInsertWiki'),
                                                        'url' => $base_url . 'wiki&fc_type=2&act_name='. $act_title. '&act_id='.$after_class->activity_id,
                                                        'icon' => 'fa fa-won-sign',
                                                        'level' => 'secondary',
                                                        'show' => !is_module_disable_FC(MODULE_ID_WIKI,$course_code,$id,$after_class->activity_id)),

                                                    array('title' => trans('langAdd').' '.trans('langInsertChat'),
                                                        'url' => $base_url . 'chat&fc_type=2&act_name='. $act_title. '&act_id='.$after_class->activity_id,
                                                        'icon' => 'fa fa-exchange',
                                                        'level' => 'secondary',
                                                        'show' => !is_module_disable_FC(MODULE_ID_CHAT,$course_code,$id,$after_class->activity_id)),

                                                    array('title' => trans('langAdd').' '.trans('langInsertTcMeeting'),
                                                        'url' => $base_url . 'tc&fc_type=2&act_name='. $act_title. '&act_id='.$after_class->activity_id,
                                                        'icon' => 'fa fa-exchange',
                                                        'level' => 'secondary',
                                                        'show' => !is_module_disable_FC(MODULE_ID_TC,$course_code,$id,$after_class->activity_id) && is_enabled_tc_server($course_id)),

                                                    array('title' => trans('langAdd').' '.trans('langGlossary'),
                                                        'url' => $base_url . 'glossary&fc_type=2&act_name='. $act_title. '&act_id='.$after_class->activity_id,
                                                        'icon' => 'fa fa-list',
                                                        'level' => 'secondary',
                                                        'show' => !is_module_disable_FC(MODULE_ID_GLOSSARY,$course_code,$id,$after_class->activity_id)),

                                                    array('title' => trans('langAdd').' '.trans('langWall'),
                                                        'url' => $base_url . 'wall&fc_type=2&act_name='. $act_title. '&act_id='.$after_class->activity_id,
                                                        'icon' => 'fa-solid fa-quote-left',
                                                        'level' => 'secondary',
                                                        'show' => !is_module_disable_FC(MODULE_ID_WALL,$course_code,$id,$after_class->activity_id)),

                                                    array('title' => trans('langAdd').' '.trans('langMindmap'),
                                                        'url' => $base_url . 'mindmap&fc_type=2&act_name='. $act_title. '&act_id='.$after_class->activity_id,
                                                        'icon' => 'fa fa-sitemap',
                                                        'level' => 'secondary',
                                                        'show' => !is_module_disable_FC(MODULE_ID_MINDMAP,$course_code,$id,$after_class->activity_id)),

                                                    array('title' => trans('langAdd').' '.trans('langGroups'),
                                                        'url' => $base_url . 'group&fc_type=2&act_name=='. $act_title. '&act_id='.$after_class->activity_id,
                                                        'icon' => 'fa fa-users',
                                                        'level' => 'secondary',
                                                        'show' => !is_module_disable_FC(MODULE_ID_GROUPS,$course_code,$id,$after_class->activity_id)),

                                                    array('title' => trans('langAdd').' '.trans('langBlog'),
                                                        'url' => $base_url . 'blog&fc_type=2&act_name='. $act_title. '&act_id='.$after_class->activity_id,
                                                        'icon' => 'fa-solid fa-globe',
                                                        'level' => 'secondary',
                                                        'show' => !is_module_disable_FC(MODULE_ID_BLOG,$course_code,$id,$after_class->activity_id)),

                                                    array('title' => trans('langAdd').' '.trans('langComments'),
                                                        'url' => $base_url . 'comments&fc_type=2&act_name='. $act_title. '&act_id='.$after_class->activity_id,
                                                        'icon' => 'fa fa-comments',
                                                        'level' => 'secondary',
                                                        'show' => !is_module_disable_FC(MODULE_ID_COMMENTS,$course_code,$id,$after_class->activity_id)),

                                                    array('title' => trans('langAdd').' '.trans('langGradebook'),
                                                        'url' => $base_url . 'gradebook&fc_type=2&act_name='. $act_title. '&act_id='.$after_class->activity_id,
                                                        'icon' => 'fa-solid fa-a',
                                                        'level' => 'secondary',
                                                        'show' => !is_module_disable_FC(MODULE_ID_GRADEBOOK,$course_code,$id,$after_class->activity_id)),

                                                    array('title' => trans('langAdd').' '.trans('langProgress'),
                                                        'url' => $base_url . 'progress&fc_type=2&act_name='. $act_title. '&act_id='.$after_class->activity_id,
                                                        'icon' => 'fa-solid fa-arrow-trend-up',
                                                        'level' => 'secondary',
                                                        'show' => !is_module_disable_FC(MODULE_ID_PROGRESS,$course_code,$id,$after_class->activity_id))

                                                ),'',true) !!}</td><td class='text-center'>


                                                {!! action_button(array(
                                                    array('title' => $vis == 1? trans('langViewHide') : trans('langViewShow'),
                                                    'url' => "$_SERVER[REQUEST_URI]&vis_act=$after_class->ID",
                                                    'icon' => $vis == 1? 'fa-eye-slash' : 'fa-eye'),

                                                    array('title' => trans('langDelete'),
                                                        'url' => "$_SERVER[REQUEST_URI]&del_act=$after_class->ID&actid=".$after_class->activity_id,
                                                        'icon' => 'fa-xmark',
                                                        'class' => 'delete',
                                                        'confirm' => trans('langUnitActivityDeleteConfirm')))) !!}

                                                </td></tr><tr><td></td>
                                            @endforeach
                                            </tr>
                                        @endif

                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endif




                </div>
            </div>
        </div>
    </div>
</div>

@endsection
