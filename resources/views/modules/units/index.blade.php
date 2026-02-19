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
                                                <summary><h3 class='mb-0'>{{ trans('langDisplayAllUnits')}}</h3></summary>
                                                <ul>
                                                    @foreach ($units as $cu)
                                                        <li {{ $cu->id == $id ? "class=active-unit" : "" }} @if($cu->visible >= 2) style="cursor: default;" @endif>
{{--                                                            <a class='TextBold{{ $cu->id != $id ? "" : " Success-200-cl" }}' href='{{ $urlServer }}modules/units/index.php?course={{ $course_code }}&amp;id={{ $cu->id }}' @if($cu->id == $id) aria-current="{{ $cu->title }}" @endif @if($cu->visible == 2) disabled="" @endif>--}}
                                                            <a class="TextBold{{ $cu->id == $id ? ' Success-200-cl' : '' }}{{ $cu->visible >= 2 ? ' disabled' : '' }}" @if($cu->visible < 2) href="{{ $urlServer }}modules/units/index.php?course={{ $course_code }}&amp;id={{ $cu->id }}" @endif @if($cu->id == $id) aria-current="{{ $cu->title }}" @endif @if($cu->visible >= 2) style="pointer-events: none; cursor: default; opacity: 0.6;" @endif>                                                                {{ $cu->title }}
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
                                @if ($is_editor and $q->flipped_flag == 2)
                                <a aria-label="{{ trans('langActivities') }}" href="{{ $urlAppend }}modules/create_course/course_units_activities.php?course={{ $course_code }}&edit_act={{ $id }}"
                                                           data-bs-toggle="tooltip" data-bs-placement="bottom" title data-bs-original-title="{{ trans('langActivities') }}">
                                    <i class="fa-solid fa-pencil fa-lg"></i>
                                </a>
                                @endif
                                @if($course_start_week or $course_finish_week)
                                    <div>
                                        <small>{{ $course_start_week }}&nbsp;{{ $course_finish_week }}</small>
                                    </div>
                                @endif
                            </div>
                            <div class="card-body">

                                @if ($previousLink or $nextLink)
                                    <div class='col-12 d-flex justify-content-between align-items-center gap-3 flex-wrap mb-4 @if($comments) border-bottom-default pb-4 @endif'>
                                        @if ($previousLink)
                                            <a class='TextBold' title='{{ $previousTitle }}' href='{{ $previousLink}}' aria-label="{{ trans('langPrevUnit') }}{{ $previousTitle }}">
                                                <i class='fa fa-arrow-left space-after-icon'></i>
                                                {{ ellipsize($previousTitle, 30) }}
                                            </a>
                                        @endif
                                        @if ($nextLink)
                                            <a class='TextBold ms-auto' title='{{ $nextTitle }}' href='{{ $nextLink}}' aria-label="{{ trans('langNextUnit') }}{{ $nextTitle }}">
                                                {{ ellipsize($nextTitle, 30) }}
                                                <i class='fa fa-arrow-right space-before-icon'></i>
                                            </a>
                                        @endif
                                    </div>
                                @endif



                                @if($comments)
                                <div style="display: flow-root;">
                                    {!! $comments !!}
                                </div>
                                @endif
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
                                                <option value='{{ $unit->id }}' {{ $unit->id == $unitId ? 'selected' : '' }} {{ $unit->visible >= 2 ? 'disabled' : '' }}>
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

                <div class="modal_in_home d-none">
                    @if ($q_in_home)

                        @foreach ($q_in_home as $in_home)
                            @php
                                $act_title = q($activities[$in_home->activity_id]['title']);
                                $vis = $in_home->visible;
                                $class_vis = $vis == 0 ? 'not_visible not_visible_unit' : '';
                                $act_indirect = $in_home->ID;
                            @endphp
                            <div class="accordion" id="accordion_in_home">
                                <div class="accordion-item">
                                    <h5 class="accordion-header" id="heading_{{$in_home->ID}}">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse_{{$in_home->ID}}" aria-expanded="false" aria-controls="collapse_{{$in_home->ID}}">
                                            {!! $act_title !!}
                                        </button>
                                    </h5>
                                    <div id="collapse_{{$in_home->ID}}" class="accordion-collapse collapse" aria-labelledby="heading_{{$in_home->ID}}" data-bs-parent="#accordion_in_home">
                                        <div class="accordion-body">
                                            <div class="accordion-body">
                                                @if (!is_module_disable_FC(MODULE_ID_EXERCISE, $course_code, $id, $in_home->activity_id))
                                                    <div><a href="{{$base_url . 'exercise&fc_type=0&act_name=' . $act_title . '&act_id=' . $in_home->activity_id}}">{{trans('langAdd')}} {{trans('langInsertExercise')}}</a></div>
                                                @endif
                                                @if (!is_module_disable_FC(MODULE_ID_DOCS, $course_code, $id, $in_home->activity_id))
                                                    <div><a href="{{$base_url . 'doc&fc_type=0&act_name=' . $act_title . '&act_id=' . $in_home->activity_id}}">{{trans('langAdd')}} {{trans('langInsertDoc')}}</a></div>
                                                @endif
                                                @if (!is_module_disable_FC(MODULE_ID_LINKS, $course_code, $id, $in_home->activity_id))
                                                    <div><a href="{{$base_url . 'link&fc_type=0&act_name=' . $act_title . '&act_id=' . $in_home->activity_id}}">{{trans('langAdd')}} {{trans('langInsertLink')}}</a></div>
                                                @endif
                                                @if (!is_module_disable_FC(MODULE_ID_LP, $course_code, $id, $in_home->activity_id))
                                                    <div><a href="{{$base_url . 'lp&fc_type=0&act_name=' . $act_title . '&act_id=' . $in_home->activity_id}}">{{trans('langAdd')}} {{trans('langLearningPath1')}}</a></div>
                                                @endif
                                                @if (!is_module_disable_FC(MODULE_ID_VIDEO, $course_code, $id, $in_home->activity_id))
                                                    <div><a href="{{$base_url . 'video&fc_type=0&act_name=' . $act_title . '&act_id=' . $in_home->activity_id}}">{{trans('langAdd')}} {{trans('langInsertVideo')}}</a></div>
                                                @endif
                                                @if (!is_module_disable_FC(MODULE_ID_FORUM, $course_code, $id, $in_home->activity_id))
                                                    <div><a href="{{$base_url . 'forum&fc_type=0&act_name=' . $act_title . '&act_id=' . $in_home->activity_id}}">{{trans('langAdd')}} {{trans('langInsertForum')}}</a></div>
                                                @endif
                                                @if (!is_module_disable_FC(MODULE_ID_EBOOK, $course_code, $id, $in_home->activity_id))
                                                    <div><a href="{{$base_url . 'ebook&fc_type=0&act_name=' . $act_title . '&act_id=' . $in_home->activity_id}}">{{trans('langAdd')}} {{trans('langInsertEBook')}}</a></div>
                                                @endif
                                                @if (!is_module_disable_FC(MODULE_ID_ASSIGN, $course_code, $id, $in_home->activity_id))
                                                    <div><a href="{{$base_url . 'work&fc_type=0&act_name=' . $act_title . '&act_id=' . $in_home->activity_id}}">{{trans('langAdd')}} {{trans('langInsertWork')}}</a></div>
                                                @endif
                                                @if (!is_module_disable_FC(MODULE_ID_QUESTIONNAIRE, $course_code, $id, $in_home->activity_id))
                                                    <div><a href="{{$base_url . 'poll&fc_type=0&act_name=' . $act_title . '&act_id=' . $in_home->activity_id}}">{{trans('langAdd')}} {{trans('langInsertPoll')}}</a></div>
                                                @endif
                                                @if (!is_module_disable_FC(MODULE_ID_WIKI, $course_code, $id, $in_home->activity_id))
                                                    <div><a href="{{$base_url . 'wiki&fc_type=0&act_name=' . $act_title . '&act_id=' . $in_home->activity_id}}">{{trans('langAdd')}} {{trans('langInsertWiki')}}</a></div>
                                                @endif
                                                @if (!is_module_disable_FC(MODULE_ID_CHAT, $course_code, $id, $in_home->activity_id))
                                                    <div><a href="{{$base_url . 'chat&fc_type=0&act_name=' . $act_title . '&act_id=' . $in_home->activity_id}}">{{trans('langAdd')}} {{trans('langInsertChat')}}</a></div>
                                                @endif
                                                @if (!is_module_disable_FC(MODULE_ID_TC, $course_code, $id, $in_home->activity_id) && is_enabled_tc_server($course_id))
                                                    <div><a href="{{$base_url . 'tc&fc_type=0&act_name=' . $act_title . '&act_id=' . $in_home->activity_id}}">{{trans('langAdd')}} {{trans('langInsertTcMeeting')}}</a></div>
                                                @endif
                                                @if (!is_module_disable_FC(MODULE_ID_GLOSSARY, $course_code, $id, $in_home->activity_id))
                                                    <div><a href="{{$base_url . 'glossary&fc_type=0&act_name=' . $act_title . '&act_id=' . $in_home->activity_id}}">{{trans('langAdd')}} {{trans('langInsertGlossary')}}</a></div>
                                                @endif
                                                @if (!is_module_disable_FC(MODULE_ID_BLOG, $course_code, $id, $in_home->activity_id))
                                                    <div><a href="{{$base_url . 'blog&fc_type=0&act_name=' . $act_title . '&act_id=' . $in_home->activity_id}}">{{trans('langAdd')}} {{trans('langInsertBlog')}}</a></div>
                                                @endif
                                                @if (!is_module_disable_FC(MODULE_ID_COMMENTS, $course_code, $id, $in_home->activity_id))
                                                    <div><a href="{{$base_url . 'comments&fc_type=0&act_name=' . $act_title . '&act_id=' . $in_home->activity_id}}">{{trans('langAdd')}} {{trans('langComments')}}</a></div>
                                                @endif
                                                @if (!is_module_disable_FC(MODULE_ID_GRADEBOOK, $course_code, $id, $in_home->activity_id))
                                                    <div><a href="{{$base_url . 'gradebook&fc_type=0&act_name=' . $act_title . '&act_id=' . $in_home->activity_id}}">{{trans('langAdd')}} {{trans('langOfGradebook')}}</a></div>
                                                @endif
                                                @if (!is_module_disable_FC(MODULE_ID_PROGRESS, $course_code, $id, $in_home->activity_id))
                                                    <div><a href="{{$base_url . 'progress&fc_type=0&act_name=' . $act_title . '&act_id=' . $in_home->activity_id}}">{{trans('langAdd')}} {{trans('langProgress')}}</a></div>
                                                @endif
                                                @if (!is_module_disable_FC(MODULE_ID_H5P, $course_code, $id, $in_home->activity_id))
                                                    <div><a href="{{$base_url . 'h5p&fc_type=0&act_name=' . $act_title . '&act_id=' . $in_home->activity_id}}">{{trans('langAdd')}} {{trans('langOfH5p')}}</a></div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        @endforeach

                    @endif
                </div> {{--modal_in_home--}}

                <div class="modal_in_class d-none">

                    @if ($q_in_class)

                        @foreach ($q_in_class as $in_class)
                            @php
                                $act_title = q($activities[$in_class->activity_id]['title']);
                                $vis = $in_class->visible;
                                $class_vis = $vis == 0 ? 'not_visible not_visible_unit' : '';
                                $act_indirect = $in_class->ID;
                            @endphp
                            <div class="accordion" id="accordion_in_class">
                                <div class="accordion-item">

                                    <h5 class="accordion-header" id="heading_{{$in_class->ID}}">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse_{{$in_class->ID}}" aria-expanded="false" aria-controls="collapse_{{$in_class->ID}}">
                                            {!! $act_title !!}
                                        </button>
                                    </h5>
                                    <div id="collapse_{{$in_class->ID}}" class="accordion-collapse collapse" aria-labelledby="heading_{{$in_class->ID}}" data-bs-parent="#accordion_in_class">
                                        <div class="accordion-body">
                                            <div class="accordion-body">
                                                @if (!is_module_disable_FC(MODULE_ID_EXERCISE, $course_code, $id, $in_class->activity_id))
                                                    <div><a href="{{$base_url . 'exercise&fc_type=1&act_name=' . $act_title . '&act_id=' . $in_class->activity_id}}">{{trans('langAdd')}} {{trans('langInsertExercise')}}</a></div>
                                                @endif
                                                @if (!is_module_disable_FC(MODULE_ID_DOCS, $course_code, $id, $in_class->activity_id))
                                                    <div><a href="{{$base_url . 'doc&fc_type=1&act_name=' . $act_title . '&act_id=' . $in_class->activity_id}}">{{trans('langAdd')}} {{trans('langInsertDoc')}}</a></div>
                                                @endif
                                                @if (!is_module_disable_FC(MODULE_ID_LINKS, $course_code, $id, $in_class->activity_id))
                                                    <div><a href="{{$base_url . 'link&fc_type=1&act_name=' . $act_title . '&act_id=' . $in_class->activity_id}}">{{trans('langAdd')}} {{trans('langInsertLink')}}</a></div>
                                                @endif
                                                @if (!is_module_disable_FC(MODULE_ID_LP, $course_code, $id, $in_class->activity_id))
                                                    <div><a href="{{$base_url . 'lp&fc_type=1&act_name=' . $act_title . '&act_id=' . $in_class->activity_id}}">{{trans('langAdd')}} {{trans('langLearningPath1')}}</a></div>
                                                @endif
                                                @if (!is_module_disable_FC(MODULE_ID_VIDEO, $course_code, $id, $in_class->activity_id))
                                                    <div><a href="{{$base_url . 'video&fc_type=1&act_name=' . $act_title . '&act_id=' . $in_class->activity_id}}">{{trans('langAdd')}} {{trans('langInsertVideo')}}</a></div>
                                                @endif
                                                @if (!is_module_disable_FC(MODULE_ID_FORUM, $course_code, $id, $in_class->activity_id))
                                                    <div><a href="{{$base_url . 'forum&fc_type=1&act_name=' . $act_title . '&act_id=' . $in_class->activity_id}}">{{trans('langAdd')}} {{trans('langInsertForum')}}</a></div>
                                                @endif
                                                @if (!is_module_disable_FC(MODULE_ID_EBOOK, $course_code, $id, $in_class->activity_id))
                                                    <div><a href="{{$base_url . 'ebook&fc_type=1&act_name=' . $act_title . '&act_id=' . $in_class->activity_id}}">{{trans('langAdd')}} {{trans('langInsertEBook')}}</a></div>
                                                @endif
                                                @if (!is_module_disable_FC(MODULE_ID_ASSIGN, $course_code, $id, $in_class->activity_id))
                                                    <div><a href="{{$base_url . 'work&fc_type=1&act_name=' . $act_title . '&act_id=' . $in_class->activity_id}}">{{trans('langAdd')}} {{trans('langInsertWork')}}</a></div>
                                                @endif
                                                @if (!is_module_disable_FC(MODULE_ID_QUESTIONNAIRE, $course_code, $id, $in_class->activity_id))
                                                    <div><a href="{{$base_url . 'poll&fc_type=1&act_name=' . $act_title . '&act_id=' . $in_class->activity_id}}">{{trans('langAdd')}} {{trans('langInsertPoll')}}</a></div>
                                                @endif
                                                @if (!is_module_disable_FC(MODULE_ID_WIKI, $course_code, $id, $in_class->activity_id))
                                                    <div><a href="{{$base_url . 'wiki&fc_type=1&act_name=' . $act_title . '&act_id=' . $in_class->activity_id}}">{{trans('langAdd')}} {{trans('langInsertWiki')}}</a></div>
                                                @endif
                                                @if (!is_module_disable_FC(MODULE_ID_CHAT, $course_code, $id, $in_class->activity_id))
                                                    <div><a href="{{$base_url . 'chat&fc_type=1&act_name=' . $act_title . '&act_id=' . $in_class->activity_id}}">{{trans('langAdd')}} {{trans('langInsertChat')}}</a></div>
                                                @endif
                                                @if (!is_module_disable_FC(MODULE_ID_TC, $course_code, $id, $in_class->activity_id) && is_enabled_tc_server($course_id))
                                                    <div><a href="{{$base_url . 'tc&fc_type=1&act_name=' . $act_title . '&act_id=' . $in_class->activity_id}}">{{trans('langAdd')}} {{trans('langInsertTcMeeting')}}</a></div>
                                                @endif
                                                @if (!is_module_disable_FC(MODULE_ID_GLOSSARY, $course_code, $id, $in_class->activity_id))
                                                    <div><a href="{{$base_url . 'glossary&fc_type=1&act_name=' . $act_title . '&act_id=' . $in_class->activity_id}}">{{trans('langAdd')}} {{trans('langInsertGlossary')}}</a></div>
                                                @endif
                                                @if (!is_module_disable_FC(MODULE_ID_BLOG, $course_code, $id, $in_class->activity_id))
                                                    <div><a href="{{$base_url . 'blog&fc_type=1&act_name=' . $act_title . '&act_id=' . $in_class->activity_id}}">{{trans('langAdd')}} {{trans('langInsertBlog')}}</a></div>
                                                @endif
                                                @if (!is_module_disable_FC(MODULE_ID_COMMENTS, $course_code, $id, $in_class->activity_id))
                                                    <div><a href="{{$base_url . 'comments&fc_type=1&act_name=' . $act_title . '&act_id=' . $in_class->activity_id}}">{{trans('langAdd')}} {{trans('langComments')}}</a></div>
                                                @endif
                                                @if (!is_module_disable_FC(MODULE_ID_GRADEBOOK, $course_code, $id, $in_class->activity_id))
                                                    <div><a href="{{$base_url . 'gradebook&fc_type=1&act_name=' . $act_title . '&act_id=' . $in_class->activity_id}}">{{trans('langAdd')}} {{trans('langOfGradebook')}}</a></div>
                                                @endif
                                                @if (!is_module_disable_FC(MODULE_ID_PROGRESS, $course_code, $id, $in_class->activity_id))
                                                    <div><a href="{{$base_url . 'progress&fc_type=1&act_name=' . $act_title . '&act_id=' . $in_class->activity_id}}">{{trans('langAdd')}} {{trans('langProgress')}}</a></div>
                                                @endif
                                                @if (!is_module_disable_FC(MODULE_ID_H5P, $course_code, $id, $in_class->activity_id))
                                                    <div><a href="{{$base_url . 'h5p&fc_type=1&act_name=' . $act_title . '&act_id=' . $in_class->activity_id}}">{{trans('langAdd')}} {{trans('langOfH5p')}}</a></div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>

                        @endforeach

                    @endif

                </div> {{--modal_in_class--}}

                <div class="modal_after_class d-none">
                    @if ($q_after_class)

                        @foreach ($q_after_class as $after_class)
                            @php
                                $act_title = q($activities[$after_class->activity_id]['title']);
                                $vis = $after_class->visible;
                                $class_vis = $vis == 0 ? 'not_visible not_visible_unit' : '';
                                $act_indirect = $after_class->ID;
                            @endphp
                            <div class="accordion" id="accordion_in_home">
                                <div class="accordion-item">
                                    <h5 class="accordion-header" id="heading_{{$after_class->ID}}">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse_{{$after_class->ID}}" aria-expanded="false" aria-controls="collapse_{{$after_class->ID}}">
                                            {!! $act_title !!}
                                        </button>
                                    </h5>
                                    <div id="collapse_{{$after_class->ID}}" class="accordion-collapse collapse" aria-labelledby="heading_{{$after_class->ID}}" data-bs-parent="#accordion_in_home">
                                        <div class="accordion-body">
                                            <div class="accordion-body">
                                                @if (!is_module_disable_FC(MODULE_ID_EXERCISE, $course_code, $id, $after_class->activity_id))
                                                    <div><a href="{{$base_url . 'exercise&fc_type=2&act_name=' . $act_title . '&act_id=' . $after_class->activity_id}}">{{trans('langAdd')}} {{trans('langInsertExercise')}}</a></div>
                                                @endif
                                                @if (!is_module_disable_FC(MODULE_ID_DOCS, $course_code, $id, $after_class->activity_id))
                                                    <div><a href="{{$base_url . 'doc&fc_type=2&act_name=' . $act_title . '&act_id=' . $after_class->activity_id}}">{{trans('langAdd')}} {{trans('langInsertDoc')}}</a></div>
                                                @endif
                                                @if (!is_module_disable_FC(MODULE_ID_LINKS, $course_code, $id, $after_class->activity_id))
                                                    <div><a href="{{$base_url . 'link&fc_type=2&act_name=' . $act_title . '&act_id=' . $after_class->activity_id}}">{{trans('langAdd')}} {{trans('langInsertLink')}}</a></div>
                                                @endif
                                                @if (!is_module_disable_FC(MODULE_ID_LP, $course_code, $id, $after_class->activity_id))
                                                    <div><a href="{{$base_url . 'lp&fc_type=2&act_name=' . $act_title . '&act_id=' . $after_class->activity_id}}">{{trans('langAdd')}} {{trans('langLearningPath1')}}</a></div>
                                                @endif
                                                @if (!is_module_disable_FC(MODULE_ID_VIDEO, $course_code, $id, $after_class->activity_id))
                                                    <div><a href="{{$base_url . 'video&fc_type=2&act_name=' . $act_title . '&act_id=' . $after_class->activity_id}}">{{trans('langAdd')}} {{trans('langInsertVideo')}}</a></div>
                                                @endif
                                                @if (!is_module_disable_FC(MODULE_ID_FORUM, $course_code, $id, $after_class->activity_id))
                                                    <div><a href="{{$base_url . 'forum&fc_type=2&act_name=' . $act_title . '&act_id=' . $after_class->activity_id}}">{{trans('langAdd')}} {{trans('langInsertForum')}}</a></div>
                                                @endif
                                                @if (!is_module_disable_FC(MODULE_ID_EBOOK, $course_code, $id, $after_class->activity_id))
                                                    <div><a href="{{$base_url . 'ebook&fc_type=2&act_name=' . $act_title . '&act_id=' . $after_class->activity_id}}">{{trans('langAdd')}} {{trans('langInsertEBook')}}</a></div>
                                                @endif
                                                @if (!is_module_disable_FC(MODULE_ID_ASSIGN, $course_code, $id, $after_class->activity_id))
                                                    <div><a href="{{$base_url . 'work&fc_type=2&act_name=' . $act_title . '&act_id=' . $after_class->activity_id}}">{{trans('langAdd')}} {{trans('langInsertWork')}}</a></div>
                                                @endif
                                                @if (!is_module_disable_FC(MODULE_ID_QUESTIONNAIRE, $course_code, $id, $after_class->activity_id))
                                                    <div><a href="{{$base_url . 'poll&fc_type=2&act_name=' . $act_title . '&act_id=' . $after_class->activity_id}}">{{trans('langAdd')}} {{trans('langInsertPoll')}}</a></div>
                                                @endif
                                                @if (!is_module_disable_FC(MODULE_ID_WIKI, $course_code, $id, $after_class->activity_id))
                                                    <div><a href="{{$base_url . 'wiki&fc_type=2&act_name=' . $act_title . '&act_id=' . $after_class->activity_id}}">{{trans('langAdd')}} {{trans('langInsertWiki')}}</a></div>
                                                @endif
                                                @if (!is_module_disable_FC(MODULE_ID_CHAT, $course_code, $id, $after_class->activity_id))
                                                    <div><a href="{{$base_url . 'chat&fc_type=2&act_name=' . $act_title . '&act_id=' . $after_class->activity_id}}">{{trans('langAdd')}} {{trans('langInsertChat')}}</a></div>
                                                @endif
                                                @if (!is_module_disable_FC(MODULE_ID_TC, $course_code, $id, $after_class->activity_id) && is_enabled_tc_server($course_id))
                                                    <div><a href="{{$base_url . 'tc&fc_type=2&act_name=' . $act_title . '&act_id=' . $after_class->activity_id}}">{{trans('langAdd')}} {{trans('langInsertTcMeeting')}}</a></div>
                                                @endif
                                                @if (!is_module_disable_FC(MODULE_ID_GLOSSARY, $course_code, $id, $after_class->activity_id))
                                                    <div><a href="{{$base_url . 'glossary&fc_type=2&act_name=' . $act_title . '&act_id=' . $after_class->activity_id}}">{{trans('langAdd')}} {{trans('langInsertGlossary')}}</a></div>
                                                @endif
                                                @if (!is_module_disable_FC(MODULE_ID_BLOG, $course_code, $id, $after_class->activity_id))
                                                    <div><a href="{{$base_url . 'blog&fc_type=2&act_name=' . $act_title . '&act_id=' . $after_class->activity_id}}">{{trans('langAdd')}} {{trans('langInsertBlog')}}</a></div>
                                                @endif
                                                @if (!is_module_disable_FC(MODULE_ID_COMMENTS, $course_code, $id, $after_class->activity_id))
                                                    <div><a href="{{$base_url . 'comments&fc_type=2&act_name=' . $act_title . '&act_id=' . $after_class->activity_id}}">{{trans('langAdd')}} {{trans('langComments')}}</a></div>
                                                @endif
                                                @if (!is_module_disable_FC(MODULE_ID_GRADEBOOK, $course_code, $id, $after_class->activity_id))
                                                    <div><a href="{{$base_url . 'gradebook&fc_type=2&act_name=' . $act_title . '&act_id=' . $after_class->activity_id}}">{{trans('langAdd')}} {{trans('langOfGradebook')}}</a></div>
                                                @endif
                                                @if (!is_module_disable_FC(MODULE_ID_PROGRESS, $course_code, $id, $after_class->activity_id))
                                                    <div><a href="{{$base_url . 'progress&fc_type=2&act_name=' . $act_title . '&act_id=' . $after_class->activity_id}}">{{trans('langAdd')}} {{trans('langProgress')}}</a></div>
                                                @endif
                                                @if (!is_module_disable_FC(MODULE_ID_H5P, $course_code, $id, $after_class->activity_id))
                                                    <div><a href="{{$base_url . 'h5p&fc_type=2&act_name=' . $act_title . '&act_id=' . $after_class->activity_id}}">{{trans('langAdd')}} {{trans('langOfH5p')}}</a></div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach

                    @endif
                </div> {{--modal_after_class--}}

                <script>
                    // javascript
                    (function($){
                        var closeLabel = @json($langClose ?? trans('langClose'));

                        // Create modal template and append when needed
                        function ensureModal() {
                            var id = 'dynamicModal';
                            var $m = $('#' + id);
                            if ($m.length) return $m;
                            var tpl = '\
                        <div class="modal fade" id="' + id + '" tabindex="-1" aria-hidden="true">\
                          <div class="modal-dialog modal-lg modal-dialog-centered">\
                            <div class="modal-content">\
                              <div class="modal-header">\
                                <h5 class="modal-title"></h5>\
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>\
                              </div>\
                              <div class="modal-body"></div>\
                              <div class="modal-footer">\
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">' + closeLabel + '</button>\
                              </div>\
                            </div>\
                          </div>\
                        </div>';
                            $('body').append(tpl);
                            return $('#' + id);
                        }

                        function showModal(content, title) {
                            var $m = ensureModal();
                            $m.find('.modal-title').text(title || '');
                            $m.find('.modal-body').html(content || '');
                            var bsModal = new bootstrap.Modal($m[0], { backdrop: true });
                            // remove modal from DOM after hide to keep things clean
                            $m.one('hidden.bs.modal', function () { $m.remove(); });
                            bsModal.show();
                        }

                        function openFromSelector(selector, title, href) {
                            var local = $.trim($(selector).html() || '');
                            if (local && (!href || href === '#')) {
                                showModal(local, title);
                                return;
                            }
                            if (href) {
                                $.ajax({
                                    url: href,
                                    method: 'GET',
                                    dataType: 'html'
                                }).done(function(data){
                                    showModal(data, title);
                                }).fail(function(xhr){
                                    // simple fallback alert on error
                                    alert((xhr.status ? xhr.status + ' ' : '') + (xhr.statusText || 'Error loading content'));
                                });
                            }
                        }

                        // mapping buttons to modal selectors
                        var map = {
                            '.in_home_btn': '.modal_in_home',
                            '.in_class_btn': '.modal_in_class',
                            '.after_class_btn': '.modal_after_class'
                        };

                        // delegated handlers
                        $(document).on('click', '.in_home_btn, .in_class_btn, .after_class_btn', function(e){
                            e.preventDefault();
                            var $btn = $(this);
                            var href = $btn.attr('href');
                            var selector = null;
                            if ($btn.is('.in_home_btn')) selector = map['.in_home_btn'];
                            else if ($btn.is('.in_class_btn')) selector = map['.in_class_btn'];
                            else if ($btn.is('.after_class_btn')) selector = map['.after_class_btn'];
                            // optional title from data-title attribute or button text
                            var title = $btn.data('title') || $btn.attr('title') || $btn.text().trim();
                            if (selector) openFromSelector(selector, title, href);
                        });

                    })(jQuery);

                </script>

            </div>
        </div>
    </div>
</div>

@endsection
