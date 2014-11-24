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
    $(window).load(function()
    {
        var initialHeight;
        var windowHeight = $(window).height();
        var contentHeight = $("#Frame").height();


        // Initialisation of Main Content height
        var margin_offset = 131;
        var initialHeight = ((contentHeight > windowHeight) ? contentHeight :  windowHeight ) - margin_offset;
        $("#Frame").css({"min-height": initialHeight});
        $("#sidebar").css({"min-height": initialHeight + margin_offset});
 

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
                    //$("#toggle-sidebar i").toggleClass("fa-rotate-180");
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
