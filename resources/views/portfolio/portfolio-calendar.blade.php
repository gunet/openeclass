@push('bottom_scripts')
    <script type="text/javascript" src="{{ $urlAppend }}js/bootstrap-calendar-master/js/language/el-GR.js?v={{ CACHE_SUFFIX }}"></script>
    <script type="text/javascript" src="{{ $urlAppend }}js/bootstrap-calendar-master/js/calendar.js?v={{ CACHE_SUFFIX }}"></script>
    <script type="text/javascript" src="{{ $urlAppend }}js/bootstrap-calendar-master/components/underscore/underscore-min.js?v={{ CACHE_SUFFIX }}"></script>

    <script>
        var events = [];
        $(function() {
            var calendar = $("#bootstrapcalendar").calendar({
                tmpl_path: "{{ $urlAppend }}js/bootstrap-calendar-master/tmpls/",
                events_source: function() {
                    return events;
                },
                language: "{{ js_escape(trans('langLanguageCode')) }}",
                views: {year:{enable: 0}, week:{enable: 0}, day:{enable: 0}},
                onAfterViewLoad: function(view) {
                    $("#current-month").text(this.getTitle()).attr("aria-label", this.getTitle());
                    $(".btn-group button").removeClass("active");
                    $("button[data-calendar-view='" + view + "']").addClass("active");
                },
                onBeforeEventsLoad: function(done) {
                    var url = "{{ $urlAppend }}main/calendar_data.php";
                    var params = {
                        "from": this.options.position.start.getTime(),
                        "to": this.options.position.end.getTime(),
                    };
                    $.get(url, params)
                        .done(function(data) {
                            if (data.success && data.result && (data.result instanceof Array)) {
                                events = data.result;
                            }
                            done();
                            calendar._render();
                        }).fail(function() {
                            events = [];
                            done();
                            calendar._render();
                        });
                },
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
@endpush

@push('head_styles')
    <link href="{{ $urlAppend }}js/bootstrap-calendar-master/css/calendar_small.css?v={{ CACHE_SUFFIX }}" rel="stylesheet" type="text/css">
    <link href="{{ $urlAppend }}template/modern/css/new_calendar.css?v={{ CACHE_SUFFIX }}" rel="stylesheet" type="text/css">
@endpush

<div class='card bg-transparent card-transparent border-0 sticky-column-course-home mb-3'>
    <div class='card-header border-0 bg-transparent d-flex justify-content-between align-items-center px-0 py-0'>
        <h2 class='text-heading-h3 mb-0'>
            {{ trans('langAgenda') }}
        </h2>
        <a class='text-decoration-underline vsmall-text' href="{{$urlAppend}}main/personal_calendar/index.php">
            {{ trans('langDetails') }}
        </a>
    </div>
</div>
<div class='panel panel-admin panel-admin-calendar card-transparent border-0 mt-lg-0 mt-2 sticky-column-course-home'>

    {!! $user_personal_calendar !!}

</div>

<div class='card bg-transparent card-transparent border-0 sticky-column-course-home'>
    <div class='d-flex justify-content-start align-items-center flex-wrap px-0 py-3'>
        <div class='d-flex align-items-center px-2 py-1'>
            <span class='event event-important'></span>
            <span class="agenda-comment" aria-label="{{ trans('langAgendaDueDay') }}"> {{ trans('langAgendaDueDay') }}</span>
        </div>
        <div class='d-flex align-items-center px-2 py-1'>
            <span class='event event-info'></span>
            <span class="agenda-comment" aria-label="{{ trans('langAgendaCourseEvent') }}">{{ trans('langAgendaCourseEvent') }}</span>
        </div>
        <div class='d-flex align-items-center px-2 py-1'>
            <span class='event event-success'></span>
            <span class="agenda-comment" aria-label="{{ trans('langAgendaSystemEvent') }}">{{ trans('langAgendaSystemEvent') }}</span>
        </div>
        <div class='d-flex align-items-center px-2 py-1'>
            <span class='event event-special'></span>
            <span class="agenda-comment" aria-label="{{ trans('langAgendaPersonalEvent') }}">{{ trans('langAgendaPersonalEvent') }}</span>
        </div>
    </div>
</div>
