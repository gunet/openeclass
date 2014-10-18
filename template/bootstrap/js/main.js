function animate_btn(){

    $(".opt-btn-wrapper").hover(
        function(){
            tool_btn_offset =((($(this).children(".opt-btn-more-wrapper").children(".opt-btn-more-tool").length)+1)*56)+"px";
            $(this).children(".opt-btn-more-wrapper").animate({width:tool_btn_offset},150);},
        function(){
            $(this).children(".opt-btn-more-wrapper").animate({width:"56px"},150);});
}


$(document).ready( function () {

    //menuheight();

    // Btn-toggle
    $('.btn-toggle').on('click',function(){
        $(this).toggleClass('btn-toggle-on');
        $('#student-view-form').append($('<input>', {
                    'name': 'next',
                    'value': window.location.pathname + window.location.search,
                    'type': 'hidden'})).submit();
    });

    // Lesson accordion - jQuery ui
    //    $( ".accordion" ).accordion({
    //      heightStyle: "content",
    //      active: false,
    //      collapsible: true,
    //      animate: 200
    //    });
    //
    //    $( ".accordion-first-active" ).accordion({
    //      heightStyle: "fill",
    //      autoHeight: false,
    //      collapsible: true,
    //      animate: 200
    //    });


    // Menu height 
//    function menuheight(){
//
//      var nav_item_height = $('#leftnav .navlist > li:first-child .title').outerHeight();
//      var nav_length = $('#leftnav .navlist > li').length;
//      var logo_height = $('.logo').outerHeight();
//      var window_height = $(window).height();
//
//      var nav_available_height = window_height - logo_height - (nav_item_height*nav_length);
//    
//      $('#leftnav .navlist > li > ul').css('height','150px !important;');
//    }

    $(".expandable-btn").click(function(){
        $(this).toggleClass("active").parents(".action-bar-wrapper").children(".expandable").toggleClass("secondary-active");
    });
    $('[rel=tooltip]').tooltip();

    //window resize
    //    $(window).resize(function() {
    //        $(".accordion-first-active, .accordion").accordion("refresh"); //refresh the accordions
    //    });

    //var tool_length=((($("div.opt-btn-more-tool.tool-btn").length / 2) + 1)*53)+"px";
    //alert(tool_length);
    animate_btn();
    
    $(window).load(function ()
    {
        var initialHeigth;
        var windowHeight = $(window).height();
        var contentHeight = $("#Frame").height();
        //var leftnavHeight = $("#leftnav").height();

        //var offsetHeight = 99+"2em";
        initialHeight = (windowHeight < contentHeight) ? initialHeight = contentHeight - 131 : initialHeight = windowHeight - 131;
        $("#Frame").css({"min-height": initialHeight});
        //leftnavHeight = $("#Frame").height();
        //$("#leftnav").css({"min-height": leftnavHeight});

                // Sidebar switcher
//        $('#toggle-sidebar').click(function () {
//            var sideBarHeight = $("#sidebar").height();
//            var inOut = $("#sidebar").hasClass("in") ? "-18.5em" : "0em";
//            $("#sidebar-container").height(sideBarHeight);
//            $("#sidebar").animate(
//                    {"right": inOut}, {duration: 150, easing: "linear",
//                start: function () {
//                    if (!$("#sidebar").hasClass("in"))
//                    {
//                        $("#sidebar-container").height(sideBarHeight);
//                    }
//                },
//                complete: function () {
//                    $("#toggle-sidebar").toggleClass("toggle-active");
//                    if ($("#sidebar").hasClass("in"))
//                    {
//                        $("#sidebar-container").height(0);
//                    }
//                    $("#sidebar").toggleClass("in");
//                }
//            });
//        });
        
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
