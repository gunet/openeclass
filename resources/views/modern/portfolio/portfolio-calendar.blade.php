<?php
    $today = getdate();
    $day = $today['mday']; $month = $today['mon']; $year = $today['year'];
    if (isset($uid)) {
        Calendar_Events::get_calendar_settings();
    }
    $user_personal_calendar = Calendar_Events::small_month_calendar($day, $month, $year);
?>

<div class='card bg-transparent card-transparent border-0 sticky-column-course-home mb-4'>
    <div class='card-header border-0 bg-transparent d-flex justify-content-between align-items-center px-0 py-0'>
        <h3 class='mb-0'>
            {{ trans('langAgenda') }}
        </h3>
        <a class='text-decoration-underline vsmall-text' href="{{$urlAppend}}main/personal_calendar/index.php">
            {{ trans('langDetails') }}
        </a>
    </div>
</div>
<div class='panel panel-admin panel-admin-calendar card-transparent border-0 mt-lg-0 mt-2 sticky-column-course-home'>

    <script src="{{ $urlAppend }}js/bootbox/bootbox.min.js?v=4.0-dev"></script>
    <script type="text/javascript" src="{{ $urlAppend }}js/bootstrap-calendar-master/js/language/el-GR.js?v=4.0-dev"></script>
    <link href="{{ $urlAppend }}js/bootstrap-calendar-master/css/calendar_small.css?v=4.0-dev" rel="stylesheet" type="text/css">
    <link href="{{ $urlAppend }}template/modern/css/new_calendar.css?v=4.0-dev" rel="stylesheet" type="text/css">
    <script type="text/javascript" src="{{ $urlAppend }}js/bootstrap-calendar-master/js/calendar.js?v=4.0-dev"></script>
    <script type="text/javascript" src="{{ $urlAppend }}js/bootstrap-calendar-master/components/underscore/underscore-min.js?v=4.0-dev"></script>
    <script type="text/javascript" src="{{ $urlAppend }}js/sortable/Sortable.min.js?v=4.0-dev"></script>

    {!! $user_personal_calendar !!}

    <script >
        jQuery(document).ready(function() {

            var calendar = $("#bootstrapcalendar").calendar({
                tmpl_path: "{{ $urlAppend }}js/bootstrap-calendar-master/tmpls/",
                events_source: "{{ $urlAppend }}main/calendar_data.php",
                language: "{{ js_escape(trans('langLanguageCode')) }}",
                views: {year:{enable: 0}, week:{enable: 0}, day:{enable: 0}},
                onAfterViewLoad: function(view) {
                    $("#current-month").text(this.getTitle());
                    $(".btn-group button").removeClass("active");
                    $("button[data-calendar-view='" + view + "']").addClass("active");
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
        });

        function show_month(day,month,year){
            $.get("calendar_data.php",{caltype:"small", day:day, month: month, year: year}, function(data){$("#smallcal").html(data);});
        }

    </script>
</div>

<div class='card bg-transparent card-transparent border-0 sticky-column-course-home'>
    <div class='d-flex justify-content-start align-items-center flex-wrap px-0 py-3'>
        <div class='d-flex align-items-center px-2 py-1'>
            <span class='event event-important'></span>
            <span class="agenda-comment"> {{ trans('langAgendaDueDay') }}</span>
        </div>
        <div class='d-flex align-items-center px-2 py-1'>
            <span class='event event-info'></span>
            <span class="agenda-comment">{{ trans('langAgendaCourseEvent') }}</span>
        </div>
        <div class='d-flex align-items-center px-2 py-1'>
            <span class='event event-success'></span>
            <span class="agenda-comment">{{ trans('langAgendaSystemEvent') }}</span>
        </div>
        <div class='d-flex align-items-center px-2 py-1'>
            <span class='event event-special'></span>
            <span class="agenda-comment">{{ trans('langAgendaPersonalEvent') }}</span>
        </div>
    </div>
</div>


