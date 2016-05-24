@extends('layouts.default')

@section('content')
    {!! isset($action_bar) ?  $action_bar : '' !!}
    <div class='row margin-top-thin margin-bottom-fat'>
        <div class='col-md-12'>
            <div class='panel panel-default'>

                <div class='panel-body'>
                    <div id='course-title-wrapper' class='course-info-title clearfix'>
                        <div class='pull-left h4'>{{ trans('langCourseDescriptionShort') }}</div> 
                        @if ($is_editor)
                            <div class='access access-edit pull-left'>
                                <a href='{{ $urlAppend }}modules/course_home/editdesc.php?course={{ $course_code }}'>
                                    <span class='fa fa-pencil' style='line-height: 30px;' data-toggle='tooltip' data-placement='top' title='Επεξεργασία πληροφοριών'></span>
                                    <span class='hidden'>.</span>
                                </a>
                            </div>                        
                        @endif
                        <ul class='course-title-actions clearfix pull-right list-inline'>
                            <li class='access pull-right'>
                                <a href='javascript:void(0);' style='color: #23527C;''>
                                    <span id='lalou' class='fa fa-info-circle fa-fw' data-container='#course-title-wrapper' data-toggle='popover' data-placement='bottom' data-html='true' data-content='{{ $course_info_popover }}'></span>
                                    <span class='hidden'>.</span>
                                </a>
                            </li>
                            <li class='access pull-right'>
                                <a href='javascript:void(0);'>{!! $lessonStatus !!}</a>
                            </li>
                            <li class='access pull-right'>
                                <a data-modal='citation' data-toggle='modal' data-target='#citation' href='javascript:void(0);'>
                                    <span class='fa fa-paperclip fa-fw' data-toggle='tooltip' data-placement='top' title='{{ trans('langCitation') }}'></span>
                                    <span class='hidden'>.</span>
                                </a>
                            </li>
                            <li class='access pull-right'>
                                <a href='{{ $urlAppend }}modules/user/{{ $is_course_admin ? '' : 'userslist.php' }}?course={{ $course_code }}'>
                                    <span class='fa fa-users fa-fw' data-toggle='tooltip' data-placement='top' title='{{ $numUsers }} {{ trans('langRegistered') }}'></span>
                                    <span class='hidden'>.</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                    @if ($course_info->home_layout == 1)
                        <div class='banner-image-wrapper col-md-5 col-sm-5 col-xs-12'>
                            <div>
                                <img class='banner-image img-responsive' src='{{ isset($course_info->course_image) ? "{$urlAppend}courses/$course_code/image/" . rawurlencode($course_info->course_image) : "$themeimg/ph1.jpg" }}' alt='Course Banner'/>
                            </div>
                        </div>                    
                    @endif
                    <div class='col-xs-12{{ $course_info->home_layout == 1 ? ' col-sm-7' : ''}}'>
                        <div class=''>
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
                    </div>
                    <div class='col-xs-12 course-below-wrapper'>
                        <div class='row text-muted course-below-info'>
                            <div class='col-xs-6'>
                                <strong>{{ trans('langCode') }}:</strong> {{ $course_info->public_code }}<br>
                                <strong>{{ trans('langFaculty') }}:</strong>
                                @foreach ($departments as $key => $department)
                                    {!! $tree->getFullPath($department) !!}
                                    @if ($key+1 < count($departments))
                                        <br>
                                    @endif
                                @endforeach                                
                             </div>
                            <div class='col-xs-6'>
                                @if ($course_info->course_license)
                                    <div class ='text-center'>
                                        <span>{!! copyright_info($course_id) !!}</span> 
                                    </div>                                
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @if(isset($rating_content) || isset($social_content) || isset($comment_content))
                    <div class='panel-footer'>
                        <div class='row'>
                        @if(isset($rating_content))
                            <div class='col-sm-6'>
                                {!! $rating_content !!}
                            </div>       
                        @endif
                        @if(isset($social_content) || isset($comment_content))
                           <div class='text-right{{ isset($rating_content) ? " col-xs-6" : " col-xs-12" }}'>
                                @if(isset($comment_content))
                                    {!! $comment_content !!}
                                @endif
                                @if(isset($social_content) && isset($comment_content))
                                    &nbsp;&nbsp;&nbsp;-&nbsp;&nbsp;&nbsp; 
                                @endif
                                @if(isset($social_content))
                                    {!! $social_content !!}
                                @endif
                            </div>
                        @endif         
                    </div>
                </div>        
            @endif                    
            </div>
        </div>
    </div>
    <div class='row'>
        @if (!$alter_layout)
            <div class='col-md-8 course-units'>
                <div class='row'>
                    <div class='col-md-12'>
                        <div class='content-title pull-left h3'>
                            {{ $course_info->view_type == 'weekly' ? trans('langCourseWeeklyFormat') : trans('langCourseUnits') }}
                        </div>
                        @if ($is_editor and $course_info->view_type == 'units')            
                            <a href='{{ $urlServer }}modules/units/info.php?course={{ $course_code }}' class='pull-left add-unit-btn' data-toggle='tooltip' data-placement='top' title='{{ trans('langAddUnit') }}'>
                                <span class='fa fa-plus-circle'></span>
                                <span class='hidden'>.</span>
                            </a>           
                        @endif
                    </div>
                </div>
                <div class='row boxlist no-list'>
                    @if ($course_units)
                        <?php $count_index = 0;?>
                        @foreach ($course_units as $key => $course_unit)
                            @if ($course_unit->visible == 1)
                               <?php $count_index++; ?>
                            @endif                    
                            <div class='col-xs-12'>
                                <div class='panel clearfix'>
                                    <div class='col-xs-12'>
                                        <div class='item-content'>
                                            <div class='item-header clearfix'>
                                                <div class='item-title h4'>
                                                    @if ($course_info->view_type == 'weekly')             
                                                        <a {!! !$course_unit->visible ? " class='not_visible'" : "" !!} href='{{ $urlServer }}modules/weeks/?course={{ $course_code }}&amp;id={{ $course_unit->id }}&amp;cnt={{ $count_index }}'>
                                                            @if(!empty($course_unit->title))
                                                                {{ $course_unit->title }}                    
                                                            @else
                                                                {{ $count_index.trans('langor') }} {{ trans('langsWeek') }}
                                                            @endif
                                                            ({{ trans('langFrom2') }} {{ nice_format($course_unit->start_week) }} {{ trans('langTill') }} {{ nice_format($course_unit->finish_week) }}) 
                                                        </a>
                                                    @else
                                                        <a{!! !$course_unit->visible ? " class='not_visible'" : "" !!} href='{{ $urlServer }}modules/units/?course={{ $course_code }}&amp;id={{ $course_unit->id }}'>
                                                            {{ $course_unit->title }}
                                                        </a>
                                                    @endif                                                  
                                                </div>
                                                @if ($is_editor)
                                                    <div class='item-side'>
                                                    @if ($course_info->view_type == 'weekly')
                                                        <!-- actions for course weekly format -->
                                                        {!! action_button([
                                                                [
                                                                    'title' => trans('langEditChange'),
                                                                    'url' => $urlAppend . "modules/weeks/info.php?course=$course_code&amp;edit=$course_unit->id&amp;cnt=$count_index",
                                                                    'icon' => 'fa-edit'
                                                                ],
                                                                [   
                                                                    'title' => $course_unit->visible == 1? trans('langViewHide') : trans('langViewShow'),
                                                                    'url' => "$_SERVER[SCRIPT_NAME]?visW=". getIndirectReference($course_unit->id),
                                                                    'icon' => $course_unit->visible == 1? 'fa-eye-slash' : 'fa-eye'
                                                                ],
                                                                [
                                                                  'title' => $course_unit->public == 1? trans('langResourceAccessLock') : trans('langResourceAccessUnlock'),
                                                                  'url' => "$_SERVER[SCRIPT_NAME]?access=". getIndirectReference($course_unit->id),
                                                                  'icon' => $course_unit->public == 1? 'fa-lock' : 'fa-unlock',
                                                                  'show' => $course_info->visible == COURSE_OPEN
                                                                ]
                                                            ]) !!}

                                                    @else
                                                        {!! action_button([
                                                                [
                                                                    'title' => trans('langEditChange'),
                                                                    'url' => $urlAppend . "modules/units/info.php?course=$course_code&amp;edit=$course_unit->id",
                                                                    'icon' => 'fa-edit'
                                                                ],
                                                                [
                                                                    'title' => trans('langDown'),
                                                                    'level' => 'primary',
                                                                    'url' => "$_SERVER[SCRIPT_NAME]?down=". getIndirectReference($course_unit->id),
                                                                    'icon' => 'fa-arrow-down',
                                                                    'disabled' => $key + 1 == count($course_units)
                                                                ],
                                                                [
                                                                    'title' => trans('langUp'),
                                                                    'level' => 'primary',
                                                                    'url' => "$_SERVER[SCRIPT_NAME]?up=". getIndirectReference($course_unit->id),
                                                                    'icon' => 'fa-arrow-up',
                                                                    'disabled' => $key == 0
                                                                ],
                                                                [
                                                                    'title' => $course_unit->visible == 1? trans('langViewHide') : trans('langViewShow'),
                                                                    'url' => "$_SERVER[SCRIPT_NAME]?vis=". getIndirectReference($course_unit->id),
                                                                    'icon' => $course_unit->visible == 1? 'fa-eye-slash' : 'fa-eye'
                                                                ],
                                                                [
                                                                    'title' => $course_unit->public == 1? trans('langResourceAccessLock') : trans('langResourceAccessUnlock'),
                                                                    'url' => "$_SERVER[SCRIPT_NAME]?access=". getIndirectReference($course_unit->id),
                                                                    'icon' => $course_unit->public == 1? 'fa-lock' : 'fa-unlock',
                                                                    'show' => $course_info->visible == COURSE_OPEN
                                                                ],
                                                                [
                                                                    'title' => trans('langDelete'),
                                                                    'url' => "$_SERVER[SCRIPT_NAME]?del=". getIndirectReference($course_unit->id),
                                                                    'icon' => 'fa-times',
                                                                    'class' => 'delete',
                                                                    'confirm' => trans('langCourseUnitDeleteConfirm')
                                                                ]
                                                            ]) !!}
                                                    @endif
                                                    </div> 
                                                @endif
                                            </div>	
                                            <div class='item-body'>
                                                {!! $course_unit->comments == ' ' ? '' : $course_unit->comments !!}
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
                {!! $course_home_main_area_widgets !!}
            </div>
        @endif

        <div class='col-md-{{ $cunits_sidebar_columns }}'> 
            <div class='row'>
                @if (isset($level) && !empty($level)) {
                    <div class='col-md-{{ $cunits_sidebar_subcolumns }}'>
                        <div class='content-title h3'>{{ trans('langOpenCourseShort') }}</div>
                        <div class='panel'>
                            <div class='panel-body'>
                                $opencourses_level
                            </div>
                            <div class='panel-footer'>
                                $opencourses_level_footer
                            </div>
                        </div>
                    </div>
                @endif
                <div class='col-md-{{ $cunits_sidebar_subcolumns }}'>
                    <div class='content-title h3'>{{ trans('langCalendar') }}</div>
                    <div class='panel'>
                        <div class='panel-body'>
                            {!! $user_personal_calendar !!}
                        </div>
                        <div class='panel-footer'>
                            <div class='row'>
                                <div class='col-sm-6 event-legend'>
                                <div>
                                    <span class='event event-important'></span>
                                    <span>{{ trans('langAgendaDueDay') }}</span>
                                </div>
                                <div>
                                    <span class='event event-info'></span>
                                    <span>{{ trans('langAgendaCourseEvent') }}</span>
                                </div>
                            </div>
                            <div class='col-sm-6 event-legend'>
                                <div>
                                    <span class='event event-success'></span>
                                    <span>{{ trans('langAgendaSystemEvent') }}</span>
                                </div>
                                <div>
                                    <span class='event event-special'></span>
                                    <span>{{ trans('langAgendaPersonalEvent') }}</span>
                                </div>
                            </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class='col-md-{{ $cunits_sidebar_subcolumns }}'>
                    <div class='content-title h3'>{{ trans('langAnnouncements') }}</div>
                    <div class='panel'>
                        <div class='panel-body'>
                            <ul class='tablelist'>
                                {!! course_announcements() !!}
                            </ul>
                        </div>
                        <div class='panel-footer clearfix'>
                            <div class='pull-right'>
                                <a href='{{ $urlAppend }}modules/announcements/?course={{ $course_code}}'>
                                    <small>{{ trans('langMore') }}&hellip;</small>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class='col-md-{{ $cunits_sidebar_subcolumns }}'>
                    {!! $course_home_sidebar_widgets !!}
                </div>
            </div>
        </div>
    </div>
    <div class='modal fade' id='citation' tabindex='-1' role='dialog' aria-labelledby='myModalLabel' aria-hidden='true'>
        <div class='modal-dialog'>
            <div class='modal-content'>
                <div class='modal-header'>
                    <button type='button' class='close' data-dismiss='modal' aria-label='Close'>
                        <span aria-hidden='true'>&times;</span>
                    </button>
                    <div class='modal-title h4' id='myModalLabel'>{{ trans('langCitation') }}</div>
                </div>
                <div class='modal-body'>
                    {{ $course_info->prof_names }}&nbsp;
                    <span>{{ $currentCourseName }}</span>&nbsp;
                    {{ trans('langAccessed') }} {{ claro_format_locale_date(trans('dateFormatLong'), strtotime('now')) }}&nbsp;
                    {{ trans('langFrom2') }} {{ $urlServer }}courses/{{$course_code}}/
                </div>                                
            </div>
        </div>
    </div>
    {!! $course_descriptions_modals !!}
@endsection