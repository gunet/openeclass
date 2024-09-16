

<div id='smallCalendar{{ $editorId }}' class='calendarViewDatesTutorGroup'></div>

<script type='text/javascript'>
    $(document).ready(function () {

        var calendar = $('#smallCalendar{{ $editorId }}').fullCalendar({
            events : "{{ $urlAppend }}modules/group/datesTutor.php?view=1&show_tutor={{ $editorId }}&show_group={{ $group_id }}&lesson_id={{ $CourseID }}",
            defaultView: 'listMonth',
            eventColor : '#687DA3',
            eventTextColor : 'white',
            selectable : false,
            locale: '{{ $language }}',
            height   : 400,
            editable : false,
            header:{
                left: 'prev,next ',
                center: 'title',
                right: ''
            },
            allDaySlot : false,
            displayEventTime: false,
            eventRender: function( event, element, view ) {
                var title = element.find( '.fc-list-item-title' );
                title.html( title.text() );

                // element.popover({
                //     title: event.title,
                //     trigger: 'hover',
                //     placement: 'top',
                //     container: 'body',
                //     html: true,
                //     sanitize: false
                // });
            },

            
        });

    });
</script>