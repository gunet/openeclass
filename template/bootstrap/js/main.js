// Action Button function
function animate_btn(){

    $(".opt-btn-wrapper").hover(
        function(){
            tool_btn_offset =((($(this).children(".opt-btn-more-wrapper").children(".opt-btn-more-tool").length)+1)*56)+"px";
            $(this).children(".opt-btn-more-wrapper").animate({width:tool_btn_offset},150);},
        function(){
            $(this).children(".opt-btn-more-wrapper").animate({width:"56px"},150);});
}


$(document).ready( function () {
    
    
    // Initialisations
    animate_btn();
    $('[rel=tooltip]').tooltip();


    // Teacher - Student Button
    $('.btn-toggle').on('click',function(){
        $(this).toggleClass('btn-toggle-on');
        $('#student-view-form').append($('<input>', {
                    'name': 'next',
                    'value': window.location.pathname + window.location.search,
                    'type': 'hidden'})).submit();
    });


    // Action Bar - More Options Button
    $(".expandable-btn").click(function(){
        $(this).toggleClass("active").parents(".action-bar-wrapper").children(".expandable").toggleClass("secondary-active");
    });
    
    
    // Actions needed to be done after full DOM elements downloaded
    $(window).load(function ()
    {
        var initialHeigth;
        var windowHeight = $(window).height();
        var contentHeight = $("#Frame").height();


        // Initialisation of Main Content height
        initialHeight = (windowHeight < contentHeight) ? initialHeight = contentHeight - 131 : initialHeight = windowHeight - 131;
        $("#Frame").css({"min-height": initialHeight});


        // Right Side toggle menu animation
        $('#toggle-sidebar').click(function () {
            var inOut = $("#sidebar").hasClass("in") ? "-18.5em" : "0em";
            $("#sidebar").animate(
                    {"right": inOut}, {duration: 150, easing: "linear",
                start: function () {
                    if (!$("#sidebar").hasClass("in"))
                    {
                        $("#sidebar-container").css({"display":"block"});
                    }
                },
                complete: function () {
                    $("#toggle-sidebar").toggleClass("toggle-active");
                    if ($("#sidebar").hasClass("in"))
                    {
                        $("#sidebar-container").css({"display":"none"});
                    }
                    $("#sidebar").toggleClass("in");
                }
            });
        });

    });
    
}); 
