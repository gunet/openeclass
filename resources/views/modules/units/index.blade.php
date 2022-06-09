@extends('layouts.default')

@section('content')
    @if ($is_editor)
        <div class='row'>
            <div class='col-md-12'>
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
            </div>
        </div>
    @endif

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
    @endif

    <div class='row'>
        <div class='col-md-12'>
            <div class='panel panel-default'>
                <div class='panel-heading'>
                    <div class='panel-title h3'>{{ $pageName }}
                        <h6 class='text-muted'>
                            {{ $course_start_week }}
                            {{ $course_finish_week }}
                        </h6>
                    </div>
                </div>
                <div class='panel-body'>
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
    </div>


    <!-- flipped classroom activity buttons -->

    @if ($q->flipped_flag == 2)
        <div class='row'>
            <div class='col-md-12'>
                <div class='panel panel-default'>
                    <div class='panel-heading'>
                        <div class='paenel-title h4'>
                            {{ trans('langActivities') }}
                            @if ($is_editor)
                                &nbsp;&nbsp; {!! icon('fa-pencil', $langEdit, $urlAppend."modules/create_course/course_units_activities.php?course=$course_code&amp;edit_act=$id") !!}
                            @endif
                        </div>
                    </div>
                    <div class='panel-body'>
                        <table class='table table-bordered'>
                            <tbody>
            @if ($q_in_home)
                    </tr><tr><th scope='row'><label class='col-2 control-label'>$langActInHome</label></th>
            @endif

            @foreach ($q_in_home as $in_home)
                {!!
                    $act_title = q($activities[$in_home->activity_id]['title']);
                    $vis = $in_home->visible;
                    $class_vis = $vis == 0  ? 'not_visible' : '';
                    $act_indirect = getIndirectReference($in_home->ID);
                 !!}
                <td><span class='col-sm-20 {!! $class_vis !!} control-label'>{!! $act_title !!}</span></td>
                @if ($is_editor)
                    <td>
                        {!! action_button(array(
                            array('title' => trans('langAdd') . ' ' . trans('langInsertExercise') ,
                            'url' => $base_url . 'exercise&fc_type=0&act_name='. $act_title. '&act_id='.$in_home->activity_id,
                            'icon' => 'fa fa-pencil-square-o',
                            'level' => 'secondary',
                            'show' => !is_module_disable_FC(MODULE_ID_EXERCISE,$course_code,$id,$in_home->activity_id)),
                            array('title' => trans('langAdd') . ' ' . trans('langInsertDoc'),
                            'url' => $base_url . 'doc&fc_type=0&act_name='. $act_title. '&act_id='.$in_home->activity_id,
                            'icon' => 'fa fa-folder-open-o',
                            'level' => 'secondary',
                            'show' => !is_module_disable_FC(MODULE_ID_DOCS,$course_code,$id,$in_home->activity_id)),
                            array('title' => trans('langAdd') . ' ' . trans('langInsertLink'),
                            'url' => $base_url . 'link&fc_type=0&act_name='. $act_title. '&act_id='.$in_home->activity_id,
                            'icon' => 'fa fa-link',
                            'level' => 'secondary',
                            'show' => !is_module_disable_FC(MODULE_ID_LINKS,$course_code,$id,$in_home->activity_id)),
                            array('title' => trans('langAdd') . ' ' . trans('langLearningPath1'),
                            'url' => $base_url . 'lp&fc_type=0&act_name='. $act_title. '&act_id='.$in_home->activity_id,
                            'icon' => 'fa fa-ellipsis-h',
                            'level' => 'secondary',
                            'show' => !is_module_disable_FC(MODULE_ID_LP,$course_code,$id,$in_home->activity_id)),
                            array('title' => trans('langAdd') . ' ' . trans('langInsertVideo'),
                            'url' => $base_url . 'video&fc_type=0&act_name='. $act_title. '&act_id='.$in_home->activity_id,
                            'icon' => 'fa fa-film',
                            'level' => 'secondary',
                            'show' => !is_module_disable_FC(MODULE_ID_VIDEO,$course_code,$id,$in_home->activity_id)),
                            array('title' => trans('langAdd') . ' ' . trans('langInsertForum'),
                            'url' => $base_url . 'forum&fc_type=0&act_name='. $act_title. '&act_id='.$in_home->activity_id,
                            'icon' => 'fa fa-comments',
                            'level' => 'secondary',
                            'show' => !is_module_disable_FC(MODULE_ID_FORUM,$course_code,$id,$in_home->activity_id)),
                            array('title' => trans('langAdd') . ' ' . trans('langInsertEBook'),
                            'url' => $base_url . 'ebook&fc_type=0&act_name='. $act_title. '&act_id='.$in_home->activity_id,
                            'icon' => 'fa fa-book',
                            'level' => 'secondary',
                            'show' =>  !is_module_disable_FC(MODULE_ID_EBOOK,$course_code,$id,$in_home->activity_id)),
                            array('title' => trans('langAdd') . ' ' . trans('langInsertWork'),
                            'url' => $base_url . 'work&fc_type=0&act_name='. $act_title. '&act_id='.$in_home->activity_id,
                            'icon' => 'fa fa-flask',
                            'level' => 'secondary',
                            'show' => !is_module_disable_FC(MODULE_ID_ASSIGN,$course_code,$id,$in_home->activity_id)),
                            array('title' => trans('langAdd') . ' ' . trans('langInsertPoll'),
                            'url' => $base_url . 'poll&fc_type=0&act_name='. $act_title. '&act_id='.$in_home->activity_id,
                            'icon' => 'fa fa-question-circle',
                            'level' => 'secondary',
                            'show' => !is_module_disable_FC(MODULE_ID_QUESTIONNAIRE,$course_code,$id,$in_home->activity_id)),
                            array('title' => trans('langAdd') . ' ' . trans('langInsertWiki'),
                            'url' => $base_url . 'wiki&fc_type=0&act_name='. $act_title. '&act_id='.$in_home->activity_id,
                            'icon' => 'fa fa-wikipedia-w',
                            'level' => 'secondary',
                            'show' => !is_module_disable_FC(MODULE_ID_WIKI,$course_code,$id,$in_home->activity_id)),
                            array('title' => trans('langAdd') . ' ' . trans('langInsertChat'),
                            'url' => $base_url . 'chat&fc_type=0&act_name='. $act_title. '&act_id='.$in_home->activity_id,
                            'icon' => 'fa fa-exchange',
                            'level' => 'secondary',
                            'show' => !is_module_disable_FC(MODULE_ID_CHAT,$course_code,$id,$in_home->activity_id)),
                            array('title' => $langAdd.' '.$langInsertTcMeeting,
                            'url' => $base_url . 'tc&fc_type=0&act_name='. $act_title. '&act_id='.$in_home->activity_id,
                            'icon' => 'fa fa-exchange',
                            'level' => 'secondary',
                            'show' => !is_module_disable_FC(MODULE_ID_TC,$course_code,$id,$in_home->activity_id) && is_configured_tc_server()),
                            array('title' => trans('langAdd') . ' ' . trans('langInsertGlossary'),
                            'url' => $base_url . 'glossary&fc_type=0&act_name='. $act_title. '&act_id='.$in_home->activity_id,
                            'icon' => 'fa fa-list',
                            'level' => 'secondary',
                            'show' => !is_module_disable_FC(MODULE_ID_GLOSSARY,$course_code,$id,$in_home->activity_id)),
                            array('title' => trans('langAdd') . ' ' . trans('langInsertWall'),
                            'url' => $base_url . 'wall&fc_type=0&act_name='. $act_title. '&act_id='.$in_home->activity_id,
                            'icon' => 'fa fa-list',
                            'level' => 'secondary',
                            'show' => !is_module_disable_FC(MODULE_ID_WALL,$course_code,$id,$in_home->activity_id)),
                            array('title' => trans('langAdd') . ' ' . trans('langInsertMindmap'),
                            'url' => $base_url . 'mindmap&fc_type=0&act_name='. $act_title. '&act_id='.$in_home->activity_id,
                            'icon' => 'fa fa-sitemap',
                            'level' => 'secondary',
                            'show' => !is_module_disable_FC(MODULE_ID_MINDMAP,$course_code,$id,$in_home->activity_id)),
                            array('title' => trans('langAdd') . ' ' . trans('langInsertBlog'),
                            'url' => $base_url . 'blog&fc_type=0&act_name='. $act_title. '&act_id='.$in_home->activity_id,
                            'icon' => 'fa fa-columns',
                            'level' => 'secondary',
                            'show' => !is_module_disable_FC(MODULE_ID_BLOG,$course_code,$id,$in_home->activity_id)),
                            array('title' => trans('langAdd') . ' ' . trans('langInsertComments'),
                            'url' => $base_url . 'comments&fc_type=0&act_name='. $act_title. '&act_id='.$in_home->activity_id,
                            'icon' => 'fa fa-comments',
                            'level' => 'secondary',
                            'show' => !is_module_disable_FC(MODULE_ID_COMMENTS,$course_code,$id,$in_home->activity_id)),
                            array('title' => trans('langAdd') . ' ' . trans('langInsertGradebook'),
                            'url' => $base_url . 'gradebook&fc_type=0&act_name='. $act_title. '&act_id='.$in_home->activity_id,
                            'icon' => 'fa fa-sort-numeric-desc',
                            'level' => 'secondary',
                            'show' => !is_module_disable_FC(MODULE_ID_GRADEBOOK,$course_code,$id,$in_home->activity_id)),
                            array('title' => trans('langAdd') . ' ' . trans('langInsertProgress'),
                            'url' => $base_url . 'progress&fc_type=0&act_name='. $act_title. '&act_id='.$in_home->activity_id,
                            'icon' => 'fa fa-trophy',
                            'level' => 'secondary',
                            'show' => !is_module_disable_FC(MODULE_ID_PROGRESS,$course_code,$id,$in_home->activity_id))

                            ),'',true);
                        !!}
                    </td><td>

                    {!! action_button(array(
                            array('title' => $vis == 1? trans('langViewHide') : trans('langViewShow'),
                            'url' => "$_SERVER[REQUEST_URI]&vis_act=$in_home->ID",
                            'icon' => $vis == 1? 'fa-eye-slash' : 'fa-eye'),
                            array('title' => trans('langDelete'),
                            'url' => "$_SERVER[REQUEST_URI]&del_act=$in_home->ID&actid=".$in_home->activity_id,
                            'icon' => 'fa-times',
                            'class' => 'delete',
                            'confirm' => trans('langUnitActivityDeleteConfirm')))) ;
                    !!}
                @endif
                </td>
            </tr><tr>
            <td></td>
        @endforeach

        @if ($q_in_class)
            <tr><th scope='row'><label class='col-2 control-label'>{{ trans('langActInClass') }}</label></th>
        @endif
            @foreach ($q_in_class as $in_class)
                {!!
                    $act_title = q($activities[$in_class->activity_id]['title']);

                    $vis = $in_class->visible;
                    $class_vis = $vis == 0  ? 'not_visible' : '';
                    $act_indirect = getIndirectReference($in_class->activity_id);
                !!}
                <td><span class='col-sm-20  {!! $class_vis !!} control-label'>{!! $act_title !!}</span></td>
                @if ($is_editor)
                    <td>
                        {!!
                        action_button(array(
                            array('title' => trans('langAdd') . ' ' . trans('langInsertExercise') ,
                            'url' => $base_url . 'exercise&fc_type=1&act_name='. $act_title. '&act_id='.$in_class->activity_id,
                            'icon' => 'fa fa-pencil-square-o',
                            'level' => 'secondary',
                            'show' => !is_module_disable_FC(MODULE_ID_EXERCISE,$course_code,$id,$in_class->activity_id)),
                            array('title' => trans('langAdd') . ' ' . trans('langInsertDoc') ,
                            'url' => $base_url . 'doc&fc_type=1&act_name='. $act_title. '&act_id='.$in_class->activity_id,
                            'icon' => 'fa fa-folder-open-o',
                            'level' => 'secondary',
                            'show' => !is_module_disable_FC(MODULE_ID_DOCS,$course_code,$id,$in_class->activity_id)),
                            array('title' => trans('langAdd') . ' ' . trans('langInsertLink') ,
                            'url' => $base_url . 'link&fc_type=1&act_name='. $act_title. '&act_id='.$in_class->activity_id,
                            'icon' => 'fa fa-link',
                            'level' => 'secondary',
                            'show' => !is_module_disable_FC(MODULE_ID_LINKS,$course_code,$id,$in_class->activity_id)),
                            array('title' => trans('langAdd') . ' ' . trans('langInsertLearningPath1') ,
                            'url' => $base_url . 'lp&fc_type=1&act_name='. $act_title. '&act_id='.$in_class->activity_id,
                            'icon' => 'fa fa-ellipsis-h',
                            'level' => 'secondary',
                            'show' => !is_module_disable_FC(MODULE_ID_LP,$course_code,$id,$in_class->activity_id)),
                            array('title' => trans('langAdd') . ' ' . trans('langInsertVideo') ,
                            'url' => $base_url . 'video&fc_type=1&act_name='. $act_title. '&act_id='.$in_class->activity_id,
                            'icon' => 'fa fa-film',
                            'level' => 'secondary',
                            'show' => !is_module_disable_FC(MODULE_ID_VIDEO,$course_code,$id,$in_class->activity_id)),
                            array('title' => trans('langAdd') . ' ' . trans('langInsertForum') ,
                            'url' => $base_url . 'forum&fc_type=1&act_name='. $act_title. '&act_id='.$in_class->activity_id,
                            'icon' => 'fa fa-comments',
                            'level' => 'secondary',
                            'show' => !is_module_disable_FC(MODULE_ID_FORUM,$course_code,$id,$in_class->activity_id)),
                            array('title' => trans('langAdd') . ' ' . trans('langInsertEBook') ,
                            'url' => $base_url . 'ebook&fc_type=1&act_name='. $act_title. '&act_id='.$in_class->activity_id,
                            'icon' => 'fa fa-book',
                            'level' => 'secondary',
                            'show' =>  !is_module_disable_FC(MODULE_ID_EBOOK,$course_code,$id,$in_class->activity_id)),
                            array('title' => trans('langAdd') . ' ' . trans('langInsertWork') ,
                            'url' => $base_url . 'work&fc_type=1&act_name='. $act_title. '&act_id='.$in_class->activity_id,
                            'icon' => 'fa fa-flask',
                            'level' => 'secondary',
                            'show' => !is_module_disable_FC(MODULE_ID_ASSIGN,$course_code,$id,$in_class->activity_id)),
                            array('title' => trans('langAdd') . ' ' . trans('langInsertPoll') ,
                            'url' => $base_url . 'poll&fc_type=1&act_name='. $act_title. '&act_id='.$in_class->activity_id,
                            'icon' => 'fa fa-question-circle',
                            'level' => 'secondary',
                            'show' => !is_module_disable_FC(MODULE_ID_QUESTIONNAIRE,$course_code,$id,$in_class->activity_id)),
                            array('title' => trans('langAdd') . ' ' . trans('langInsertWiki') ,
                            'url' => $base_url . 'wiki&fc_type=1&act_name='. $act_title. '&act_id='.$in_class->activity_id,
                            'icon' => 'fa fa-wikipedia-w',
                            'level' => 'secondary',
                            'show' => !is_module_disable_FC(MODULE_ID_WIKI,$course_code,$id,$in_class->activity_id)),
                            array('title' => trans('langAdd') . ' ' . trans('langInsertChat') ,
                            'url' => $base_url . 'chat&fc_type=1&act_name='. $act_title. '&act_id='.$in_class->activity_id,
                            'icon' => 'fa fa-exchange',
                            'level' => 'secondary',
                            'show' => !is_module_disable_FC(MODULE_ID_CHAT,$course_code,$id,$in_class->activity_id)),
                            array('title' => trans('langAdd') . ' ' . trans('langInsertTcMeeting') ,
                            'url' => $base_url . 'tc&fc_type=1&act_name='. $act_title. '&act_id='.$in_class->activity_id,
                            'icon' => 'fa fa-exchange',
                            'level' => 'secondary',
                            'show' => !is_module_disable_FC(MODULE_ID_TC,$course_code,$id,$in_class->activity_id) && is_configured_tc_server()),
                            array('title' => trans('langAdd') . ' ' . trans('langInsertGlossary') ,
                            'url' => $base_url . 'glossary&fc_type=1&act_name='. $act_title. '&act_id='.$in_class->activity_id,
                            'icon' => 'fa fa-list',
                            'level' => 'secondary',
                            'show' => !is_module_disable_FC(MODULE_ID_GLOSSARY,$course_code,$id,$in_class->activity_id)),
                            array('title' => trans('langAdd') . ' ' . trans('langInsertWall') ,
                            'url' => $base_url . 'wall&fc_type=1&act_name='. $act_title. '&act_id='.$in_class->activity_id,
                            'icon' => 'fa fa-list',
                            'level' => 'secondary',
                            'show' => !is_module_disable_FC(MODULE_ID_WALL,$course_code,$id,$in_class->activity_id)),
                            array('title' => trans('langAdd') . ' ' . trans('langInsertMindmap') ,
                            'url' => $base_url . 'mindmap&fc_type=1&act_name='. $act_title. '&act_id='.$in_class->activity_id,
                            'icon' => 'fa fa-sitemap',
                            'level' => 'secondary',
                            'show' => !is_module_disable_FC(MODULE_ID_MINDMAP,$course_code,$id,$in_class->activity_id)),
                            array('title' => trans('langAdd') . ' ' . trans('langInsertGroups') ,
                            'url' => $base_url . 'group&fc_type=1&act_name='. $act_title. '&act_id='.$in_class->activity_id,
                            'icon' => 'fa fa-users',
                            'level' => 'secondary',
                            'show' => !is_module_disable_FC(MODULE_ID_GROUPS,$course_code,$id,$in_class->activity_id)),
                            array('title' => trans('langAdd') . ' ' . trans('langInsertBlog') ,
                            'url' => $base_url . 'blog&fc_type=1&act_name='. $act_title. '&act_id='.$in_class->activity_id,
                            'icon' => 'fa fa-columns',
                            'level' => 'secondary',
                            'show' => !is_module_disable_FC(MODULE_ID_BLOG,$course_code,$id,$in_class->activity_id)),
                            array('title' => trans('langAdd') . ' ' . trans('langInsertComments') ,
                            'url' => $base_url . 'comments&fc_type=1&act_name='. $act_title. '&act_id='.$in_class->activity_id,
                            'icon' => 'fa fa-comments',
                            'level' => 'secondary',
                            'show' => !is_module_disable_FC(MODULE_ID_COMMENTS,$course_code,$id,$in_class->activity_id)),
                            array('title' => trans('langAdd') . ' ' . trans('langInsertGradebook') ,
                            'url' => $base_url . 'gradebook&fc_type=1&act_name='. $act_title. '&act_id='.$in_class->activity_id,
                            'icon' => 'fa fa-sort-numeric-desc',
                            'level' => 'secondary',
                            'show' => !is_module_disable_FC(MODULE_ID_GRADEBOOK,$course_code,$id,$in_class->activity_id)),
                            array('title' => trans('langAdd') . ' ' . trans('langInsertProgress') ,
                            'url' => $base_url . 'progress&fc_type=1&act_name='. $act_title. '&act_id='.$in_class->activity_id,
                            'icon' => 'fa fa-trophy',
                            'level' => 'secondary',
                            'show' => !is_module_disable_FC(MODULE_ID_PROGRESS,$course_code,$id,$in_class->activity_id))

                            ),'',
                        true)
                        !!}

                    </td><td>
                    {!!
                        action_button(array(
                            array('title' => $vis == 1? trans('langViewHide') : trans('langViewShow'),
                                 'url' => "$_SERVER[REQUEST_URI]&vis_act=$in_class->ID",
                                'icon' => $vis == 1? 'fa-eye-slash' : 'fa-eye'),
                            array('title' => trans('langDelete'),
                                'url' => "$_SERVER[REQUEST_URI]&del_act=$in_class->ID&actid=".$in_class->activity_id,
                                'icon' => 'fa-times',
                                'class' => 'delete',
                                'confirm' => trans('langUnitActivityDeleteConfirm')))) ;
                     !!}
                @endif
                    </td>
                </tr><tr>
                <td></td>
            @endforeach
        @if ($q_after_class)
           <tr><th scope='row'><label class='col-md-auto control-label'>{{ trans('langActAfterClass') }}</label></th>
           @foreach ($q_after_class as $after_class)
               {!!
                   $act_title = q($activities[$after_class->activity_id]['title']);

                   $vis = $after_class->visible;
                   $class_vis = $vis == 0  ? 'not_visible' : '';
                   $act_indirect = getIndirectReference($after_class->ID);
               !!}
               <td><span class='col-sm-20 {!! $class_vis !!} control-label'>{!! $act_title !!}</span></td>
               @if ($is_editor)
                   <td>
                       {!! action_button(array(
                           array('title' => trans('langAdd') . ' ' . trans('langInsertExercise') ,
                           'url' => $base_url . 'exercise&fc_type=2&act_name='. $act_title,
                           'icon' => 'fa fa-pencil-square-o',
                           'level' => 'secondary',
                           'show' => !is_module_disable_FC(MODULE_ID_EXERCISE,$course_code,$id,$after_class->activity_id)),
                           array('title' => trans('langAdd') . ' ' . trans('langInsertDoc') ,
                           'url' => $base_url . 'doc&fc_type=2&act_name='. $act_title. '&act_id='.$after_class->activity_id,
                           'icon' => 'fa fa-folder-open-o',
                           'level' => 'secondary',
                           'show' => !is_module_disable_FC(MODULE_ID_DOCS,$course_code,$id,$after_class->activity_id)),
                           array('title' => trans('langAdd') . ' ' . trans('langInsertLink') ,
                           'url' => $base_url . 'link&fc_type=2&act_name='. $act_title. '&act_id='.$after_class->activity_id,
                           'icon' => 'fa fa-link',
                           'level' => 'secondary',
                           'show' => !is_module_disable_FC(MODULE_ID_LINKS,$course_code,$id,$after_class->activity_id)),
                           array('title' => trans('langAdd') . ' ' . trans('langInsertLearningPath1') ,
                           'url' => $base_url . 'lp&fc_type=2&act_name='. $act_title. '&act_id='.$after_class->activity_id,
                           'icon' => 'fa fa-ellipsis-h',
                           'level' => 'secondary',
                           'show' => !is_module_disable_FC(MODULE_ID_LP,$course_code,$id,$after_class->activity_id)),
                           array('title' => trans('langAdd') . ' ' . trans('langInsertVideo') ,
                           'url' => $base_url . 'video&fc_type=2&act_name='. $act_title. '&act_id='.$after_class->activity_id,
                           'icon' => 'fa fa-film',
                           'level' => 'secondary',
                           'show' => !is_module_disable_FC(MODULE_ID_VIDEO,$course_code,$id,$after_class->activity_id)),
                           array('title' => trans('langAdd') . ' ' . trans('langInsertForum') ,
                           'url' => $base_url . 'forum&fc_type=2&act_name='. $act_title. '&act_id='.$after_class->activity_id,
                           'icon' => 'fa fa-comments',
                           'level' => 'secondary',
                           'show' => !is_module_disable_FC(MODULE_ID_FORUM,$course_code,$id,$after_class->activity_id)),
                           array('title' => trans('langAdd') . ' ' . trans('langInsertEBook') ,
                           'url' => $base_url . 'ebook&fc_type=2&act_name='. $act_title. '&act_id='.$after_class->activity_id,
                           'icon' => 'fa fa-book',
                           'level' => 'secondary',
                           'show' =>  !is_module_disable_FC(MODULE_ID_EBOOK,$course_code,$id,$after_class->activity_id)),
                           array('title' => trans('langAdd') . ' ' . trans('langInsertWork') ,
                           'url' => $base_url . 'work&fc_type=2&act_name='. $act_title. '&act_id='.$after_class->activity_id,
                           'icon' => 'fa fa-flask',
                           'level' => 'secondary',
                           'show' => !is_module_disable_FC(MODULE_ID_ASSIGN,$course_code,$id,$after_class->activity_id)),
                           array('title' => trans('langAdd') . ' ' . trans('langInsertPoll') ,
                           'url' => $base_url . 'poll&fc_type=2&act_name='. $act_title. '&act_id='.$after_class->activity_id,
                           'icon' => 'fa fa-question-circle',
                           'level' => 'secondary',
                           'show' => !is_module_disable_FC(MODULE_ID_QUESTIONNAIRE,$course_code,$id,$after_class->activity_id)),
                           array('title' => trans('langAdd') . ' ' . trans('langInsertWiki') ,
                           'url' => $base_url . 'wiki&fc_type=2&act_name='. $act_title. '&act_id='.$after_class->activity_id,
                           'icon' => 'fa fa-wikipedia-w',
                           'level' => 'secondary',
                           'show' => !is_module_disable_FC(MODULE_ID_WIKI,$course_code,$id,$after_class->activity_id)),
                           array('title' => trans('langAdd') . ' ' . trans('langInsertChat') ,
                           'url' => $base_url . 'chat&fc_type=2&act_name='. $act_title. '&act_id='.$after_class->activity_id,
                           'icon' => 'fa fa-exchange',
                           'level' => 'secondary',
                           'show' => !is_module_disable_FC(MODULE_ID_CHAT,$course_code,$id,$after_class->activity_id)),
                           array('title' => trans('langAdd') . ' ' . trans('langInsertTcMeeting') ,
                           'url' => $base_url . 'tc&fc_type=2&act_name='. $act_title. '&act_id='.$after_class->activity_id,
                           'icon' => 'fa fa-exchange',
                           'level' => 'secondary',
                           'show' => !is_module_disable_FC(MODULE_ID_TC,$course_code,$id,$after_class->activity_id) && is_configured_tc_server()),
                           array('title' => trans('langAdd') . ' ' . trans('langInsertGlossary') ,
                           'url' => $base_url . 'glossary&fc_type=2&act_name='. $act_title. '&act_id='.$after_class->activity_id,
                           'icon' => 'fa fa-list',
                           'level' => 'secondary',
                           'show' => !is_module_disable_FC(MODULE_ID_GLOSSARY,$course_code,$id,$after_class->activity_id)),
                           array('title' => trans('langAdd') . ' ' . trans('langInsertWall') ,
                           'url' => $base_url . 'wall&fc_type=2&act_name='. $act_title. '&act_id='.$after_class->activity_id,
                           'icon' => 'fa fa-list',
                           'level' => 'secondary',
                           'show' => !is_module_disable_FC(MODULE_ID_WALL,$course_code,$id,$after_class->activity_id)),
                           array('title' => trans('langAdd') . ' ' . trans('langInsertMindmap') ,
                           'url' => $base_url . 'mindmap&fc_type=2&act_name='. $act_title. '&act_id='.$after_class->activity_id,
                           'icon' => 'fa fa-sitemap',
                           'level' => 'secondary',
                           'show' => !is_module_disable_FC(MODULE_ID_MINDMAP,$course_code,$id,$after_class->activity_id)),
                           array('title' => trans('langAdd') . ' ' . trans('langInsertGroups') ,
                           'url' => $base_url . 'group&fc_type=2&act_name=='. $act_title. '&act_id='.$after_class->activity_id,
                           'icon' => 'fa fa-users',
                           'level' => 'secondary',
                           'show' => !is_module_disable_FC(MODULE_ID_GROUPS,$course_code,$id,$after_class->activity_id)),
                           array('title' => trans('langAdd') . ' ' . trans('langInsertBlog') ,
                           'url' => $base_url . 'blog&fc_type=2&act_name='. $act_title. '&act_id='.$after_class->activity_id,
                           'icon' => 'fa fa-columns',
                           'level' => 'secondary',
                           'show' => !is_module_disable_FC(MODULE_ID_BLOG,$course_code,$id,$after_class->activity_id)),
                           array('title' => trans('langAdd') . ' ' . trans('langInsertComments') ,
                           'url' => $base_url . 'comments&fc_type=2&act_name='. $act_title. '&act_id='.$after_class->activity_id,
                           'icon' => 'fa fa-comments',
                           'level' => 'secondary',
                           'show' => !is_module_disable_FC(MODULE_ID_COMMENTS,$course_code,$id,$after_class->activity_id)),
                           array('title' => trans('langAdd') . ' ' . trans('langInsertGradebook') ,
                           'url' => $base_url . 'gradebook&fc_type=2&act_name='. $act_title. '&act_id='.$after_class->activity_id,
                           'icon' => 'fa fa-sort-numeric-desc',
                           'level' => 'secondary',
                           'show' => !is_module_disable_FC(MODULE_ID_GRADEBOOK,$course_code,$id,$after_class->activity_id)),
                           array('title' => trans('langAdd') . ' ' . trans('langInsertProgress') ,
                           'url' => $base_url . 'progress&fc_type=2&act_name='. $act_title. '&act_id='.$after_class->activity_id,
                           'icon' => 'fa fa-trophy',
                           'level' => 'secondary',
                           'show' => !is_module_disable_FC(MODULE_ID_PROGRESS,$course_code,$id,$after_class->activity_id))

                           ),'',true)
                            !!}
                        </td><td>
                   {!!  action_button(array(
                       array('title' => $vis == 1? trans('langViewHide') : trans('langViewShow'),
                           'url' => "$_SERVER[REQUEST_URI]&vis_act=$after_class->ID",
                           'icon' => $vis == 1? 'fa-eye-slash' : 'fa-eye'),
                       array('title' => trans('langDelete'),
                           'url' => "$_SERVER[REQUEST_URI]&del_act=$after_class->ID&actid=".$after_class->activity_id,
                           'icon' => 'fa-times',
                           'class' => 'delete',
                           'confirm' => trans('langUnitActivityDeleteConfirm'))))
                    !!}

                   @endif
                   </td></tr><tr><td></td>
           @endforeach
           </tr>
       @endif
        </tbody>
            </table>
            </div>
        </div>
        </div>
        </div>
    @endif

    <div class='row'>
        <div class='col-md-12'>
            <div class='panel panel-default'>
                <div class='panel-body'>
                    <form class='form-horizontal' name='unitselect' action='{{ $urlAppend }}modules/units/' method='get'>
                        <input type='hidden' name='course' value='{{ $course_code }}'>
                        <div class='form-group'>
                            <label class='col-sm-8 control-label'>{{ trans('langCourseUnits') }}</label>
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
                </div>
            </div>
        </div>
    </div>
@endsection

