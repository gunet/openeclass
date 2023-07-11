@extends('layouts.default')

@section('content')

<div class="col-12 basic-section p-xl-5 px-lg-3 py-lg-5">

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

                    @if ($is_editor and $q->flipped_flag != 2)
                                {!! action_bar(array(
                                    array('title' => trans('langEditUnitSection'),
                                        'url' => $editUrl,
                                        'icon' => 'fa fa-edit',
                                        'level' => 'primary-label',
                                        'button-class' => 'btn-success'),
                                    array('title' => trans('langUnitManage'),
                                        'url' => $manageUrl,
                                        'icon' => 'fa fa-edit',
                                        'level' => 'primary-label',
                                        'button-class' => 'btn-success'),
                                    array('title' => trans('langAdd') . ' ' . trans('langInsertExercise'),
                                        'url' => $insertBaseUrl . 'exercise',
                                        'icon' => 'fa fa-square-pen',
                                        'level' => 'secondary',
                                        'show' => !is_module_disable(MODULE_ID_EXERCISE)),
                                    array('title' => trans('langAdd') . ' ' . trans('langInsertDoc'),
                                        'url' => $insertBaseUrl . 'doc',
                                        'icon' => 'fa fa-folder-open',
                                        'level' => 'secondary',
                                        'show' => !is_module_disable(MODULE_ID_DOCS)),
                                    array('title' => trans('langAdd') . ' ' . trans('langInsertText'),
                                        'url' => $insertBaseUrl . 'text',
                                        'icon' => 'fa fa-file-lines',
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
                                    array('title' => trans('langAdd') .' '. trans('langOfH5p'),
                                        'url' => $insertBaseUrl . 'h5p',
                                        'icon' => 'fa fa-tablet',
                                        'level' => 'secondary',
                                        'show' => !is_module_disable(MODULE_ID_H5P)),
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
                                        'icon' => 'fa fa-won-sign',
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
                                        'show' => (!is_module_disable(MODULE_ID_TC) && is_enabled_tc_server($course_id)))
                                    )) !!}

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
                    
                    <div class='col-12'>
                        <form class='form-horizontal' name='unitselect' action='{{ $urlAppend }}modules/units/index.php' method='get'>
                            <input type='hidden' name='course' value='{{ $course_code }}'>
                            <div class='form-group'>
                                <label class='control-label-notes' for='id'><span class='fa fa-search'></span>&nbsp{{ trans('langCourseUnits') }}</label>
                                <select name='id' id='id' class='form-select' onchange='document.unitselect.submit()'>
                                    @foreach ($units as $unit)
                                        <option value='{{ $unit->id }}' {{ $unit->id == $unitId ? 'selected' : '' }}>
                                            {{ ellipsize($unit->title, 50) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </form>
                    </div>

                    @if(Session::has('message'))
                        <div class='col-12 mt-4'>
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

                    {{--
                    @if ($previousLink or $nextLink)

                        <div class='col-12'>
                            <div class='card panelCard px-lg-0 py-lg-0 border-0'>
                                <div class="card-body d-flex justify-content-between align-items-center p-3 bg-light Borders">              
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
                    --}}


                    <div class='col-12 mt-4'>
                        <div class="card panelCard px-lg-4 py-lg-3">
                            <div class='card-header border-0 bg-white d-xl-flex justify-content-xl-between align-items-xl-center'>
                                <div class='text-uppercase normalColorBlueText TextBold fs-6'>{{ $pageName }}</div>
                                @if($course_start_week or $course_finish_week)
                                    <div class='normalColorBlueText TextSemiBold'>
                                        <small>{{ $course_start_week }}&nbsp{{ $course_finish_week }}</small>
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
                                <div class='card-footer small-text bg-white border-0'>
                                    <small><span class='text-muted'>{{ trans('langTags') }}:</span> {!! $tags_list !!}</small>
                                </div>
                            @endif
                        </div>
                    </div>


                    @if ($is_editor and $q->flipped_flag == 2)
                        <div class='col-12 mt-4'>
                            <div class='card panelCard px-lg-4 py-lg-3'>
                                <div class='card-header border-0 bg-white d-flex justify-content-between align-items-center'>
                                    <div class='text-uppercase normalColorBlueText TextBold fs-6'>
                                        <div class='d-inline-flex align-items-top'>
                                            {{ trans('langActivities')}}
                                            <a href="{{ $urlAppend }}modules/create_course/course_units_activities.php?course={{ $course_code }}&edit_act={{ $id }}">
                                                <span class="fa fa-pencil ms-2" data-bs-toggle="tooltip" data-bs-placement="bottom" title data-bs-original-title="{{ trans('langEdit') }}"></span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <div class='card-body'>
                                    <table class='table table-bordered'>
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

                                                <td><span class='col-sm-12 {{$class_vis}} text-secondary fs-6'>{!! $act_title !!}</span></td>
                                                <td class='text-center'>
                                                    {!! action_button(array(
                                                        array('title' => trans('langAdd') . ' ' . trans('langInsertExercise'),
                                                            'url' => $base_url . 'exercise&fc_type=0&act_name=' . $act_title . '&act_id=' . $in_home->activity_id,
                                                            'icon' => 'fa fa-square-pen',
                                                            'level' => 'secondary',
                                                            'show' => !is_module_disable_FC(MODULE_ID_EXERCISE, $course_code, $id, $in_home->activity_id)),

                                                        array('title' => trans('langAdd') . ' ' . trans('langInsertDoc'),
                                                            'url' => $base_url . 'doc&fc_type=0&act_name=' . $act_title . '&act_id=' . $in_home->activity_id,
                                                            'icon' => 'fa fa-folder-open',
                                                            'level' => 'secondary',
                                                            'show' => !is_module_disable_FC(MODULE_ID_DOCS, $course_code, $id, $in_home->activity_id)),

                                                        array('title' =>trans('langAdd') . ' ' . trans('langInsertLink'),
                                                            'url' => $base_url . 'link&fc_type=0&act_name=' . $act_title . '&act_id=' . $in_home->activity_id,
                                                            'icon' => 'fa fa-link',
                                                            'level' => 'secondary',
                                                            'show' => !is_module_disable_FC(MODULE_ID_LINKS, $course_code, $id, $in_home->activity_id)),

                                                        array('title' => trans('langAdd') . ' ' . trans('langLearningPath1'),
                                                            'url' => $base_url . 'lp&fc_type=0&act_name=' . $act_title . '&act_id=' . $in_home->activity_id,
                                                            'icon' => 'fa fa-ellipsis-h',
                                                            'level' => 'secondary',
                                                            'show' => !is_module_disable_FC(MODULE_ID_LP, $course_code, $id, $in_home->activity_id)),

                                                        array('title' => trans('langAdd') . ' ' . trans('langInsertVideo'),
                                                            'url' => $base_url . 'video&fc_type=0&act_name=' . $act_title . '&act_id=' . $in_home->activity_id,
                                                            'icon' => 'fa fa-film',
                                                            'level' => 'secondary',
                                                            'show' => !is_module_disable_FC(MODULE_ID_VIDEO, $course_code, $id, $in_home->activity_id)),

                                                        array('title' => trans('langAdd') . ' ' . trans('langInsertForum'),
                                                            'url' => $base_url . 'forum&fc_type=0&act_name=' . $act_title . '&act_id=' . $in_home->activity_id,
                                                            'icon' => 'fa fa-comments',
                                                            'level' => 'secondary',
                                                            'show' => !is_module_disable_FC(MODULE_ID_FORUM, $course_code, $id, $in_home->activity_id)),

                                                        array('title' => trans('langAdd') . ' ' . trans('langInsertEBook'),
                                                            'url' => $base_url . 'ebook&fc_type=0&act_name=' . $act_title . '&act_id=' . $in_home->activity_id,
                                                            'icon' => 'fa fa-book',
                                                            'level' => 'secondary',
                                                            'show' => !is_module_disable_FC(MODULE_ID_EBOOK, $course_code, $id, $in_home->activity_id)),

                                                        array('title' => trans('langAdd') . ' ' . trans('langInsertWork'),
                                                            'url' => $base_url . 'work&fc_type=0&act_name=' . $act_title . '&act_id=' . $in_home->activity_id,
                                                            'icon' => 'fa fa-flask',
                                                            'level' => 'secondary',
                                                            'show' => !is_module_disable_FC(MODULE_ID_ASSIGN, $course_code, $id, $in_home->activity_id)),

                                                        array('title' =>trans('langAdd') . ' ' . trans('langInsertPoll'),
                                                            'url' => $base_url . 'poll&fc_type=0&act_name=' . $act_title . '&act_id=' . $in_home->activity_id,
                                                            'icon' => 'fa fa-question-circle',
                                                            'level' => 'secondary',
                                                            'show' => !is_module_disable_FC(MODULE_ID_QUESTIONNAIRE, $course_code, $id, $in_home->activity_id)),

                                                        array('title' =>trans('langAdd') . ' ' . trans('langInsertWiki'),
                                                            'url' => $base_url . 'wiki&fc_type=0&act_name=' . $act_title . '&act_id=' . $in_home->activity_id,
                                                            'icon' => 'fa fa-won-sign',
                                                            'level' => 'secondary',
                                                            'show' => !is_module_disable_FC(MODULE_ID_WIKI, $course_code, $id, $in_home->activity_id)),

                                                        array('title' => trans('langAdd') . ' ' . trans('langInsertChat'),
                                                            'url' => $base_url . 'chat&fc_type=0&act_name=' . $act_title . '&act_id=' . $in_home->activity_id,
                                                            'icon' => 'fa fa-exchange',
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
                                                            'icon' => 'fa fa-list',
                                                            'level' => 'secondary',
                                                            'show' => !is_module_disable_FC(MODULE_ID_WALL, $course_code, $id, $in_home->activity_id)),

                                                        array('title' => trans('langAdd') . ' ' . trans('langMindmap'),
                                                            'url' => $base_url . 'mindmap&fc_type=0&act_name=' . $act_title . '&act_id=' . $in_home->activity_id,
                                                            'icon' => 'fa fa-sitemap',
                                                            'level' => 'secondary',
                                                            'show' => !is_module_disable_FC(MODULE_ID_MINDMAP, $course_code, $id, $in_home->activity_id)),

                                                        array('title' => trans('langAdd') . ' ' . trans('langBlog'),
                                                            'url' => $base_url . 'blog&fc_type=0&act_name=' . $act_title . '&act_id=' . $in_home->activity_id,
                                                            'icon' => 'fa fa-columns',
                                                            'level' => 'secondary',
                                                            'show' => !is_module_disable_FC(MODULE_ID_BLOG, $course_code, $id, $in_home->activity_id)),

                                                        array('title' => trans('langAdd') . ' ' . trans('langComments'),
                                                            'url' => $base_url . 'comments&fc_type=0&act_name=' . $act_title . '&act_id=' . $in_home->activity_id,
                                                            'icon' => 'fa fa-comments',
                                                            'level' => 'secondary',
                                                            'show' => !is_module_disable_FC(MODULE_ID_COMMENTS, $course_code, $id, $in_home->activity_id)),

                                                        array('title' => trans('langAdd') . ' ' . trans('langGradebook'),
                                                            'url' => $base_url . 'gradebook&fc_type=0&act_name=' . $act_title . '&act_id=' . $in_home->activity_id,
                                                            'icon' => 'fa fa-sort-numeric-desc',
                                                            'level' => 'secondary',
                                                            'show' => !is_module_disable_FC(MODULE_ID_GRADEBOOK, $course_code, $id, $in_home->activity_id)),

                                                        array('title' => trans('langAdd') . ' ' . trans('langProgress'),
                                                            'url' => $base_url . 'progress&fc_type=0&act_name=' . $act_title . '&act_id=' . $in_home->activity_id,
                                                            'icon' => 'fa fa-trophy',
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

                                                <td><span class='col-sm-12  {!! $class_vis !!} text-secondary fs-6'>{!! $act_title !!}</span></td>
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
                                                                'icon' => 'fa fa-ellipsis-h',
                                                                'level' => 'secondary',
                                                                'show' => !is_module_disable_FC(MODULE_ID_LP,$course_code,$id,$in_class->activity_id)),

                                                            array('title' => trans('langAdd').' '.trans('langInsertVideo'),
                                                                'url' => $base_url . 'video&fc_type=1&act_name='. $act_title. '&act_id='.$in_class->activity_id,
                                                                'icon' => 'fa fa-film',
                                                                'level' => 'secondary',
                                                                'show' => !is_module_disable_FC(MODULE_ID_VIDEO,$course_code,$id,$in_class->activity_id)),

                                                            array('title' =>trans('langAdd').' '.trans('langInsertForum'),
                                                                'url' => $base_url . 'forum&fc_type=1&act_name='. $act_title. '&act_id='.$in_class->activity_id,
                                                                'icon' => 'fa fa-comments',
                                                                'level' => 'secondary',
                                                                'show' => !is_module_disable_FC(MODULE_ID_FORUM,$course_code,$id,$in_class->activity_id)),

                                                            array('title' => trans('langAdd').' '. trans('langInsertEBook'),
                                                                'url' => $base_url . 'ebook&fc_type=1&act_name='. $act_title. '&act_id='.$in_class->activity_id,
                                                                'icon' => 'fa fa-book',
                                                                'level' => 'secondary',
                                                                'show' =>  !is_module_disable_FC(MODULE_ID_EBOOK,$course_code,$id,$in_class->activity_id)),

                                                            array('title' => trans('langAdd').' '.trans('langInsertWork'),
                                                                'url' => $base_url . 'work&fc_type=1&act_name='. $act_title. '&act_id='.$in_class->activity_id,
                                                                'icon' => 'fa fa-flask',
                                                                'level' => 'secondary',
                                                                'show' => !is_module_disable_FC(MODULE_ID_ASSIGN,$course_code,$id,$in_class->activity_id)),

                                                            array('title' => trans('langAdd').' '. trans('langInsertPoll'),
                                                                'url' => $base_url . 'poll&fc_type=1&act_name='. $act_title. '&act_id='.$in_class->activity_id,
                                                                'icon' => 'fa fa-question-circle',
                                                                'level' => 'secondary',
                                                                'show' => !is_module_disable_FC(MODULE_ID_QUESTIONNAIRE,$course_code,$id,$in_class->activity_id)),

                                                            array('title' => trans('langAdd').' '. trans('langInsertWiki'),
                                                                'url' => $base_url . 'wiki&fc_type=1&act_name='. $act_title. '&act_id='.$in_class->activity_id,
                                                                'icon' => 'fa fa-won-sign',
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
                                                                'icon' => 'fa fa-list',
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
                                                                'icon' => 'fa fa-columns',
                                                                'level' => 'secondary',
                                                                'show' => !is_module_disable_FC(MODULE_ID_BLOG,$course_code,$id,$in_class->activity_id)),

                                                            array('title' => trans('langAdd').' '.trans('langComments'),
                                                                'url' => $base_url . 'comments&fc_type=1&act_name='. $act_title. '&act_id='.$in_class->activity_id,
                                                                'icon' => 'fa fa-comments',
                                                                'level' => 'secondary',
                                                                'show' => !is_module_disable_FC(MODULE_ID_COMMENTS,$course_code,$id,$in_class->activity_id)),

                                                            array('title' => trans('langAdd').' '.trans('langGradebook'),
                                                                'url' => $base_url . 'gradebook&fc_type=1&act_name='. $act_title. '&act_id='.$in_class->activity_id,
                                                                'icon' => 'fa fa-sort-numeric-desc',
                                                                'level' => 'secondary',
                                                                'show' => !is_module_disable_FC(MODULE_ID_GRADEBOOK,$course_code,$id,$in_class->activity_id)),

                                                            array('title' => trans('langAdd').' '.trans('langProgress'),
                                                                'url' => $base_url . 'progress&fc_type=1&act_name='. $act_title. '&act_id='.$in_class->activity_id,
                                                                'icon' => 'fa fa-trophy',
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


                                                <td><span class='col-sm-12 {!! $class_vis !!} text-secondary fs-6'>{!! $act_title !!}</span></td>
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
                                                        'icon' => 'fa fa-list',
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
                                                        'icon' => 'fa fa-columns',
                                                        'level' => 'secondary',
                                                        'show' => !is_module_disable_FC(MODULE_ID_BLOG,$course_code,$id,$after_class->activity_id)),

                                                    array('title' => trans('langAdd').' '.trans('langComments'),
                                                        'url' => $base_url . 'comments&fc_type=2&act_name='. $act_title. '&act_id='.$after_class->activity_id,
                                                        'icon' => 'fa fa-comments',
                                                        'level' => 'secondary',
                                                        'show' => !is_module_disable_FC(MODULE_ID_COMMENTS,$course_code,$id,$after_class->activity_id)),

                                                    array('title' => trans('langAdd').' '.trans('langGradebook'),
                                                        'url' => $base_url . 'gradebook&fc_type=2&act_name='. $act_title. '&act_id='.$after_class->activity_id,
                                                        'icon' => 'fa fa-sort-numeric-desc',
                                                        'level' => 'secondary',
                                                        'show' => !is_module_disable_FC(MODULE_ID_GRADEBOOK,$course_code,$id,$after_class->activity_id)),

                                                    array('title' => trans('langAdd').' '.trans('langProgress'),
                                                        'url' => $base_url . 'progress&fc_type=2&act_name='. $act_title. '&act_id='.$after_class->activity_id,
                                                        'icon' => 'fa fa-trophy',
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



                    {{--
                    <div class='col-12 mt-4'>
                        <div class='panel panel-default'>
                            <div class='panel-heading bgTheme'>{{ trans('langCourseUnits') }}</div>
                            <div class='panel-body'>
                                <form class='form-horizontal' name='unitselect' action='{{ $urlAppend }}modules/units/index.php' method='get'>
                                    <input type='hidden' name='course' value='{{ $course_code }}'>
                                    <div class='form-group'>
                                        <div class='col-sm-12'>
                                            <label class='sr-only' for='id'>{{ trans('langCourseUnits') }}</label>
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
                    </div>
                    --}}


                </div>
            </div>
        </div>
    
</div>

@endsection

