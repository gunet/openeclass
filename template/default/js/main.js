// Action Button function
function animate_btn() {
    $(".opt-btn-wrapper").hover(
            function () {
                tool_btn_offset = ((($(this).children(".opt-btn-more-wrapper").children(".opt-btn-more-tool").length) + 1) * 56) + "px";
                $(this).children(".opt-btn-more-wrapper").animate({width: tool_btn_offset}, 150);
            },
            function () {
                $(this).children(".opt-btn-more-wrapper").animate({width: "56px"}, 150);
            });
}
function act_confirm() {
    $('.confirmAction').on('click', function (e) {
        var message = $(this).attr('data-message');
        var title = $(this).attr('data-title');
        var cancel_text = $(this).attr('data-cancel-txt');
        var action_text = $(this).attr('data-action-txt');
        var action_btn_class = $(this).attr('data-action-class');
        var form = $(this).closest('form');
        bootbox.dialog({
            message: message,
            title: title,
            buttons: {
                cancel_btn: {
                    label: cancel_text,
                    className: "btn-default"
                },
                action_btn: {
                    label: action_text,
                    className: action_btn_class,
                    callback: function () {
                        form.submit();
                    }
                }
            }
        });
    });
}
function popover_init() {
    var hidePopover = function () {
        $(this).popover('hide');
    }
    , togglePopover = function () {
        $(this).popover('toggle');
    };
    $('[data-toggle="popover"]').popover().on('click', togglePopover).on('blur', hidePopover);
    $('[data-toggle="popover"]').on('shown.bs.popover', function () {
        act_confirm();
    });
}
$(document).ready(function () {

    // Initialisations
    act_confirm();
    animate_btn();
    $('[rel=tooltip]').tooltip();
    popover_init();

    $(document).on("click", function (e) {
        var target = $(e.target);
        //console.log(target.parents("#leftnav").length);
        if (!(target.parents("#leftnav").length) || target.hasClass(".float-menu")) {
            if ($("#leftnav").hasClass("float-menu-in")) {
                $("#leftnav").animate({
                    "left": "-225px"
                }, 150, function () {
                    $(this).toggleClass("float-menu-in");
                });
            }
        }
    });

    $("#save_note").on("click", function () {
        var note_title = $("#title-note").val();
        var note_text = $("#text-note").val();
        note_text = $('<p/>').text(note_text).wrap('<div/>').parent().html();

        var notesHeight = $(".overlayed").height();
        var notesWidth = $(".overlayed").width();
        console.log(notesHeight);
        console.log(notesWidth);
        $(".spinner-div").removeClass("hidden").css({height: notesHeight, width: notesWidth});
        $.ajax({
            type: "POST",
            url: sidebarConfig.notesLink,
            data: {newTitle: note_title, newContent: note_text, refobjgentype: 0, refcourse: 0, refobjtype: 0, refobjid: 0, submitNote: 1},
            success: function (data) {
                $(".spinner-div p").text(data);
                $(".spinner-div img").toggleClass("hidden");
                $(".spinner-div p").toggleClass("hidden");
                setTimeout(function () {
                    $(".spinner-div").addClass("hidden");
                    $(".spinner-div img").toggleClass("hidden");
                    $(".spinner-div p").toggleClass("hidden");
                    $("#title-note").val('');
                    $("#text-note").val('');
                }, 2000);
            }
        });
    });

//    $("#send_message").on("click", function () {
//        var msg_recipients = $("#msg-recipients").val();
//        var msg_text = $("#msg-text").val();
//        msg_text = $('<p/>').text(note_text).wrap('<div/>').parent().html();
//        var msgHeight = $(".spinner-div").parent().height();
//        var msgWidth = $(".spinner-div").parent().width();
//        $(".spinner-div").removeClass("hidden").css({height: msgHeight, width: msgWidth});
//        $.ajax({
//            type: "POST",
//            url: sidebarConfig.notesLink,
//            data: { message_title: "nanana", body: "na", recipients: "" },
//            success: function(data){
//                $(".spinner-div p").text(data);
//                $(".spinner-div img").toggleClass("hidden");
//                $(".spinner-div p").toggleClass("hidden");
//                setTimeout(function(){
//                    $(".spinner-div").addClass("hidden");
//                    $(".spinner-div img").toggleClass("hidden");
//                    $(".spinner-div p").toggleClass("hidden");
//                    $("#msg-recipients").val('');
//                    $("#msg-text").val('');
//                }, 2000);
//            }
//        });      
//    });



    $(".navbar-toggle").on("click", function (e) {
        if ($("#sidebar").hasClass("in")) {
            $("#sidebar").animate(
                    {"right": "-18.5em"}, {duration: 150, easing: "linear",
                start: function () {
                    if (!$("#sidebar").hasClass("in"))
                        $("#sidebar-container").css({"display": "block"});
                },
                complete: function () {
                    $("#toggle-sidebar").toggleClass("toggle-active");
                    if ($("#sidebar").hasClass("in")) {
                        $("#sidebar-container").css({"display": "none"});
                        $("#sidebar").toggleClass("in");
                    }
                }
            });
        }
        if (!$("#leftnav").hasClass("float-menu-in")) {
            $("#leftnav").animate({
                "left": "0"
            }, 150, function () {
                $(this).toggleClass("float-menu-in");
            });
        } else {
            $(".float-menu").animate({
                "left": "-225px"
            }, 150, function () {
                $(this).toggleClass("float-menu-in");
            });
        }
        e.stopPropagation();
    });

    $(window).on("resize", function () {
        if ($(".float-menu").css("position") === "relative") {
            $(".float-menu").removeAttr("style");
            $(".float-menu").removeClass("float-menu-in");
        }
    });

    // Teacher - Student Button
    $('.btn-toggle').on('click', function () {
        $(this).toggleClass('btn-toggle-on');
        $('#student-view-form').append($('<input>', {
            'name': 'next',
            'value': window.location.pathname + window.location.search,
            'type': 'hidden'})).submit();
    });

    // Leftnav - rotate Category Menu Item icon
    if ($(".collapse.in").length > 0) { //when page first loads the show.bs.collapse event is not triggered
        $(".collapse.in").prev("a").find("i").addClass("fa-rotate-90");
    }
    $('.panel-collapse').on('show.bs.collapse', function () {
        $(this).prev("a").find("i").addClass("fa-rotate-90");
    });
    $('.panel-collapse').on('hide.bs.collapse', function () {
        $(this).prev("a").find("i").removeClass("fa-rotate-90");
    });

    // ScrollTop - When page is scrolled down and we click on menu item then the menu is collapsed
    // and the menu is not inside the viwport. This snippet scrolls the page to the top.
    function scrollToTop(element, time) {
        var targetElement;
        var animateTime;
        if ($(window).scrollTop() != 0) {
            (typeof element === 'undefined') ? targetElement = "html, body" : targetElement = element;
            (typeof time === 'undefined') ? animateTime = 300 : animateTime = time;
            $('html, body').animate({
                scrollTop: $(targetElement).offset().top
            }, animateTime);
        }
        ;
    }

    $(window).scroll(function () {
        if ($(window).scrollTop() > 250) {
            $("#scrollToTop").css("display", "block");
        } else {
            $("#scrollToTop").css("display", "none");
        }
    });

    $("#scrollToTop i").on('click', function () {
        scrollToTop("html, body", 500);
    });

    $('.panel-collapse').on('shown.bs.collapse', function () {
        //scrollToTop($(this).prev('a'),500);  // Uncomment this if you want to make anchor the Parent Menu Item
        scrollToTop("html, body", 500);
    });

    // Action Bar - More Options Button
    $(".expandable-btn").click(function () {
        $(this).toggleClass("active").parents(".action-bar-wrapper").children(".expandable").toggleClass("secondary-active");
    });


    // Actions needed to be done after full DOM elements downloaded
    $(window).load(function ()
    {
        var initialHeight;
        var windowHeight = $(window).height();
        var contentHeight = $("#Frame").height();


        // Initialisation of Main Content height
        var margin_offset = 131;
        var initialHeight = ((contentHeight > windowHeight) ? contentHeight : windowHeight) - margin_offset;
        $("#Frame").css({"min-height": initialHeight});
        $("#sidebar").css({"min-height": initialHeight + margin_offset});


        // Right Side toggle menu animation
        $('#toggle-sidebar').click(function () {
            var inOut = $("#sidebar").hasClass("in") ? "-18.5em" : "0em";

            if ($("#leftnav").hasClass("float-menu-in")) {
                $("#leftnav").animate({
                    "left": "-225"
                }, 150, function () {
                    $(this).toggleClass("float-menu-in");
                });
            }

            if (!$("#sidebar").hasClass("in")) {
                $.ajax({
                    type: "POST",
                    url: sidebarConfig.messagesLink,
                    dataType: "json",
                    success: function (data) {
                        $("ul.sidebar-mymessages").append(data.messages);
                        $(".spinner-div img").toggleClass("hidden");
                        $(".spinner-div p").toggleClass("hidden");
                        setTimeout(function () {
                            $(".spinner-div").addClass("hidden");
                            $(".spinner-div img").toggleClass("hidden");
                            $(".spinner-div p").toggleClass("hidden");
                            $("#title-note").val('');
                            $("#text-note").val('');
                        }, 2000);
                    }
                });
            }

            $("#sidebar").animate(
                    {"right": inOut}, {duration: 150, easing: "linear",
                start: function () {
                    if (!$("#sidebar").hasClass("in"))
                        $("#sidebar-container").css({"display": "block"});
                },
                complete: function () {
                    $("#toggle-sidebar").toggleClass("toggle-active");
                    if ($("#sidebar").hasClass("in"))
                        $("#sidebar-container").css({"display": "none"});
                    $("#sidebar").toggleClass("in");
                }
            });
        });

    });

});
