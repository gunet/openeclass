@extends('layouts.default')

@section('content')

<div class="col-12 main-section">
    <div class='{{ $container }} module-container py-lg-0'>
        <div class="course-wrapper d-lg-flex align-items-lg-strech w-100">

            @include('layouts.partials.left_menu')

            <div class="col_maincontent_active">

                <div class="row">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    <div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="collapseTools">
                        <div class="offcanvas-header">
                            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="{{ trans('langClose') }}"></button>
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
                                    array('title' => trans('langAdd') . ' ' . trans('langInsertDivider'),
                                        'url' => $insertBaseUrl . 'divider',
                                        'icon' => 'fa fa-grip-lines',
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

                    @include('layouts.partials.show_alert')

                    @if(count($units) > 0)
                        <div class='col-12'>
                            <div class="card panelCard card-units px-lg-4 py-lg-3 p-3">
                                <div class='card-body p-0'>
                                    <ul class="tree-units">
                                        <li>
                                            <details>
                                                <summary><h3 class='mb-0'>{{ trans('langUnits')}}</h3></summary>
                                                <ul>
                                                    @foreach ($units as $cu)
                                                        <li {{ $cu->id == $id ? "class=active-unit" : "" }}>
                                                            <a class='TextBold{{ $cu->id != $id ? "" : " Success-200-cl" }}' href='{{ $urlServer }}modules/units/index.php?course={{ $course_code }}&amp;id={{ $cu->id }}'>
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
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </details>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    @endif


                    <div class='col-12 mt-4'>
                        <div class="card panelCard card-default px-lg-4 py-lg-3">
                            <div class='card-header border-0 d-flex justify-content-between align-items-center gap-3 flex-wrap'>
                                <h3>{{ $pageName }}</h3>
                                @if($course_start_week or $course_finish_week)
                                    <div>
                                        <small>{{ $course_start_week }}&nbsp;{{ $course_finish_week }}</small>
                                    </div>
                                @endif
                            </div>
                            <div class="card-body">

                                @if ($previousLink or $nextLink)
                                    <div class='col-12 d-flex justify-content-between align-items-center gap-3 flex-wrap pb-4 mb-4 border-bottom-default'>
                                        @if ($previousLink)
                                            <a class='TextBold' title='{{ $previousTitle }}' href='{{ $previousLink}}'>
                                                <i class='fa fa-arrow-left space-after-icon'></i>
                                                {{ ellipsize($previousTitle, 30) }}
                                            </a>
                                        @endif
                                        @if ($nextLink)
                                            <a class='TextBold ms-auto' title='{{ $nextTitle }}' href='{{ $nextLink}}'>
                                                {{ ellipsize($nextTitle, 30) }}
                                                <i class='fa fa-arrow-right space-before-icon'></i>
                                            </a>
                                        @endif
                                    </div>
                                @endif



                                <div style="display: flow-root;" class="{{ $comments ? 'border-bottom-default pb-4' : '' }}">
                                    {!! $comments !!}
                                </div>
                                <div class='unit-resources mt-3'>
                                    {!! $tool_content_units !!}
                                </div>
                            </div>
                            @if ($tags_list)
                                <div class='card-footer border-0'>
                                    <p class='TextBold'>{{ trans('langTags') }}: {!! $tags_list !!}</p>
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
                            <div class='card panelCard card-default px-lg-4 py-lg-3'>
                                <div class='card-header border-0 d-flex justify-content-between align-items-center'>
                                    <h3>
                                        <div class='d-flex justify-content-start align-items-center gap-2 flex-wrap'>
                                            {{ trans('langActivities')}}
                                            <a aria-label="{{ trans('langEdit') }}" href="{{ $urlAppend }}modules/create_course/course_units_activities.php?course={{ $course_code }}&edit_act={{ $id }}"
                                                data-bs-toggle="tooltip" data-bs-placement="bottom" title data-bs-original-title="{{ trans('langEdit') }}">
                                                <i class="fa-solid fa-pencil fa-lg"></i>
                                            </a>
                                        </div>
                                    </h3>
                                </div>
                                <div class='card-body'>
                                    <table class='table table-default'>
                                        <tbody>
                                        @if ($q_in_home)
                                            <tr><th scope='row' colspan="2"><div class='col-12 control-label-notes'>{{ trans('langActInHome') }}</div></th>
                                            @foreach ($q_in_home as $in_home)
                                                @php
                                                    $act_title = q($activities[$in_home->activity_id]['title']);
                                                    $vis = $in_home->visible;
                                                    $class_vis = $vis == 0 ? 'not_visible not_visible_unit' : '';
                                                    $act_indirect = $in_home->ID;
                                                @endphp

                                                <tr><td><span class='col-10 {{$class_vis}} '>{!! $act_title !!}</span></td>
                                                <td class='col-2 text-end'>
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
                                                            'show' => !is_module_disable_FC(MODULE_ID_PROGRESS, $course_code, $id, $in_home->activity_id)),

                                                        array('title' => trans('langAdd') . ' ' . trans('langOfH5p'),
                                                           'url' => $base_url . 'h5p&fc_type=0&act_name=' . $act_title . '&act_id=' . $in_home->activity_id,
                                                           'icon' => 'fa fa-tablet',
                                                           'level' => 'secondary',
                                                           'show' => !is_module_disable_FC(MODULE_ID_H5P, $course_code, $id, $in_home->activity_id))

                                                        ),'',true)
                                                    !!}
                                                    <span class='col-sm-6 text-end'>
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
                                                </span></td></tr>
                                            @endforeach
                                        @endif

                                        @if($q_in_class)
                                            <tr><th scope='row' colspan='2'><div class='col-12 control-label-notes'>{{ trans('langActInClass') }}</div></th>

                                            @foreach($q_in_class as $in_class)

                                                @php
                                                    $act_title = q($activities[$in_class->activity_id]['title']);
                                                    $vis = $in_class->visible;
                                                    $class_vis = $vis == 0  ? 'not_visible not_visible_unit' : '';
                                                    $act_indirect = $in_class->activity_id;
                                                @endphp

                                                <tr><td><span class='col-10  {!! $class_vis !!} control-label'>{!! $act_title !!}</span></td>
                                                @if($is_editor)
                                                    <td class='col-6 text-end'>
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
                                                                'show' => !is_module_disable_FC(MODULE_ID_PROGRESS,$course_code,$id,$in_class->activity_id)),

                                                            array('title' => trans('langAdd') . ' ' . trans('langOfH5p'),
                                                               'url' => $base_url . 'h5p&fc_type=0&act_name=' . $act_title . '&act_id=' . $in_class->activity_id,
                                                               'icon' => 'fa fa-tablet',
                                                               'level' => 'secondary',
                                                               'show' => !is_module_disable_FC(MODULE_ID_H5P, $course_code, $id, $in_class->activity_id))

                                                        ),'',true) !!}
                                                    <span class='col-sm-6'>

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
                                                </span></td></tr>
                                            @endforeach
                                        @endif

                                        @if($q_after_class)
                                            <tr><th scope='row' colspan='2'><label class='col-md-auto control-label'>{{ trans('langActAfterClass') }}</label></th>

                                            @foreach($q_after_class as $after_class)

                                                @php
                                                    $act_title = q($activities[$after_class->activity_id]['title']);
                                                    $vis = $after_class->visible;
                                                    $class_vis = $vis == 0  ? 'not_visible not_visible_unit' : '';
                                                    $act_indirect = $after_class->ID;
                                                @endphp


                                                <tr><td class='col-10 {!! $class_vis !!} control-label'>{!! $act_title !!}</td>
                                                <td class='col-6 text-end'> {!! action_button(array(
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
                                                        'show' => !is_module_disable_FC(MODULE_ID_PROGRESS,$course_code,$id,$after_class->activity_id)),

                                                    array('title' => trans('langAdd') . ' ' . trans('langOfH5p'),
                                                           'url' => $base_url . 'h5p&fc_type=0&act_name=' . $act_title . '&act_id=' . $after_class->activity_id,
                                                           'icon' => 'fa fa-tablet',
                                                           'level' => 'secondary',
                                                           'show' => !is_module_disable_FC(MODULE_ID_H5P, $course_code, $id, $after_class->activity_id))

                                                ),'',true) !!}

                                                <span class='col-sm-6 text-end'>

                                                {!! action_button(array(
                                                    array('title' => $vis == 1? trans('langViewHide') : trans('langViewShow'),
                                                    'url' => "$_SERVER[REQUEST_URI]&vis_act=$after_class->ID",
                                                    'icon' => $vis == 1? 'fa-eye-slash' : 'fa-eye'),

                                                    array('title' => trans('langDelete'),
                                                        'url' => "$_SERVER[REQUEST_URI]&del_act=$after_class->ID&actid=".$after_class->activity_id,
                                                        'icon' => 'fa-xmark',
                                                        'class' => 'delete',
                                                        'confirm' => trans('langUnitActivityDeleteConfirm')))) !!}

                                                </span></td></tr>
                                            @endforeach
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
