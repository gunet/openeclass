$(document).ready(function(){

     // Teacher - Student Button
     $('.btn-toggle').on('click', function () {
        //localStorage.input = $(this).is(':checked');
        $(this).toggleClass('btn-toggle-on');
        $('#student-view-form').append($('<input>', {
            'name': 'next',
            'value': window.location.pathname + window.location.search,
            'type': 'hidden'})).submit();
    });

});

