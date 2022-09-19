$(document).ready(function(){

     // Teacher - Student Button
     $('.btn-toggle').on('click', function () {
        $(this).toggleClass('btn-toggle-on');
        $('#student-view-form').append($('<input>', {
            'name': 'next',
            'value': window.location.pathname + window.location.search,
            'type': 'hidden'})).submit();
    });

});

