@extends('layouts.default')

@push('head_scripts')
    <script type='text/javascript'>
        $(document).on('click', '#email_notification', function(e) {
            e.preventDefault();
            var info_message = '';
            var action_message = '';
            var url = $(this).attr('href');
            var varUrl = url.split('?'); /* split url parameters */

            for (i = 0; i < varUrl.length; i++) {
                varUrlName = varUrl[i].split('=');
            }

            var valueMessage = varUrlName[2]; /* value of url parameter 'email_un' */

            if (valueMessage == 1) {
                info_message = "{{ js_escape(trans('langUserEmailNotification')) }}" + "<br><br>" + "{{ js_escape(trans('langConfDisableMailNotification')) }}"
                action_message = " {{ js_escape(trans('langDeactivate')) }} ";
            } else {
                info_message = "{{ js_escape(trans('langNoUserEmailNotification')) }}" + "<br><br>" + "{{ js_escape(trans('langConfEnableMailNotification')) }}";
                action_message = " {{ js_escape(trans('langActivate')) }} ";
            }
            bootbox.confirm({
                title: "{{ js_escape(trans('langEmailUnsubscribe')) }}",
                message: info_message,
                buttons: {
                    confirm: {
                        label: action_message,
                        className: 'submitAdminBtn'
                    },
                    cancel: {
                        label: "{{ js_escape(trans('langCancel')) }}",
                    }
                },
                callback: function(result) {
                    if (result) {
                        window.location.href = url;
                    }
                }
            });
        });
    </script>
@endpush

@if($course_info->view_type == 'units' and $countUnits > 0)
  @push('head_scripts')
    <script src="{{ $urlServer }}/js/sortable/Sortable.min.js"></script>
    <script type='text/javascript'>
        $(document).ready(function(){
            Sortable.create(boxlistSort, {
                    animation: 350,
                    handle: '.fa-arrows',
                    animation: 150,
                    onUpdate: function (evt) {
                        var itemEl = $(evt.item);
                        var idReorder = itemEl.attr('data-id');
                        var prevIdReorder = itemEl.prev().attr('data-id');

                        $.ajax({
                        type: 'post',
                        dataType: 'text',
                        data: {
                            toReorder: idReorder,
                            prevReorder: prevIdReorder,
                        }
                        });
                    }
                });
        });
    </script>
  @endpush
@endif

@push('head_scripts')
    <script>
        $(function() {
            $('body').keydown(function(e) {
                if(e.keyCode == 37 || e.keyCode == 39) {
                    if ($('.modal.in').length) {
                        var visible_modal_id = $('.modal.in').attr('id').match(/\d+/);
                        if (e.keyCode == 37) {
                            var new_modal_id = parseInt(visible_modal_id) - 1;
                        } else {
                            var new_modal_id = parseInt(visible_modal_id) + 1;
                        }
                        var new_modal = $('#hidden_'+new_modal_id);
                        if (new_modal.length) {
                            hideVisibleModal();
                            new_modal.modal('show');
                        }
                    }
                }
            });
        });
        function hideVisibleModal(){
            var visible_modal = $('.modal.in');
            if (visible_modal) { // modal is active
                visible_modal.modal('hide'); // close modal
            }
        };
    </script>
@endpush


@section('content')

<div class="pb-lg-3 pt-lg-3 pb-0 pt-0">
    <div class="container-fluid main-container">
        <div class="row rowMedium">

            <div id="background-cheat-leftnav" class="col-xl-2 col-lg-3 col_sidebar_active">
                <div class="d-none d-sm-block d-sm-none d-md-block d-md-none d-lg-block">
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


                    <div class='col-12'>
                        <div class='panel panel-admin px-lg-4 py-lg-3 bg-white'>
                            <div class='panel-heading bg-body'>
                                <div class='col-12 Help-panel-heading'>
                                    <div class='row'>
                                        <div class='col-md-9 col-auto d-flex align-items-end'>
                                            <span class="panel-title text-uppercase Help-text-panel-heading">{{ trans('langCourseProgram') }}</span>
                                            @if ($is_editor)
                                                @php
                                                    warnCourseInvalidDepartment(true);
                                                @endphp;
                                                <a class='mt-2' href='{{ $urlAppend }}modules/course_home/editdesc.php?course={{ $course_code }}'>
                                                    <span class='fa fa-pencil' data-bs-toggle='tooltip' data-bs-placement='top' title='{{ trans('langEditMeta') }}'></span>
                                                </a>
                                            @endif
                                        </div>
                                        <div class='col-md-3 col'>
                                            {!! $email_notify_icon !!}
                                            <a href='javascript:void(0);' data-bs-modal='citation' data-bs-toggle='modal' data-bs-target='#citation' class='float-end mt-2'>
                                                <span class='fa fa-paperclip fa-fw' data-bs-toggle='tooltip' data-bs-placement='bottom' title data-bs-original-title="{{ trans('langCitation') }}"></span>
                                            </a>
                                            @if($uid)
                                                @if ($is_course_admin)
                                                    <a href="{{ $urlAppend }}modules/user/index.php?course={{$course_code}}" class='float-end me-2 mt-2'>
                                                        <span class="fa fa-users fa-fw" data-bs-toggle="tooltip" data-bs-placement="bottom" title data-bs-original-title="{{ $numUsers }}&nbsp;{{ trans('langRegistered') }}"></span>
                                                    </a>
                                                @else
                                                    @if ($visible == COURSE_CLOSED)
                                                        <a href="{{ $urlAppend }}modules/user/userslist.php?course={{ $course_code }}" class='float-end me-2 mt-2'>
                                                            <span class="fa fa-users fa-fw" data-bs-toggle="tooltip" data-bs-placement="bottom" title data-bs-original-title="{{ $numUsers }}&nbsp;{{ trans('langRegistered') }}"></span>
                                                        </a>
                                                    @endif
                                                @endif
                                            @endif
                                            @if ($offline_course)
                                                <a href="{{ $urlAppend }}modules/offline/index.php?course={{ $course_code }}" class='float-end me-2 mt-2'>
                                                    <span class="fa fa-download fa-fw" data-bs-toggle="tooltip" data-bs-placement="bottom" title data-bs-original-title="{{ trans('langDownloadCourse') }}"></span>
                                                </a>
                                            @endif
                                        </div>
                                    </div>

                                </div>
                            </div>
                            <div class='panel-body'>
                                <div class='row'>
                                    @if($course_info->home_layout == 1)
                                        <div class='col-md-6 col-12'>
                                            <figure>
                                                <picture>
                                                    @if($course_info->course_image)
                                                        <img class='uploadImageCourse' src='{{$urlAppend}}courses/{{$course_code}}/image/{{$course_info->course_image}}' alt='Course Banner'/>
                                                    @else
                                                        <img class='uploadImageCourse' src='{{$urlAppend}}template/modern/img/ph1.jpg'/>
                                                    @endif
                                                </picture>
                                            </figure>
                                        </div>
                                        <div class='col-md-6 col-12'>
                                            <div class='course_info'>
                                                @if ($course_info->description)
                                                        {!! $course_info->description !!}
                                                @else
                                                    <p class='not_visible text-center'> - {{ trans('langThisCourseDescriptionIsEmpty') }} - </p>
                                                @endif
                                            </div>
                                        </div>
                                    @else
                                        <div class='col-12'>
                                            <div class='course_info'>
                                                @if ($course_info->description)
                                                        {!! $course_info->description !!}
                                                @else
                                                    <p class='not_visible text-center'> - {{ trans('langThisCourseDescriptionIsEmpty') }} - </p>
                                                @endif
                                            </div>
                                        </div>
                                    @endif

                                </div>

                                @if ((!$is_editor) and (!$courseDescriptionVisible))
                                    @if ($course_info->course_license)
                                        <div class='col-12 d-flex justify-content-end'>{!! copyright_info($course_id) !!}</div>
                                    @endif
                                @else
                                    <div class='col-12 course-below-wrapper mt-2'>
                                        <div class='row text-muted course-below-info'>

                                            <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 col-12 d-flex justify-content-md-start justify-content-center">
                                                <a role='button' id='btn-syllabus' data-bs-toggle='collapse' href='#collapseDescription' aria-expanded='false' aria-controls='collapseDescription'>
                                                    <span class='fa fa-chevron-right fa-fw'></span>
                                                    <span class='ps-1'>{{ trans('langCourseDescription') }}</span>
                                                </a>
                                                @if($is_editor)
                                                    <span class='ps-2'>{!! $edit_course_desc_link !!}</span>
                                                @endif
                                            </div>



                                            @if ($course_info->course_license)
                                                <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 col-12 mt-md-0 mt-2 d-flex justify-content-end">{!! copyright_info($course_id) !!}</div>
                                            @endif
                                            <div class='col-12'>
                                                <div class='collapse p-0' id='collapseDescription'>
                                                    <div class='col-12 p-0 bg-white'>
                                                        @foreach ($course_descriptions as $row)
                                                            <div class='row mb-3'>
                                                                <div class='col-xl-6 col-12'>
                                                                    <p class='control-label-notes text-start'>{{$row->title}}:</p>
                                                                </div>
                                                                <div class='col-xl-6 col-12 desCourse'>
                                                                    {!! standard_text_escape($row->comments) !!}
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            @if(isset($rating_content) || isset($social_content) || isset($comment_content))
                                <div class='panel-footer mt-0 mb-0 p-0'>
                                    <div class='row'>
                                        @if(isset($rating_content) and isset($social_content) and isset($comment_content))
                                            <div class='col-md-4 col-12 d-flex justify-content-md-start justify-content-center align-items-center'>
                                                <div class='ps-3 pb-2 pt-2 pe-2'>{!! $rating_content !!}</div>
                                            </div>
                                            <div class='col-md-4 col-12 d-flex justify-content-center align-items-center'>
                                                <div class='p-2'>{!! $comment_content !!}</div>
                                            </div>
                                            <div class='col-md-4 col-12 d-flex justify-content-md-end justify-content-center align-items-center'>
                                                <div class='p-2'>{!! $social_content !!}</div>
                                            </div>

                                        @elseif(isset($rating_content) and isset($comment_content) and !isset($social_content))
                                            <div class='col-md-8 col-12 d-flex justify-content-md-start justify-content-center align-items-center'>
                                                <div class='ps-3 pt-2 pb-2 pe-2'>{!! $rating_content !!}</div>
                                            </div>
                                            <div class='col-md-4 col-12 d-flex justify-content-md-end justify-content-center align-items-center'>
                                                <div class='ps-3 pt-2 pb-2 pe-3'>{!! $comment_content !!}</div>
                                            </div>

                                        @elseif(isset($rating_content) and isset($social_content) and !isset($comment_content))
                                            <div class='col-md-7 col-12 d-flex justify-content-md-start justify-content-center align-items-center'>
                                                <div class='ps-3 pt-2 pb-2 pe-2'>{!! $rating_content !!}</div>
                                            </div>
                                            <div class='col-md-5 col-12 col-12 d-flex justify-content-md-end justify-content-center align-items-center'>
                                                <div class='ps-3 pt-2 pb-2 pe-2'>{!! $social_content !!}</div>
                                            </div>

                                        @elseif(isset($comment_content) and isset($social_content) and !isset($rating_content))
                                            <div class='col-md-6 col-12 d-flex justify-content-md-start justify-content-center align-items-center'>
                                                <div class='ps-3 pt-2 pb-2 pe-2'>{!! $comment_content !!}</div>
                                            </div>
                                            <div class='col-md-6 col-12 col-12 d-flex justify-content-md-end justify-content-center align-items-center'>
                                                <div class='ps-3 pt-2 pb-2 pe-2'>{!! $social_content !!}</div>
                                            </div>

                                        @else
                                            @if(isset($rating_content))
                                                <div class='col-12 d-flex justify-content-md-start justify-content-center align-items-center'>
                                                    <div class='ps-3 pt-2 pb-2 pe-2'>{!! $rating_content !!}</div>
                                                </div>
                                            @endif
                                            @if(isset($comment_content))
                                                <div class='col-12 d-flex justify-content-md-start justify-content-center align-items-center'>
                                                    <div class='ps-3 pt-2 pb-2 pe-2'>{!! $comment_content !!}</div>
                                                </div>
                                            @endif
                                            @if(isset($social_content))
                                                <div class='col-12 d-flex justify-content-md-start justify-content-center align-items-center'>
                                                    <div class='ps-3 pt-2 pb-2 pe-2'>{!! $social_content !!}</div>
                                                </div>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            @endif

                        </div>
                    </div>

                    <div class="col-xxl-8 col-xl-7 col-lg-12 col-md-12 col_maincontent_unit mt-4">

                        @if($course_info->view_type == 'units')
                            @if($total_cunits > 0)
                                <div class='panel panel-admin px-lg-4 py-lg-3 bg-white'>
                                    <div class='panel-heading bg-body'>
                                        <div class='col-12 d-inline-flex Help-panel-heading'>
                                            <div class='col-6'>
                                                <span class='panel-title text-uppercase Help-text-panel-heading'>
                                                    {{ trans('langCourseUnits') }}
                                                    @if($is_editor)
                                                        @php $q = Database::get()->querySingle("SELECT flipped_flag FROM course WHERE id = ?d", $course_id); @endphp
                                                        @if($q->flipped_flag==2)
                                                            <a href='{{ $urlServer }}modules/create_course/edit_flipped_classroom.php?course={{ $course_code }}' class='add-unit-btn' data-bs-toggle='tooltip' data-bs-placement='bottom' title='{{ trans("langFlippedEdit") }}'>
                                                            <span class='fa fa-pencil text-warning'></span></a>
                                                        @endif
                                                    @endif
                                                </span>
                                            </div>
                                            <div class='col-6'>
                                                @if ($is_editor and $course_info->view_type == 'units')

                                                    <a href='{{ $urlServer }}modules/units/info.php?course={{ $course_code }}' class='add-unit-btn mt-0 float-end' data-bs-toggle='tooltip' data-bs-placement='bottom' title data-bs-original-title="{{ trans('langAddUnit') }}">
                                                        <span class='fa fa-plus-circle'></span>
                                                    </a>

                                                    <a href='{{ $urlServer }}modules/course_home/course_home.php?course={{ $course_code }}&viewUnit=0' class='add-unit-btn mt-0 float-end' data-bs-toggle='tooltip' data-bs-placement='bottom' title data-bs-original-title="{{ trans('langShowUnitCarousel') }}">
                                                        <span class='fa fa-columns pe-2'></span>
                                                    </a>

                                                    <a href='{{ $urlServer }}modules/course_home/course_home.php?course={{ $course_code }}&viewUnit=1' class='add-unit-btn mt-0 float-end' data-bs-toggle='tooltip' data-bs-placement='bottom' title data-bs-original-title="{{ trans('langShowUnitRow') }}">
                                                        <span class='fa fa-list pe-2 mb-0'></span>
                                                    </a>

                                                @endif
                                                <a class='add-unit-btn mt-0 float-end' id='help-btn' href='{{ $urlServer }}modules/help/help.php?language={{$language}}&topic=course_units' data-bs-toggle='tooltip' data-bs-placement='bottom' title data-bs-original-title="{{ trans('langHelp') }}">
                                                    <span class='fa fa-question-circle @if($is_editor) pe-2 @endif'></span>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class='panel-body' id='boxlistSort'>
                                        {!! $cunits_content !!}
                                    </div>
                                </div>
                            @else
                                <div class='panel panel-admin px-lg-4 py-lg-3 bg-white'>
                                    <div class='panel-heading bg-body'>
                                        <div class='col-12 d-inline-flex Help-panel-heading'>
                                            <div class='col-6 panel-title text-uppercase Help-text-panel-heading'>
                                                {{ trans('langCourseUnits') }}
                                            </div>
                                            <div class='col-6'>
                                                @if($is_editor)

                                                    <a href='{{ $urlServer }}modules/units/info.php?course={{ $course_code }}' class='add-unit-btn mt-0 float-end' data-bs-toggle='tooltip' data-bs-placement='bottom' title data-bs-original-title="{{ trans('langAddUnit') }}">
                                                        <span class='fa fa-plus-circle'></span>
                                                    </a>

                                                @endif
                                                <a class='add-unit-btn mt-0 float-end' id='help-btn' href='{{ $urlServer }}modules/help/help.php?language={{$language}}&topic=course_units' data-bs-toggle='tooltip' data-bs-placement='bottom' title data-bs-original-title="{{ trans('langHelp') }}">
                                                    <span class='fa fa-question-circle @if($is_editor) pe-2 @endif'></span>
                                                </a>
                                            </div>
                                        </div>

                                    </div>
                                    <div class='panel-body'>
                                        <div class='not_visible text-center'> - {{ trans('langNoUnits') }} - </div>
                                    </div>
                                </div>
                            @endif
                        @endif


                        @if($course_info->view_type == 'activity')
                            @if($is_editor)
                                <div class='col-12 d-flex justify-content-center mb-3'>
                                    <a class='btn submitAdminBtn w-100 mt-0 mb-0' href="{{ $urlServer }}modules/course_info/activity_edit.php?course{{$course_code}}"><span class='fa fa-edit me-2'></span>{{trans('langActivityEdit')}}</a>
                                </div>
                            @endif
                            <div class='col-12'>
                                @php
                                    $qVisible = ($is_editor? '': 'AND visible = 1');
                                    $items = Database::get()->queryArray("SELECT activity_content.id, heading, content
                                        FROM activity_heading
                                            LEFT JOIN activity_content
                                                ON activity_heading.id = activity_content.heading_id AND
                                                course_id = ?d
                                        ORDER BY `order`", $course_id);
                                @endphp

                                @foreach ($items as $item)
                                    @if (trim($item->content))
                                        <div class='panel panel-admin px-lg-4 py-lg-3 bg-white mb-3'>
                                            <div class='panel-heading bg-body'>
                                                <div class='col-12 Help-panel-heading'>
                                                    <span class='panel-title text-uppercase Help-text-panel-heading'>
                                                        {!! q(getSerializedMessage($item->heading)) !!}
                                                    </span>
                                                </div>
                                            </div>
                                            <div class='panel-body'>
                                                {!! standard_text_escape($item->content) !!}
                                            </div>

                                            @php
                                                $resources = Database::get()->queryArray("SELECT * FROM unit_resources
                                                    WHERE unit_id = ?d AND `order` >= 0 $qVisible ORDER BY `order`", $item->id);
                                            @endphp

                                            @if (count($resources))
                                                <div class='table-responsive'>
                                                    <table class='table table-striped table-hover'>
                                                        <tbody>
                                                            @foreach ($resources as $info)
                                                                @php $info->comments = standard_text_escape($info->comments); @endphp
                                                                {!! show_resourceWeek($info) !!}
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            @endif
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @endif


                        @if($course_info->view_type == 'wall')
                            @include('layouts.partials.course_wall_functions',['is_editor' => $is_editor])
                        @endif

                        @if($course_info->view_type == 'simple')
                            <div class="panel panel-admin px-lg-4 py-lg-3 bg-white @if($course_info->view_type =='units' or $course_info->view_type =='activity') mt-4 @else mt-0 @endif">
                                <div class='panel-heading bg-body'>
                                    <div class='col-12 d-inline-flex Help-panel-heading'>
                                        <div class='col-6'>
                                            <span class='panel-title text-uppercase Help-text-panel-heading'>{{ trans('langAnnouncements') }}</span>
                                        </div>
                                        <div class='col-6 text-end'>
                                            <a href='{{ $urlAppend }}modules/announcements/index.php?course={{ $course_code }}'
                                            class='mt-0 float-end' data-bs-toggle='tooltip' data-bs-placement='bottom' title data-bs-original-title="{{ trans('langAllAnnouncements') }}">
                                                <span class='fa fa-arrow-right'></span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <div class='panel-body bg-white ps-0 pe-0 pt-1 pb-1'>
                                    <ul class='list-group list-group-flush Borders ps-3 pe-3'>
                                        {!! course_announcements() !!}
                                    </ul>
                                </div>
                            </div>
                        @endif

                        {!! $course_home_main_area_widgets !!}

                    </div><!-- end col units -->

                    <div class="col-xxl-4 col-xl-5 col-lg-12 col-md-12 mt-lg-4 mt-4 float-end ">

                        <div class="panel panel-admin p-0 bg-white">
                            {!! $user_personal_calendar !!}
                            <div class='panel-footer'>
                                <div class='col-12'>
                                    <div class='row rowMedium'>
                                        <div class='col-xl-12 col-lg-6 col-md-3 col-12 event-legend'>
                                            <div class='d-inline-flex align-items-top'>
                                                <span class='event event-important mt-1'></span>
                                                <span>{{ trans('langAgendaDueDay') }}</span>
                                            </div>
                                        </div>

                                        <div class='col-xl-12 col-lg-6 col-md-3 col-12 event-legend'>
                                            <div class='d-inline-flex align-items-top'>
                                                <span class='event event-info mt-1'></span>
                                                <span>{{ trans('langAgendaCourseEvent') }}</span>
                                            </div>
                                        </div>

                                        <div class='col-xl-12 col-lg-6 col-md-3 col-12 event-legend'>
                                            <div class='d-inline-flex align-items-top'>
                                                <span class='event event-success mt-1'></span>
                                                <span>{{ trans('langAgendaSystemEvent') }}</span>
                                            </div>
                                        </div>
                                        <div class='col-xl-12 col-lg-6 col-md-3 col-12 event-legend'>
                                            <div class='d-inline-flex align-items-top'>
                                                <span class='event event-special mt-1'></span>
                                                <span>{{ trans('langAgendaPersonalEvent') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if($course_info->view_type != 'simple')
                            <div class="panel panel-admin px-lg-4 py-lg-3 bg-white mt-4">
                                <div class='panel-heading bg-body'>
                                    <div class='col-12 d-inline-flex Help-panel-heading'>
                                        <div class='col-6'>
                                            <span class='panel-title text-uppercase Help-text-panel-heading'>{{ trans('langAnnouncements') }}</span>
                                        </div>
                                        <div class='col-6 text-end'>
                                            <a href='{{ $urlAppend }}modules/announcements/index.php?course={{ $course_code }}'
                                            class='mt-0 float-end' data-bs-toggle='tooltip' data-bs-placement='bottom' title data-bs-original-title="{{ trans('langAllAnnouncements') }}">
                                                <span class='fa fa-arrow-right'></span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <div class='panel-body bg-white ps-0 pe-0 pt-1 pb-1'>
                                    <ul class='list-group list-group-flush Borders ps-3 pe-3'>
                                        {!! course_announcements() !!}
                                    </ul>
                                </div>
                            </div>
                        @endif

                        {!! $course_home_sidebar_widgets !!}

                        @if(isset($course_completion_id) and $course_completion_id > 0)
                            <div class="panel panel-admin px-lg-4 py-lg-3 bg-white mt-4">
                                <div class='panel-heading bg-body'>
                                    <div class='col-12 Help-panel-heading'>
                                        <span class='panel-title text-uppercase Help-text-panel-heading'>{{ trans('langCourseCompletion') }}</span>
                                    </div>
                                </div>
                                <div class='panel-body'>
                                    <div class='text-center'>
                                        <div class='col-sm-12'>
                                            <div class="center-block d-inline-block">
                                                <a href='{{ $urlServer }}modules/progress/index.php?course={{ $course_code }}&badge_id={{ $course_completion_id}}&u={{ $uid }}'>
                                            @if ($percentage == '100%')
                                                <i class='fa fa-check-circle fa-5x state_success'></i>
                                            @else
                                                <div class='course_completion_panel_percentage'>
                                                    {{ $percentage }}
                                                </div>
                                            @endif
                                            </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if (isset($level) && !empty($level))
                            <div class='panel panel-admin px-lg-4 py-lg-3 bg-white mt-4'>
                                <div class='panel-heading bg-body'>
                                    <div class='col-12 Help-panel-heading'>
                                        <span class='panel-title text-uppercase Help-text-panel-heading'>{{ trans('langOpenCourseShort') }}</span>
                                    </div>
                                </div>
                                <div class='panel-body'>
                                    {!! $opencourses_level !!}
                                    <div class='mt-3 text-center'>
                                        {!! $opencourses_level_footer !!}
                                    </div>
                                </div>
                            </div>
                        @endif


                    </div><!-- end col calendar-announcements-progress -->

                </div> <!-- end row -->

            </div><!-- end col-10 maincontent active-->

        </div>
    </div>
</div>


<div class='modal fade' id='citation' tabindex='-1' role='dialog' aria-labelledby='myModalLabel' aria-hidden='true'>
    <div class='modal-dialog'>
        <div class='modal-content'>
            <div class='modal-header'>
                <button type='button' class='close' data-bs-dismiss='modal' aria-label='Close'>
                    <span aria-hidden='true'>&times;</span>
                </button>
                <div class='modal-title h4' id='myModalLabel'>{{ trans('langCitation') }}</div>
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

@if(!$registered)
    <script type='text/javascript'>
        $(function() {
            $('#passwordModal').on('click', function(e){
                var registerUrl = this.href;
                e.preventDefault();
                @if ($course_info->password !== '')
                    bootbox.dialog({
                        title: '{{ js_escape(trans('langLessonCode')) }}',
                        message: '<form class="form-horizontal" role="form" action="' + registerUrl + '" method="POST" id="password_form">' +
                                    '<div class="form-group">' +
                                        '<div class="col-sm-12">' +
                                            '<input type="text" class="form-control" id="password" name="password">' +
                                            '<input type="hidden" id="register" name="register" value="from-home">' +
                                            "{!! generate_csrf_token_form_field() !!}" +
                                        '</div>' +
                                    '</div>' +
                                '</form>',
                        buttons: {
                            cancel: {
                                label: '{{ js_escape(trans('langCancel')) }}',
                                className: 'cancelAdminBtn'
                            },
                            success: {
                                label: '{{ js_escape(trans('langSubmit')) }}',
                                className: 'submitAmdinBtn',
                                callback: function (d) {
                                    var password = $('#password').val();
                                    if(password != '') {
                                        $('#password_form').submit();
                                    } else {
                                        $('#password').closest('.form-group').addClass('has-error');
                                        $('#password').after('<span class="help-block">{{ js_escape(trans('langTheFieldIsRequired')) }}</span>');
                                        return false;
                                    }
                                }
                            }
                        }
                    });
                @else
                    $('<form method="POST" action="' + registerUrl + '">' +
                        '<input type="hidden" name="register" value="from-home">' +
                        "{!! generate_csrf_token_form_field() !!}" +
                    '</form>').appendTo('body').submit();
                @endif
            });
        });

    </script>

@endif

@endsection
