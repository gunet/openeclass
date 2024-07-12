@extends('layouts.default')
@push('head_scripts')
    <script type='text/javascript'>
        $(document).ready(function() {
            $('#btn-syllabus').click(function () {
                $(this).find('.fa-chevron-right').toggleClass('fa-rotate-90');
            });
            var calendar = $("#bootstrapcalendar").calendar({
                tmpl_path: "{{ $urlAppend }}js/bootstrap-calendar-master/tmpls/",
                    events_source: "{{ $urlAppend }}main/calendar_data.php?course={{ $course_code }}",
                    language: "{{ js_escape(trans('langLanguageCode')) }}",
                    views: {
                        year:{
                            enable: 0
                        },
                        week:{
                            enable: 0
                        },
                        day:{
                            enable: 0
                        }
                    },
                onAfterViewLoad: function(view) {
                    $("#current-month").text(this.getTitle());
                    $(".btn-group button").removeClass("active");
                    $("button[data-calendar-view=\'" + view + "\']").addClass("active");
                }
            });

            $(".btn-group button[data-calendar-nav]").each(function() {
                var $this = $(this);
                $this.click(function() {
                    calendar.navigate($this.data("calendar-nav"));
                });
            });

            $(".btn-group button[data-calendar-view]").each(function() {
                var $this = $(this);
                $this.click(function() {
                    calendar.view($this.data("calendar-view"));
                });
            });

            $('#cu-help-btn').click(function(e) {
                e.preventDefault();
                $.get($(this).attr("href"), function (data) {
                    bootbox.alert({
                        size: 'large',
                        backdrop: true,
                        message: data,
                        buttons: {
                            ok: {
                                label: "{{ js_escape(trans('langClose')) }}",
                                className: "submitAdminBtnDefault"
                            }
                        }
                    });
                });
            });

            $("#email_notification").click(function(e) {
                e.preventDefault();
                var info_message = '';
                var action_message = '';
                var url = $(this).attr("href");
                var varUrl = url.split('?'); /* split url parameters */

                for (i = 0; i < varUrl.length; i++) {
                    varUrlName = varUrl[i].split('=');
                }

                var valueMessage = varUrlName[2]; /* value of url parameter 'email_un' */

                if (valueMessage == 1) {
                    info_message = "{{ js_escape(trans('langUserEmailNotification')) }}" + "<br><br>" + "{{ js_escape(trans('langConfDisableMailNotification')) }}"
                    action_message = " {{ js_escape(trans('langDeactivate')) }} ";
                    $actionButton = "deleteAdminBtn";
                    $actionIcon = "<i class='fa-regular fa-trash-can fa-xl Accent-200-cl'></i>";
                } else {
                    info_message = "{{ js_escape(trans('langNoUserEmailNotification')) }}" + "<br><br>" + "{{ js_escape(trans('langConfEnableMailNotification')) }}";
                    action_message = " {{ js_escape(trans('langActivate')) }} ";
                    $actionButton = "submitAdminBtn";
                    $actionIcon = "<i class='fa-solid fa-cloud-arrow-up fa-xl Neutral-500-cl'></i>";
                }
                bootbox.confirm({
                    closeButton: false,
                    title: "<div class='icon-modal-default'>"+$actionIcon+"</div>"+"<h3 class='modal-title-default text-center mb-0'>"+"{{ js_escape(trans('langEmailUnsubscribe')) }}"+"</h3>",
                    message: "<p class='text-center'>"+info_message+"</p>",
                    buttons: {
                        confirm: {
                            label: action_message,
                            className: $actionButton+' '+'position-center'
                        },
                        cancel: {
                            label: "{{ js_escape(trans('langCancel')) }}",
                            className: 'cancelAdminBtn position-center'
                        }
                    },
                    callback: function(result) {
                        if (result) {
                            window.location.href = url;
                        }
                    }
                });
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


@if (!empty($level))
    @push('head_scripts')
        <link rel='stylesheet' type='text/css' href='{{ $urlAppend }}modules/course_metadata/course_metadata.css'>
        <script type='text/javascript'>
            var dialog;
            var showMetadata = function(course) {
                $('.modal-body', dialog).load('{{ $urlAppend }}modules/course_metadata/anoninfo.php', {course: course}, function(response, status, xhr) {
                    if (status === 'error') {
                        $('.modal-body', dialog).html('Sorry but there was an error, please try again');
                    }
                });
                dialog.modal('show');
            };
            $(document).ready(function() {
                dialog = $("<div class='modal fade' tabindex='-1' role='dialog' aria-labelledby='modal-label' aria-hidden='true'>" +
                    "<div class='modal-dialog modal-lg'>" +
                        "<div class='modal-content'>" +
                            "<div class='modal-header'>" +
                                "<div class='modal-title' id='modal-label'>{{ js_escape((trans('langCourseMetadata'))) }}</div>" +
                                    "<button type='button' class='close' data-bs-dismiss='modal'>" +
                                    "</button>" +
                                "</div>" +
                                "<div class='modal-body'>body</div>" +
                            "</div>" +
                        "</div>" +
                    "</div>"
                );
            });
        </script>
    @endpush
@endif

@section('content')

<main id="main" class="col-12 main-section">
    <div class='{{ $container }} module-container py-lg-0'>
        <div class="course-wrapper course-wrapper-courseHome d-lg-flex align-items-lg-strech w-100">

            <nav id="background-cheat-leftnav" class="col_sidebar_active d-flex justify-content-start align-items-strech ps-lg-0 pe-lg-0">
                <div class="d-none d-sm-block d-sm-none d-md-block d-md-none d-lg-block ContentLeftNav">
                    @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                </div>
            </nav>

            <div class="col_maincontent_active">

                <div class="row">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    <div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="collapseTools">
                        <div class="offcanvas-header">
                            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                        </div>
                        <div class="offcanvas-body">
                            @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                        </div>
                    </div>

                    @include('layouts.partials.legend_view')

                    @if(Session::has('message'))
                        <div class='col-12 all-alerts'>
                            <div class="alert {{ Session::get('alert-class', 'alert-info') }} alert-dismissible fade show" role="alert">
                                @php
                                    $alert_type = '';
                                    if (Session::get('alert-class', 'alert-info') == 'alert-success') {
                                        $alert_type = "<i class='fa-solid fa-circle-check fa-lg'></i>";
                                    } elseif(Session::get('alert-class', 'alert-info') == 'alert-info') {
                                        $alert_type = "<i class='fa-solid fa-circle-info fa-lg'></i>";
                                    } elseif(Session::get('alert-class', 'alert-info') == 'alert-warning') {
                                        $alert_type = "<i class='fa-solid fa-triangle-exclamation fa-lg'></i>";
                                    } else {
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

                </div>

                <div class='d-xl-flex gap-5 mt-0'>

                    <div class='flex-grow-1'>
                        <div class='card panelCard card-transparent border-0'>

                            <div class='card-header card-header-default px-0 py-0 border-0 d-flex justify-content-between align-items-center gap-3 flex-wrap'>
                                <div>
                                    <h3 class='mb-0'>{{ trans('langCourseProgram') }}</h3>
                                </div>
                                <div>
                                    {!! $action_bar !!}
                                </div>
                            </div>
                            <div class='card-body card-body-default pb-0 px-0'>
                                <div class='row m-auto'>
                                    @if($course_info->home_layout == 1)
                                        <div class='col-12 px-0'>
                                            <figure role="none">
                                                <picture>
                                                    @if($course_info->course_image)
                                                        <img class='uploadImageCourse' src='{{$urlAppend}}courses/{{$course_code}}/image/{{$course_info->course_image}}' alt='This is the image of course'/>
                                                    @else
                                                        <img class='uploadImageCourse' src='{{$urlAppend}}template/modern/img/ph1.jpg' alt='No available'/>
                                                    @endif
                                                </picture>
                                            </figure>
                                        </div>
                                        <div class='col-12 mt-1 mb-3 px-0'>
                                            <div class='course_info'>
                                                @if ($course_info->description)
                                                        {!! $course_info->description !!}
                                                @else
                                                    <p class='not_visible text-center'> - {{ trans('langThisCourseDescriptionIsEmpty') }} - </p>
                                                @endif
                                            </div>
                                        </div>
                                    @else
                                        <div class='col-12 px-0'>
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
                            </div>

                            @if(isset($rating_content) || isset($comment_content))
                                <div class='card-footer card-footer-default d-md-flex justify-content-md-between align-items-md-start border-0 mt-0 mb-0 p-0'>
                                    @if(isset($rating_content))
                                        <div>{!! $rating_content !!}</div>
                                    @endif
                                    @if(isset($comment_content))
                                        <div class='mt-md-0 mt-3'>{!! $comment_content !!}</div>
                                    @endif

                                </div>
                            @endif

                        </div>

                        @if(isset($social_content))
                            <div class='col-12 d-flex justify-content-end align-items-start mt-4'>
                                <div>{!! $social_content !!}</div>
                            </div>
                        @endif

                        <div class='col-12 mt-4'>
                            <div class='row'>
                                <div class='panel'>
                                    <div class='panel-group group-section mt-2 px-0' id='accordionDes'>
                                        <ul class="list-group list-group-flush">
                                            <li class="list-group-item px-0 mb-4 bg-transparent">

                                                @if ($courseDescriptionVisible > 0)
                                                    <div class='d-flex justify-content-between border-bottom-default'>
                                                        <a class='accordion-btn d-flex justify-content-start align-items-start gap-2 py-2' role='button' id='btn-syllabus' data-bs-toggle='collapse' href='#collapseDescription' aria-expanded='false' aria-controls='collapseDescription'>
                                                            <i class='fa-solid fa-chevron-down settings-icon'></i>
                                                            {{ trans('langSyllabus') }}
                                                        </a>
                                                    </div>
                                                @endif

                                                <div class='panel-collapse accordion-collapse collapse border-0 rounded-0 mt-3' id='collapseDescription' data-bs-parent='#accordionDes'>
                                                    @if(count($course_descriptions) == 0)
                                                        <div class='col-12 mb-4'>
                                                            <p>{{ trans('langNoSyllabus')}}</p>
                                                        </div>
                                                    @else
                                                        @foreach ($course_descriptions as $row)
                                                            <div class='col-12 mb-4'>
                                                                <p class='form-label text-start'>{{ $row->title }}</p>
                                                                {!! standard_text_escape($row->comments) !!}
                                                            </div>
                                                        @endforeach
                                                    @endif
                                                </div>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>


                        @if($course_info->view_type == 'units')
                            <div class='card panelCard card-transparent px-0 py-0 mt-4 border-0 mb-5'>
                                <div class='card-header card-header-default border-0 d-flex justify-content-between align-items-center px-0 py-0 mb-2'>
                                    <h3>
                                        <div class='d-flex gap-2'>
                                            {{ trans('langCourseUnits') }}
                                        </div>
                                    </h3>

                                    <div class='d-flex gap-2 flex-wrap'>
                                        <a id='cu-help-btn' class='helpAdminBtn' href='{{ $urlServer }}modules/help/help.php?language={{$language}}&topic=course_units' class='add-unit-btn d-flex align-items-center' data-bs-toggle='tooltip' data-bs-placement='bottom' title data-bs-original-title="{{ trans('langHelp') }}" aria-label="{{ trans('langHelp') }}">
                                            <i class="fa-solid fa-circle-info"></i>
                                        </a>
                                        @if($is_editor)
                                            <button class="btn submitAdminBtn" type="button" id="dropdownToolsUnit" data-bs-toggle="dropdown" aria-expanded="false" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa-solid fa-ellipsis-vertical"></i>
                                            </button>

                                            <div class="m-0 p-3 dropdown-menu dropdown-menu-end contextual-menu contextual-border" aria-labelledby="dropdownToolsUnit" style='z-index:1;'>
                                                <ul class="list-group list-group-flush">
                                                    @if ($is_editor)
                                                        <li>
                                                            <a href='{{ $urlServer }}modules/units/info.php?course={{ $course_code }}' class='list-group-item d-flex justify-content-start align-items-start gap-2 py-3'>
                                                                <i class='fa-solid fa-plus settings-icon'></i>
                                                                {{ trans('langAddUnit') }}
                                                            </a>
                                                        </li>
                                                        @if($course_info->flipped_flag == 2)
                                                            <li>
                                                                <a href='{{ $urlServer }}modules/create_course/edit_flipped_classroom.php?course={{ $course_code }}&fromFlipped=1' class='list-group-item d-flex justify-content-start align-items-start gap-2 py-3'>
                                                                    <i class='fa-solid fa-pen-to-square settings-icon'></i>
                                                                    {{ trans('langFlippedEdit') }}
                                                                </a>
                                                            </li>
                                                        @endif
                                                    @endif
                                                    @if($total_cunits > 0 and $is_editor)
                                                        <li>
                                                            <a href='{{ $urlServer }}modules/course_home/course_home.php?course={{ $course_code }}&viewUnit=0' class='list-group-item d-flex justify-content-start align-items-start gap-2 py-3'>
                                                                <i class="fa-solid fa-table-cells-large settings-icon"></i>
                                                                {{ trans('langShowUnitCarousel') }}
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a href='{{ $urlServer }}modules/course_home/course_home.php?course={{ $course_code }}&viewUnit=1' class='list-group-item d-flex justify-content-start align-items-start gap-2 py-3'>
                                                                <i class="fa-solid fa-table-list settings-icon"></i>
                                                                {{ trans('langShowUnitRow') }}
                                                            </a>
                                                        </li>
                                                    @endif
                                                </ul>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <div class='card-body card-body-default px-0 pt-0' id='boxlistSort'>
                                    {!! $cunits_content !!}
                                </div>
                            </div>
                        @endif


                        @if($course_info->view_type == 'activity')
                            @if($is_editor)
                                <div class='col-12 d-flex justify-content-start mb-3 mt-5'>
                                    <a class='btn submitAdminBtnDefault mt-0 mb-2 gap-2' href="{{ $urlServer }}modules/course_info/activity_edit.php?course{{$course_code}}">
                                        <i class='fa-solid fa-edit'></i>
                                        {{trans('langActivityEdit')}}
                                    </a>
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

                                @if(count($items) > 0)

                                    <div class='panel'>
                                        <div class='panel-group group-section' id='accordion' role='tablist' aria-multiselectable='true'>
                                            <ul class="list-group list-group-flush @if($is_editor) mt-3 @else mt-5 @endif">
                                                @foreach ($items as $item)
                                                    @if (trim($item->content))
                                                        <li class="list-group-item px-0 mb-4 bg-transparent">
                                                            <a class='accordion-btn d-flex justify-content-start align-items-start' role='button' data-bs-toggle='collapse' href='#item-{{ $item->id }}' aria-expanded='false' aria-controls='#{{ $faq->id }}'>
                                                                <span class='fa-solid fa-chevron-down'></span>
                                                                {!! q(getSerializedMessage($item->heading)) !!}

                                                            </a>

                                                            <div id='item-{{ $item->id }}' class='panel-collapse accordion-collapse collapse border-0 rounded-0' role='tabpanel' data-bs-parent='#accordion'>
                                                                <div class='panel-body bg-transparent Neutral-900-cl px-4'>
                                                                    {!! standard_text_escape($item->content) !!}

                                                                    @php
                                                                        $resources = Database::get()->queryArray("SELECT * FROM unit_resources
                                                                            WHERE unit_id = ?d AND `order` >= 0 $qVisible ORDER BY `order`", $item->id);
                                                                    @endphp

                                                                    @if (count($resources))
                                                                        <div class='table-responsive'>
                                                                            <table class='table-default table-striped table-hover'>
                                                                                <tbody>
                                                                                    @foreach ($resources as $info)
                                                                                        @php $info->comments = standard_text_escape($info->comments); @endphp
                                                                                        {!! show_resource($info) !!}
                                                                                    @endforeach
                                                                                </tbody>
                                                                            </table>
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </li>
                                                    @endif
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>


                                @endif
                            </div>
                        @endif


                        @if($course_info->view_type == 'wall')
                            @include('layouts.partials.course_wall_functions',['is_editor' => $is_editor])
                        @endif

                        @if($course_info->view_type == 'sessions' && count($course_sessions) > 0)
                            <div class='card panelCard card-transparent card-sessions px-lg-4 py-lg-3 p-3 mt-4 border-0 mb-5'>
                                <div class='card-header card-header-default border-0 d-flex justify-content-between align-items-center px-0 py-0 mb-2'>
                                    <h3>{{ trans('langSession') }}</h3>
                                    <a class='TextRegular text-decoration-underline vsmall-text'
                                         href="{{ $urlAppend }}modules/session/index.php?course={{ $course_code }}">
                                         {{ trans('langAllAnnouncements') }}
                                    </a>
                                </div>
                                <div class='card-body card-body-default px-0 pt-0'>
                                    <ul class="tree-sessions">
                                        <li>
                                            <details open>
                                                <summary><strong>{{ trans('langSession') }}</strong></summary>
                                                <ul>
                                                    <li>
                                                        <details open>
                                                            <summary><strong>{{ trans('langSessionInProgress') }}</strong></summary>
                                                            <ul>
                                                                @php $c1 = 0; @endphp
                                                                @foreach($course_sessions as $s)
                                                                    @if($s->start < $current_time && $s->finish > $current_time)
                                                                        <li>
                                                                            <a class="link-color TextBold" 
                                                                                href="{{ $urlAppend }}modules/session/session_space.php?course={{ $course_code }}&amp;session={{ $s->id }}">
                                                                                {{ $s->title }}
                                                                            </a>
                                                                            &nbsp;<span>({!! format_locale_date(strtotime($s->start), 'short', false) !!})</span>
                                                                            <p>{!! participant_name($s->creator) !!}</p>
                                                                            <div class="spinner-grow text-success" role="status" style='width:15px; height:15px;'>
                                                                                <span class="visually-hidden"></span>
                                                                            </div>
                                                                            <div class="spinner-grow text-danger" role="status" style='width:15px; height:15px;'>
                                                                                <span class="visually-hidden"></span>
                                                                            </div>
                                                                                <div class="spinner-grow text-warning" role="status" style='width:15px; height:15px;'>
                                                                            <span class="visually-hidden"></span>
                                                                            </div>
                                                                            <div class="spinner-grow text-info" role="status" style='width:15px; height:15px;'>
                                                                                <span class="visually-hidden"></span>
                                                                            </div>
                                                                        </li>
                                                                        @php $c1++; @endphp
                                                                    @endif
                                                                @endforeach
                                                                @if($c1 == 0)
                                                                    <li>{{ trans('langNoSessionInProgress') }}</li>
                                                                @endif
                                                            </ul>
                                                        </details>
                                                    </li>
                                                    <li>
                                                        <details open>
                                                            <summary><strong>{{ trans('langNextSession') }}</strong></summary>
                                                            <ul>
                                                                <li>
                                                                    @if($next_session)
                                                                        <a class="link-color TextBold" 
                                                                            href="{{ $urlAppend }}modules/session/session_space.php?course={{ $course_code }}&amp;session={{ $next_session->id }}">
                                                                            {{ $next_session->title }}
                                                                        </a>
                                                                        &nbsp;<span>({!! format_locale_date(strtotime($next_session->start), 'short', false) !!})</span>
                                                                        <p>{!! participant_name($next_session->creator) !!}</p>
                                                                    @else
                                                                        {{ trans('langNoExistsNextSession') }}
                                                                    @endif
                                                                </li>
                                                            </ul>
                                                        </details>
                                                    </li>
                                                    <li>
                                                        <details open>
                                                            <summary><strong>{{ trans('langSessionsNotStarted') }}</strong></summary>
                                                            <ul>
                                                                @php $c3 = 0; @endphp
                                                                @foreach($course_sessions as $s)
                                                                    @if($s->start > $current_time)
                                                                        <li>
                                                                            <a class="link-color TextBold" 
                                                                                href="{{ $urlAppend }}modules/session/session_space.php?course={{ $course_code }}&amp;session={{ $s->id }}">
                                                                                {{ $s->title }}
                                                                            </a>
                                                                            &nbsp;<span>({!! format_locale_date(strtotime($s->start), 'short', false) !!})</span>
                                                                            <p>{!! participant_name($s->creator) !!}</p>
                                                                        </li>
                                                                        @php $c3++; @endphp
                                                                    @endif
                                                                @endforeach
                                                                @if($c3 == 0)
                                                                    <li>{{ trans('langNoSessionsExist') }}</li>
                                                                @endif
                                                            </ul>
                                                        </details>
                                                    </li>
                                                    <li>
                                                        <details>
                                                            <summary><strong>{{ trans('langSessionsHasExpired') }}</strong></summary>
                                                            <ul>
                                                                @php $c4 = 0; @endphp
                                                                @foreach($course_sessions as $s)
                                                                    @if($s->finish < $current_time)
                                                                        <li>
                                                                            <a class="link-color TextBold" 
                                                                                href="{{ $urlAppend }}modules/session/session_space.php?course={{ $course_code }}&amp;session={{ $s->id }}">
                                                                                {{ $s->title }}
                                                                            </a>
                                                                            &nbsp;<span>({!! format_locale_date(strtotime($s->start), 'short', false) !!})</span>
                                                                            <p>{!! participant_name($s->creator) !!}</p>
                                                                        </li>
                                                                        @php $c4++; @endphp
                                                                    @endif
                                                                @endforeach
                                                                @if($c4 == 0)
                                                                    <li>{{ trans('langNoSessionsExist') }}</li>
                                                                @endif
                                                            </ul>
                                                        </details>
                                                    </li>
                                                </ul>
                                            </details>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        @endif

                        @if($course_home_main_area_widgets)
                            {!! html_entity_decode($course_home_main_area_widgets) !!}
                        @endif


                    </div>



                    <div>
                        <div class='card bg-transparent card-transparent border-0 sticky-column-course-home mb-4'>
                            <div class='card-header border-0 bg-transparent d-flex justify-content-between align-items-center px-0 py-0'>
                                <h3 class='mb-0'>{{ trans('langAgenda') }}</h3>
                            </div>
                        </div>
                        <div class="panel panel-admin panel-admin-calendar card-transparent p-0 border-0 sticky-column-course-home">
                            {!! $user_personal_calendar !!}
                        </div>
                        <div class='card bg-transparent card-transparent border-0 sticky-column-course-home'>
                            <div class='d-flex justify-content-start align-items-center flex-wrap px-0 py-3'>
                                <div class='d-flex align-items-center px-2 py-1'>
                                    <span class='event event-important'></span>
                                    <span class='agenda-comment'>{{ trans('langAgendaDueDay') }}</span>
                                </div>
                                <div class='d-flex align-items-center px-2 py-1'>
                                    <span class='event event-info'></span>
                                    <span class='agenda-comment'>{{ trans('langAgendaCourseEvent') }}</span>
                                </div>
                                <div class='d-flex align-items-center px-2 py-1'>
                                    <span class='event event-success'></span>
                                    <span class='agenda-comment'>{{ trans('langAgendaSystemEvent') }}</span>
                                </div>
                                <div class='d-flex align-items-center px-2 py-1'>
                                    <span class='event event-special'></span>
                                    <span class='agenda-comment'>{{ trans('langAgendaPersonalEvent') }}</span>
                                </div>
                            </div>
                        </div>

                        @if ($displayQuickPoll)
                            @include('modules.course_home.quickpoll')
                        @endif

                        <div class="card panelCard card-transparent border-0 mt-5 sticky-column-course-home">
                            <div class='card-header card-header-default px-0 py-0 border-0 d-flex justify-content-between align-items-center gap-3 flex-wrap'>
                                <h3 class='mb-0'>{{ trans('langAnnouncements') }}</h3>
                                <a class='TextRegular text-decoration-underline vsmall-text' href="{{ $urlAppend }}modules/announcements/index.php?course={{ $course_code }}">{{ trans('langAllAnnouncements') }}...</a>
                            </div>
                            <div class='card-body card-body-default px-0 py-0'>
                                <ul class='list-group list-group-flush mt-3'>
                                    {!! course_announcements() !!}
                                </ul>
                            </div>
                        </div>

                        @if ($uid && (isset($is_collaborative_course) and !$is_collaborative_course))
                            @if(isset($course_completion_id) and $course_completion_id > 0)
                                <div class="card panelCard card-transparent border-0 mt-5 sticky-column-course-home">
                                    <div class='card-header card-header-default px-0 py-0 border-0 d-flex justify-content-between align-items-center gap-3 flex-wrap'>
                                        <h3 class='mb-0'>{{ trans('langCourseCompletion') }}</h3>
                                        @if ($is_editor)
                                            <a class='Course-home-ellipsis TextRegular text-decoration-underline vsmall-text' href='{{ $urlServer }}modules/progress/index.php?course={{ $course_code }}&badge_id={{ $course_completion_id }}&progressall=true'>
                                                {{ $certified_users}}/{{ $studentUsers }} {{ trans('langUsersS') }}...
                                            </a>
                                        @else
                                            <a class='Course-home-ellipsis TextRegular text-decoration-underline vsmall-text' href='{{ $urlServer }}modules/progress/index.php?course={{ $course_code }}&badge_id={{ $course_completion_id }}&u={{ $uid }}'>
                                                {{ trans('langDetail') }}...
                                            </a>
                                        @endif
                                    </div>
                                    <div class='card-body card-body-default px-0'>
                                        <div class='text-center'>
                                            <div class='col-12 h-100'>
                                                @if ($is_editor)
                                                    <div class='card statistics-card drop-shadow'>
                                                        <div class='card-body d-flex justify-content-center align-items-center'>
                                                            <a href='{{ $urlServer }}modules/progress/index.php?course={{ $course_code }}&badge_id={{ $course_completion_id }}&progressall=true'>
                                                                @if ($percentage_t == '100')
                                                                    <i class='fa fa-check-circle fa-5x state_success'></i>
                                                                @else
                                                                    @if(get_config('theme_options_id') > 0)
                                                                        <div class='progress-circle-bar' role="progressbar" aria-valuenow="{{ $percentage_t }}" aria-valuemin="0" aria-valuemax="100" style="--value: {{ $percentage_t }}; --size: 9rem;"></div>
                                                                    @else
                                                                        <div id="progress_circle" data-progress="{{ $percentage_t }}" style="--progress: {{ $angle }}deg;">{{ $percentage_t }}%</div>
                                                                    @endif

                                                                @endif
                                                            </a>
                                                        </div>
                                                    </div>

                                                @else
                                                    <div class='card statistics-card drop-shadow'>
                                                        <div class='card-body d-flex justify-content-center align-items-center'>
                                                            <a href='{{ $urlServer }}modules/progress/index.php?course={{ $course_code }}&badge_id={{ $course_completion_id}}&u={{ $uid }}'>
                                                                @if ($percentage == '100')
                                                                    <i class='fa fa-check-circle fa-5x state_success'></i>
                                                                @else
                                                                    @if(get_config('theme_options_id') > 0)
                                                                        <div class='progress-circle-bar' role="progressbar" aria-valuenow="{{ $percentage }}" aria-valuemin="0" aria-valuemax="100" style="--value: {{ $percentage }}; --size: 9rem;"></div>
                                                                    @else
                                                                        <div id="progress_circle" data-progress="{{ $percentage }}" style="--progress: {{ $percentage }}deg;">{{ $percentage }}%</div>
                                                                    @endif

                                                                @endif
                                                            </a>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endif

                        @if(isset($is_collaborative_course) and !$is_collaborative_course)
                            @if (isset($level) && !empty($level))
                                <div class='card panelCard card-transparent border-0 mt-5 sticky-column-course-home'>
                                    <div class='card-header px-0 py-0 border-0 d-flex justify-content-between align-items-center'>
                                        <h3>{{ trans('langOpenCourseShort') }}</h3>
                                    </div>
                                    <div class='card-body card-body-default px-0 py-0'>
                                        {!! $opencourses_level !!}
                                        <div class='mt-3 text-center'>
                                            {!! $opencourses_level_footer !!}
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endif

                        @if(isset($is_collaborative_course) and !$is_collaborative_course)
                            @if($course_home_sidebar_widgets)
                                <div class='card panelCard card-transparent border-0 mt-5 sticky-column-course-home'>
                                    <div class='card-header card-header-default px-0 py-0 border-0 d-flex justify-content-between align-items-center'>
                                        <h3>{{ trans('langWidgets') }}</h3>
                                    </div>
                                    <div class='card-body card-body-default px-0 py-0'>
                                        {!! html_entity_decode($course_home_sidebar_widgets) !!}
                                    </div>
                                </div>
                            @endif
                        @endif

                    </div>
                </div>

            </div> <!-- end row -->

        </div>

    </div>
</main>

<div class='modal fade' id='citation' tabindex='-1' role='dialog' aria-labelledby='myModalLabel' aria-hidden='true'>
    <div class='modal-dialog'>
        <div class='modal-content'>
            <div class='modal-header'>
                <div class='modal-title' id='myModalLabel'>{{ trans('langCitation') }}</div>
                <button type='button' class='close' data-bs-dismiss='modal' aria-label='Close'>
                </button>

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

@if ($displayQuickPoll)
    <script type='text/javascript'>
        $(document).ready(function() {
            var message = '{{ js_escape(trans('langSelectReq')) }}';
            formReqChecker('#homePollForm', message)
        });
    </script>
@endif

@endsection
