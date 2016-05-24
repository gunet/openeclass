function act_confirm() {
    $('.confirmAction').on('click', function (e) {
        var message = $(this).attr('data-message');
        var title = $(this).attr('data-title');
        var cancel_text = $(this).attr('data-cancel-txt');
        var action_text = $(this).attr('data-action-txt');
        var action_btn_class = $(this).attr('data-action-class');
        var form = $(this).closest('form').attr('action');
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
                        window.location = form;
                    }
                }
            }
        });
    });
}

function popover_init() {
    $('[data-toggle="popover"]').on('click',function(e){
        e.preventDefault();
    }).popover();
    var click_in_process = false;
    var hidePopover = function () {
        if (!click_in_process) {
            $(this).popover('hide');
        }
    }
    , togglePopover = function () {
        $(this).popover('toggle');
        $('#action_button_menu').parent().parent().addClass('menu-popover');
    };
    $('.menu-popover').popover().on('click', togglePopover).on('blur', hidePopover);
    $('.menu-popover').on('shown.bs.popover', function () {
        $('.popover').mousedown(function () {
            click_in_process = true;
        });
        $('.popover').mouseup(function () {
            click_in_process = false;
            $(this).popover('hide');
        });
        act_confirm();
    });

}

function tooltip_init() {
    $('[data-toggle="tooltip"]').tooltip({container: 'body'});
}

function truncate_toggle(click_elem, truncated, not_truncated, container_elem){

    /*
    / click_elem -> The element where the click event handler will be attached
    / truncated -> The element in dom which is hidden and stores the truncated text
    / not_truncated -> The element in dom which is hidden and stores the full text
    / container_elem -> The container of the toggled text
    */

    var show_text = function(e) {

        var expand = $(container_elem).hasClass('is_less');

        if(expand) {
            // Get the full text stored in the hidden div with id -> not_truncated
            var full_text = $(not_truncated).html();
            $(container_elem).html(full_text);
            $(container_elem).toggleClass('is_less');
        } else {
            // Get the truncated text stored in the hidden div with id -> truncated
            var less_text = $(truncated).html();
            $(container_elem).html(less_text);
            $(container_elem).toggleClass('is_less');
        }
        e.preventDefault();
    }

    $(document).on("click", click_elem, show_text);
}

$(document).ready(function () {

    // Initialisations
    act_confirm();
    tooltip_init();
    popover_init();
    truncate_toggle('.more_less_btn', '#truncated', '#not_truncated', '#descr_content');


    // Login Box
    var width;
    var count = $('.login-form .wrapper-login-option').children().length;


    if (count == '2') {
        $('.login-form .wrapper-login-option > .login-option button.option-btn-login:not([data-target="2"])').removeClass('hide');
    } else if (count == '3') {
        $('.login-form .wrapper-login-option > .login-option button.option-btn-login').removeClass('hide');
    }

    $('.login-option button.option-btn-login').on('click', function () {
        width = $('.login-form').outerWidth();
        var tar = $(this).attr('data-target');
        tar = -(tar * 100);
        $(this).parents('.wrapper-login-option').css('margin-left', tar + '%')
    });

    // Action Bar Placeholder
    var action_bar = $('.action_bar').length;
    if (!action_bar) {
        $('div.title-row').css('margin-bottom', '24px');
    }

    $('body').on('click', 'a.disabled', function (e) {
        e.preventDefault();
    });
    $('body').on('click', 'a.back_btn', function (e) {
        e.preventDefault();
        javascript:window.history.back();
    });
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

    // Module toggle button
    $('#module_toggle').on('click', function (e) {
        e.preventDefault();
        var url = $(this).attr('href');
        $(this).removeAttr('href').find('span')
            .removeClass('fa-minus-square fa-check-square tiny-icon-red tiny-icon-green')
            .addClass('fa-spinner fa-spin');
        $.post(url,
            { hide: $(this).data('state') },
            function () { location.reload(); });
    });
    
    // External Apps activate/deactivate button
    $('.extapp-status').on('click', function () {
        var url = window.location.href;
        var button = $(this).children('i');
        var state = button.hasClass('fa-toggle-on') ? "fa-toggle-on" : "fa-toggle-off";
        var appName = button.parent('button').attr('data-app');
        
        button.removeClass(state).addClass('fa-spinner fa-spin');
        
        $.post( url,
                {state: state,
                 appName: appName},
                function (data) {
                    var newState = (data === "0")? "fa-toggle-off":"fa-toggle-on";                    
                    button.removeClass('fa-spinner fa-spin').addClass(newState);
                    btnColorState = button.parent('button').hasClass('btn-success')?'btn-success':'btn-danger';
                    newBtnColorState = button.parent('button').hasClass('btn-success')?'btn-danger':'btn-success';
                    button.parent('button').removeClass(btnColorState).addClass(newBtnColorState);
                });
    });

    // Leftnav - rotate Category Menu Item icon
    if ($(".collapse.in").length > 0) { //when page first loads the show.bs.collapse event is not triggered
        $(".collapse.in").prev("a").find("span.fa").addClass("fa-rotate-90");
    }
    $('.panel-collapse').on('show.bs.collapse', function () {
        $(this).prev("a").find("span.fa").addClass("fa-rotate-90");
    });
    $('.panel-collapse').on('hide.bs.collapse', function () {
        $(this).prev("a").find("span.fa").removeClass("fa-rotate-90");
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

    $("#scrollToTop span").on('click', function () {
        scrollToTop("html, body", 500);
    });

    $('.panel-collapse').on('shown.bs.collapse', function () {
        //scrollToTop($(this).prev('a'),500);  // Uncomment this if you want to make anchor the Parent Menu Item
        scrollToTop("html, body", 500);
    });


    // Actions needed to be done after full DOM elements downloaded
    $(window).load(function ()
    {
        var initialHeight;
        var windowHeight = $(window).height();
        var contentHeight = $("#Frame").height();



        $("#innerpanel-container").slimScroll({height: '215px'});
        $("#collapseMessages ul.sidebar-mymessages").slimScroll({height: '215px'});

        // Initialisation of Main Content height
        var margin_offset = 131;
        var initialHeight = ((contentHeight > windowHeight) ? contentHeight : windowHeight) - margin_offset;
        $("#Frame").css({"min-height": initialHeight});
        $("#sidebar").css({"min-height": initialHeight + margin_offset});


        // Right Side toggle menu animation
        $('#toggle-sidebar').click(function () {
            var inOut = $("#sidebar").hasClass("in") ? "-18.5em" : "-2em";

            if ($("#leftnav").hasClass("float-menu-in")) {
                $("#leftnav").animate({
                    "left": "-225"
                }, {duration: 150, start: function () {
                        $(this).removeClass("float-menu-in");
                    }});
            }

            if (!$("#sidebar").hasClass("in")) {
                var courseIDs = [];
                $(".lesson-notifications").each(function () {
                    courseIDs.push($(this).data('id'));
                });
                $.ajax({
                    type: "GET",
                    url: sidebarConfig.messagesLink,
                    dataType: "json",
                    data: {courseIDs: courseIDs},
                    success: function (data) {
                        var objData = data.messages;
                        var $jqObjData = $(objData);
                        var noMsgs = $jqObjData.filter("li.no-messages").length;
                        if (!(noMsgs > 0)) {
                            var numMsgs = $jqObjData.filter("li").length;
                            var numMsgsString = " (" + numMsgs + ") ";
                            $("span.num-msgs").html(numMsgsString);
                        }
                        $("ul.sidebar-mymessages").html(data.messages);
                        $(".lesson-notifications").each(function () {
                            var id = $(this).data('id');
                            if (data.notifications[id]) {
                                $(this).html(data.notifications[id]);
                                $(this).closest('.panel').find('span.lesson-title-caret').removeClass('fa-caret-down').addClass('fa-bell alert-info').attr('rel', 'tooltip').attr('title', data.langNotificationsExist);
                            }
                        });
                        tooltip_init();
                    }
                });
            }

            $("#save_note").on("click", function () {
                var note_title = $("#title-note").val();
                var note_text = $("#text-note").val();

                $(".spinner-div").removeClass("hidden");

                if (note_title === '' || note_text === '') {
                    $(".spinner-div p").text(sidebarConfig.note_fail_messge);
                    $(".spinner-div img").toggleClass("hidden");
                    $(".spinner-div p").toggleClass("hidden");
                    setTimeout(function () {
                        $(".spinner-div").addClass("hidden");
                        $(".spinner-div img").toggleClass("hidden");
                        $(".spinner-div p").toggleClass("hidden");
                    }, 2500);
                } else {
                    note_text = $('<p/>').text(note_text).wrap('<div/>').parent().html();
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
                }
            });

            $("#sidebar").animate(
                    {"right": inOut}, {duration: 150, easing: "linear",
                start: function () {
                    if (!$("#sidebar").hasClass("in"))
                        $("#sidebar-container").css({"display": "block"});
                },
                complete: function () {
                    $("#toggle-sidebar").toggleClass("toggle-active");
                    if ($("#sidebar").hasClass("in")) {
                        $("#sidebar-container").css({"display": "none"});
                    }
                    $("#sidebar").toggleClass("in");
                }
            });
        });

    });
});
