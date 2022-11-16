<?php
    $today = getdate();
    $day = $today['mday']; $month = $today['mon']; $year = $today['year'];
    if (isset($uid)) { Calendar_Events::get_calendar_settings(); }
    $user_personal_calendar = Calendar_Events::small_month_calendar($day, $month, $year);
?>


<div class='panel panel-admin panel-Calendar-portfolio border-0 mt-0'>
    <script src="{{ $urlAppend }}js/bootbox/bootbox.min.js?v=4.0-dev"></script>
    <script type="text/javascript" src="{{ $urlAppend }}js/bootstrap-calendar-master/js/language/el-GR.js?v=4.0-dev"></script>
    <link href="{{ $urlAppend }}js/bootstrap-calendar-master/css/calendar_small.css?v=4.0-dev" rel="stylesheet" type="text/css">
    <script type="text/javascript" src="{{ $urlAppend }}js/bootstrap-calendar-master/js/calendar.js?v=4.0-dev"></script>
    <script type="text/javascript" src="{{ $urlAppend }}js/bootstrap-calendar-master/components/underscore/underscore-min.js?v=4.0-dev"></script>
    <script type="text/javascript" src="{{ $urlAppend }}js/sortable/Sortable.min.js?v=4.0-dev"></script>
    {!! $user_personal_calendar !!}
    <script >
        jQuery(document).ready(function() {

            var calendar = $("#bootstrapcalendar").calendar({
                tmpl_path: "{{ $urlAppend }}js/bootstrap-calendar-master/tmpls/",
                events_source: "{{ $urlAppend }}main/calendar_data.php",
                language: "el-GR",
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
    <div class='panel-footer ps-3 pe-3'>
        <div class='row'>
            <div class='col-xxl-6 col-lg-12 col-md-3 col-12 event-legend'>
                <div class='d-inline-flex align-items-center'>
                    <div class='event event-important'></div>
                    <div class="agenda-comment"> {{ trans('langAgendaDueDay') }}</div>
                </div>
            </div>
            <div class='col-xxl-6 col-lg-12 col-md-3 col-12 event-legend'>
                <div class='d-inline-flex align-items-center'>
                    <span class='event event-info'></span>
                    <span class="agenda-comment">{{ trans('langAgendaCourseEvent') }}</span>
                </div>
            </div>
            <div class='col-xxl-6 col-lg-12 col-md-3 col-12 event-legend'>
                <div class='d-inline-flex align-items-center'>
                    <span class='event event-success'></span>
                    <span class="agenda-comment">{{ trans('langAgendaSystemEvent') }}</span>
                </div>
            </div>
            <div class='col-xxl-6 col-lg-12 col-md-3 col-12 event-legend'>
                <div class='d-inline-flex align-items-center'>
                    <span class='event event-special'></span>
                    <span class="agenda-comment">{{ trans('langAgendaPersonalEvent') }}</span>
                </div>
            </div>
        </div>
    </div>
</div>


