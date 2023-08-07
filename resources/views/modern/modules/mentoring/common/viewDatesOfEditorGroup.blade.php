

<div id='smallCalendar{{ $editorId }}' class='calendarViewDatesTutorGroup'></div>

    <script type='text/javascript'>
        $(document).ready(function () {

            var calendar = $('#smallCalendar{{ $editorId }}').fullCalendar({
                events : "{{ $urlAppend }}modules/mentoring/programs/group/datesMentor/datesForMentor.php?view=1&show_mentor={{ $editorId }}&show_group={{ $group_id }}",
                eventColor : '#4682B4',
                eventTextColor : 'white',
                selectable : false,
                locale: '{{ $language }}',
                //height   : 500,
                editable : false,
                header:{
                    left: 'prev,next today',
                    center: 'title',
                    right: 'agendaWeek'
                },
                allDaySlot : false,
                displayEventTime: false,
                eventRender: function( event, element, view ) {
                    var title = element.find( '.fc-title' );
                    title.html( title.text() );

                    element.popover({
                        title: event.title,
                        trigger: 'hover',
                        placement: 'top',
                        container: 'body',
                        html: true,
                        sanitize: false
                    });
                },

                
            });

        });
    </script>