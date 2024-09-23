@extends('layouts.default')

@section('content')

<div class="col-12 main-section">
<div class='container module-container py-lg-0'>
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

                    {!! isset($action_bar) ?  $action_bar : '' !!}


                        <div class='col-12'>
                            <div class='card panelCard card-default px-lg-4 py-lg-3'>

                                <div class='card-body'>
                                    <div id='course-title-wrapper' class='course-info-title d-flex justify-content-between align-items-start'>
                                        <h3>
                                            {{ trans('langDescription') }}
                                        </h3>
                                        <ul class='course-title-actions clearfix float-end list-inline'>
                                            <li class='access float-end'>
                                                <a href='javascript:void(0);' style='color: #23527C;'>
                                                    <span id='lalou' class='fa fa-info-circle fa-fw' data-bs-container='#course-title-wrapper' data-bs-toggle='popover' data-bs-placement='bottom' data-bs-html='true' data-bs-content='{{ $course_info_popover }}'></span>
                                                    <span class='hidden'>.</span>
                                                </a>
                                            </li>
                                            <li class='access float-end'>
                                                <a href='javascript:void(0);'>{!! $lessonStatus !!}</a>
                                            </li>
                                            <li class='access float-end'>
                                                <a data-modal='citation' data-bs-toggle='modal' data-bs-target='#citation' href='javascript:void(0);' aria-label="{{ trans('langCitation') }}">
                                                    <span class='fa fa-paperclip fa-fw' data-bs-toggle='tooltip' data-bs-placement='top' title='{{ trans('langCitation') }}'></span>
                                                    <span class='hidden'>.</span>
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                    @if ($course_info->home_layout == 1)
                                        <div class='banner-image-wrapper col-12 mt-3'>
                                            <div>
                                                <img class='banner-image img-responsive' src='{{ isset($course_info->course_image) ? "{$urlAppend}courses/$course_code/image/" . rawurlencode($course_info->course_image) : "$themeimg/ph1.jpg" }}' alt='Course Banner'/>
                                            </div>
                                        </div>
                                    @endif
                                    <div class='col-12 mt-3'>
                                        <div class='course_info'>
                                            @if ($course_info->description)
                                                <!--Hidden html text to store the full description text & the truncated desctiption text so as to be accessed by javascript-->
                                                <div id='not_truncated' class='hidden'>
                                                    {!! $full_description !!}
                                                </div>
                                                <div id='truncated' class='hidden'>
                                                    {!! $truncated_text !!}
                                                </div>
                                                <!--Show the description text-->
                                                <div id='descr_content' class='is_less'>
                                                    {!! $truncated_text !!}
                                                </div>
                                            @else
                                                <p class='not_visible'> - {{ trans('langThisCourseDescriptionIsEmpty') }} - </p>
                                            @endif
                                        </div>
                                    </div>
                                    <div class='col-12 course-below-wrapper mt-3'>
                                        <div class='row text-muted course-below-info'>
                                            <div class='col-6'>
                                                <strong>{{ trans('langCode') }}: </strong> {{ $course_info->public_code }}<br>
                                                <strong>{{ trans('langFaculty') }}: </strong>
                                                {!! $departments !!}
                                            </div>
                                            <div class='col-6'>
                                                @if ($course_info->course_license)
                                                    <div class ='text-center'>
                                                        <strong>{{ trans('langLicense') }}:</strong><br>
                                                        <span>{!! copyright_info($course_id, 0) !!}</span>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    
                   
                        <div class='col-12 mt-5'>
                            <div class='card panelCard card-default px-lg-4 py-lg-3 mt-4'>
                                <div class='card-body'>
                                    <div class='col-12 d-flex justify-content-between align-items-start'>
                                        <h3>
                                            {{ $course_info->view_type == 'weekly' ? trans('langCourseWeeklyFormat') : trans('langCourseUnits') }}
                                        </h3>
                                        <a aria-label="{{ trans('langHelp') }}" class='text-start add-unit-btn' id='help-btn' href='{{ $urlAppend }}modules/help/help.php?language={{ $language}}&topic=course_units' data-bs-toggle='tooltip' data-bs-placement='top' title='{{ trans('langHelp') }}'>
                                            <span class='fa fa-question-circle'></span>
                                        </a>
                                    </div>
                                    <div class='col-12 mt-3'>
                                        <div class='row boxlist no-list'>
                                            @if ($course_units)
                                                <?php $count_index = 0;?>
                                                @foreach ($course_units as $key => $course_unit)
                                                    <?php $count_index++; ?>
                                                    <div class='col-12 mb-4'>
                                                        <div class='panel clearfix'>
                                                            <div class='col-12'>
                                                                <div class='item-content'>
                                                                    <div class='item-header clearfix'>
                                                                        <div class='item-title '>
                                                                            @if ($course_info->view_type == 'weekly')
                                                                                <a href="{{ $urlAppend }}modules/weeks/index.php?course={{ $course_code }}&amp;id={{ $course_unit->id }}&amp;cnt={{ $count_index }}">
                                                                                    @if(!empty($course_unit->title))
                                                                                        {{ $course_unit->title }}
                                                                                    @else
                                                                                        {{ $count_index.trans('langor') }} {{ trans('langsWeek') }}
                                                                                    @endif
                                                                                    ({{ trans('langFrom2') }} {{ format_locale_date(strtotime($course_unit->start_week), 'short', false) }} {{ trans('langTill') }} {{ format_locale_date(strtotime($course_unit->finish_week), 'short', false) }})
                                                                                </a>
                                                                            @else
                                                                                <a href="modules/unit/{{ $course_unit->id }}.html">
                                                                                    {{ $course_unit->title }}
                                                                                </a>
                                                                            @endif
                                                                        </div>
                                                                    </div>
                                                                    <div class='item-body'>
                                                                        {!! $course_unit->comments == ' ' ? '' : standard_text_escape($course_unit->comments) !!}
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            @else
                                                <div class='col-sm-12'>
                                                    <div class='panel'>
                                                        <div class='panel-body not_visible'> - {{ trans('langNoUnits') }} - </div>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            {!! $course_home_main_area_widgets !!}
                        </div>
                   
                        <div class='modal fade' id='citation' tabindex='-1' role='dialog' aria-labelledby='myModalLabel' aria-hidden='true'>
                            <div class='modal-dialog'>
                                <div class='modal-content'>
                                    <div class='modal-header'>
                                        <div class='modal-title' id='myModalLabel'>{{ trans('langCitation') }}</div>
                                        <button type='button' class='close' data-bs-dismiss='modal' aria-label="{{ trans('langClose') }}"></button>
                                        
                                    </div>
                                    <div class='modal-body'>
                                        {{ $course_info->prof_names }}&nbsp;
                                        <span>{{ $currentCourseName }}</span>&nbsp;
                                        {{ trans('langAccessed') }} {{ format_locale_date(strtotime('now')) }}&nbsp;
                                        {{ trans('langFrom2') }} {{ $urlServer }}courses/{{$course_code}}/
                                    </div>
                                </div>
                            </div>
                        </div>

                        {!! $course_descriptions_modals !!}
                </div>
            </div>
        </div>
    
</div>
</div>
@endsection
