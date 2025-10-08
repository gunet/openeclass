
$(document).ready(function(){

    $(".login-form-submit").on('click',function(){
        localStorage.clear();
    })



    // Sticky Header
    var header = $("#bgr-cheat-header");
    var header_icon = header.find("img.eclass-nav-icon");

    $(window).scroll(function () {
        if ($(this).scrollTop() > 0) {
            header.addClass("fixed");
            header_icon.addClass("fixed");
        } else {
            header.removeClass("fixed");
            header_icon.removeClass("fixed");
        }
    });
    // Sticky Header END


    //////////////////////////////////////////////////////////////////////////////

    $(".menu-popover").popover({
      html: true,
      sanitize: false
    });

    $('.view-style > span').on('click',function(){
      $('.view-style > span').removeClass('active');
      if($(this).hasClass('list-style')){
        $(this).parents('.view-style-option').removeClass('grid-style').addClass('list-style');
      } else {
        $(this).parents('.view-style-option').addClass('grid-style').removeClass('list-style');
      }
      $(this).addClass('active');
    });

    $('body').on('click', 'a.disabled', function (e) {
        e.preventDefault();
    });
    $('body').on('click', 'a.back_btn', function (e) {
        e.preventDefault();
        javascript:window.history.back();
    });

    act_confirm();
    tooltip_init();
    popover_init();
    truncate_toggle('.more_less_btn', '#truncated', '#not_truncated', '#descr_content');
    validator_rubric();
    nextAuthedicationMethod();
    about_animation();

    //fix modal appearance
    $('.modal').appendTo("body")

    //startdate , enddate disabled
    if($("#startIdCheckbox, #enableWorkStart, #WorkStart, #exerciseStartDate, #enableStartDate, #start_date_active").is(':checked')){
        $(".add-on1").css('background-color','#ffffff');
    }else{
        $(".add-on1").css('background-color','#E8EDF8');
    }
    $('#startIdCheckbox, #enableWorkStart, #WorkStart, #exerciseStartDate, #enableStartDate, #start_date_active').on('click',function(){
        if($('#startIdCheckbox, #enableWorkStart, #WorkStart, #exerciseStartDate, #enableStartDate, #start_date_active').is(':checked')){
            $('.add-on1').css('background-color','#ffffff');
        }else{
            $('.add-on1').css('background-color','#E8EDF8');
        }
    });

    if($("#endIdCheckbox, #enableWorkEnd, #WorkEnd, #exerciseEndDate, #enableEndDate, #enablecertdeadline, #enableWorkFeedbackRelease, #end_date_active").is(':checked')){
        $(".add-on2").css('background-color','#ffffff');
    }else{
        $(".add-on2").css('background-color','#E8EDF8');
    }
    $('#endIdCheckbox, #enableWorkEnd, #WorkEnd, #exerciseEndDate, #enableEndDate, #enablecertdeadline, #enableWorkFeedbackRelease, #end_date_active').on('click',function(){
        if($('#endIdCheckbox, #enableWorkEnd, #WorkEnd, #exerciseEndDate, #enableEndDate, #enablecertdeadline, #enableWorkFeedbackRelease, #end_date_active').is(':checked')){
            $('.add-on2').css('background-color','#ffffff');
        }else{
            $('.add-on2').css('background-color','#E8EDF8');
        }
    });


    // Regarding the scroll up button
    const btnScrollToTop = document.querySelector(".btnScrollToTop");

    // toggle 'scroll to top' based on scroll position
    window.addEventListener('scroll', e => {
        btnScrollToTop.style.display = window.scrollY > 20 ? 'block' : 'none';
    });

    $('.btnScrollToTop').click(function () {
        $('html, body').animate({scrollTop: 0}, 1500);
        return false;
    });

});


function switch_cources_toggle(id)
{
	if( $('#bars-active').is(":visible") )
	{
		$('#bars-active').hide();
		$('#cources-bars').hide();
		$('#pics-active').css('display','flex');
		$('#cources-pics').show();
	} else {
		$('#pics-active').hide();
		$('#cources-pics').hide();
		$('#bars-active').css('display','flex');
		$('#cources-bars').show();
	}
}

////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////
// popover and tooltip with bootbox



function act_confirm() {
    $('.confirmAction').click(function (e) {

		var message = $(this).attr('data-message');
		var title = $(this).attr('data-title');
		var cancel_text = $(this).attr('data-cancel-txt');
		var action_text = $(this).attr('data-action-txt');
		var action_btn_class = $(this).attr('data-action-class');
		var form = $(this).closest('form').clone().appendTo('body');

        $icon = '';
        if(action_btn_class == 'deleteAdminBtn'){
            $icon = "<div class='icon-modal-default'><i class='fa-regular fa-trash-can fa-xl Accent-200-cl'></i></div>";
        }

        e.preventDefault();
        e.stopPropagation();

        bootbox.dialog({
            closeButton: false,
            message: "<p class='text-center'>"+message+"</p>",
            title: $icon+"<div class='modal-title-default text-center mb-0'>"+title+"</div>",
            buttons: {
                cancel_btn: {
                    label: cancel_text,
                    className: "cancelAdminBtn position-center"
                },
                action_btn: {
                    label: action_text,
                    className: action_btn_class+" "+"position-center",
                    callback: function () {
                        form.submit();
						//location.href = href;
                    }
                }
            }
        });
    });
}

function popover_init() {

    $('[data-bs-toggle="popover"]').on('click',function(e){
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
    $('.menu-popover').popover({html:true}).on('click', togglePopover).on('blur', hidePopover);
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
	$('[data-bs-toggle="tooltip"]').tooltip({container: 'body'});
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


/////////////////////////////////////////////////////////////////////////////////////////////////////
// Validator form in rubric work

function validator_rubric(){
    'use strict'

    // Fetch all the forms we want to apply custom Bootstrap validation styles to
    var forms = document.querySelectorAll('#rubric_form')

    // Loop over them and prevent submission
    Array.prototype.slice.call(forms)
      .forEach(function (form) {
        form.addEventListener('submit', function (event) {
          if (!form.checkValidity()) {
            event.preventDefault()
            event.stopPropagation()
          }

          form.classList.add('was-validated')
        }, false)
      })
}


function nextAuthedicationMethod(){
    const slidePage = document.querySelector(".slide-page");
    const nextPage1 = document.querySelector(".next-page-1");
    const nextPage2 = document.querySelector(".next-page-2");

    const BtnFirst = document.querySelector(".firstNext");

    const prevBtn1 = document.querySelector(".prev-1");
    const prevBtn2 = document.querySelector(".prev-2");

    const nextBtn1 = document.querySelector(".next-1");
    const nextBtn2 = document.querySelector(".next-2");

    const nextBtn3 = document.querySelector(".next-3");

    if(BtnFirst != null){
        BtnFirst.addEventListener("click", function(event){
            event.preventDefault();
            slidePage.style.marginLeft = "-100%";
            nextPage1.style.marginLeft = "0%";
            if(nextPage2 != null){
                nextPage2.style.marginLeft = "100%";
            }

        });
    }

    if(nextBtn1 != null){
        nextBtn1.addEventListener("click", function(event){
            event.preventDefault();
            slidePage.style.marginLeft = "-100%";
            nextPage1.style.marginLeft = "-100%";
            if(nextPage2 != null){
                nextPage2.style.marginLeft = "0%";
            }

        });
    }

    if(prevBtn1 != null){
        prevBtn1.addEventListener("click", function(event){
            event.preventDefault();
            slidePage.style.marginLeft = "0%";
            nextPage1.style.marginLeft = "100%";
            if(nextPage2 != null){
                nextPage2.style.marginLeft = "100%";
            }

        });
    }

    if(nextBtn2 != null){
        nextBtn2.addEventListener("click", function(event){
            event.preventDefault();
            slidePage.style.marginLeft = "-100%";
            nextPage1.style.marginLeft = "-100%";
            if(nextPage2 != null){
                nextPage2.style.marginLeft = "0%";
            }

        });
    }

    if(prevBtn2 != null){
        prevBtn2.addEventListener("click", function(event){
            event.preventDefault();
            slidePage.style.marginLeft = "-100%";
            nextPage1.style.marginLeft = "0%";
            if(nextPage2 != null){
                nextPage2.style.marginLeft = "100%";
            }

        });
    }

    if(nextBtn3 != null){
        nextBtn3.addEventListener("click", function(event){
            event.preventDefault();
            slidePage.style.marginLeft = "0%";
            nextPage1.style.marginLeft = "100%";
            if(nextPage2 != null){
                nextPage2.style.marginLeft = "-100%";
            }

        });
    }

}

// Returns a function to use as a callback for the rendering of notifications
// where type = [lesson|collaboration] to initialize the corresponding display
function build_notification_callback(type) {
  var selector = "." + type + "-notifications";
  return function (settings, json) {
    var courseIDs = [];
    var table = settings.oInstance.api();
    var rowcollection = table.$(selector, {"page": "all"});
    rowcollection.each(function () {
      courseIDs.push($(this).data('id'));
    });
    $.ajax({
      type: "GET",
      url: notificationsCourses.getNotifications,
      dataType: "json",
      data: {courseIDs: courseIDs},
      success: function (data) {
        courseIDs.forEach(function (id) {
          var notifications = data.notifications_courses[id]
          if (notifications) {
            $('#notificationCard' + id).find(selector).html(notifications.notification_content);
            $('#notification' + id).find(selector).html(notifications.notification_content);
            if (notifications.notifications_exist) {
              $('#btnNotificationCards_' + id).removeClass('d-none').show();
              $('#btnNotification_'+id).removeClass('invisible');
            }
          }
        });
      }
    });
  }
}

function initialize_lesson_display (pages) {
    var languageOptions = {
        "sLengthMenu": msg.langDisplay + " _MENU_ " + msg.langResults2,
        "zeroRecords": msg.langNoResult,
        "sInfo": " " + msg.langDisplayed + " _START_ " + msg.langTill + " _END_ " + msg.langFrom2 + " _TOTAL_ " + msg.langTotalResults,
        "sInfoEmpty": " " + msg.langDisplayed + " 0 " + msg.langTill + " 0 " + msg.langFrom2 + " 0 " + msg.langResults2,
        "sInfoFiltered": '',
        "sInfoPostFix": '',
        "sSearch": '',
        "sUrl": '',
        "oPaginate": {
            "sFirst": '&laquo;',
            "sPrevious": '&lsaquo;',
            "sNext": '&rsaquo;',
            "sLast": '&raquo;'
        },
    };
    $('#portfolio_lessons').DataTable({
      "bLengthChange": false,
      "iDisplayLength": pages,
      "bSort": false,
      "fnDrawCallback": function (oSettings) {
        $('#portfolio_lessons_filter label input').attr({
          class: 'form-control input-sm searchCoursePortfolio Neutral-700-cl ms-0 mb-3',
          placeholder: msg.langSearch + '...'
        });
        $('#portfolio_lessons_filter label').attr('aria-label', msg.langSearch);
        $('#portfolio_lessons_filter label').prepend("<span class='sr-only'>" + msg.langSearch + "</span>")
      },
      "initComplete": build_notification_callback('lesson'),
      "dom": "<'all_courses float-end px-0'>frtip",
      "oLanguage": languageOptions,
    });

    $('#portfolio_collaborations').DataTable({
      "bLengthChange": false,
      "iDisplayLength": pages,
      "bSort": false,
      "fnDrawCallback": function (oSettings) {
        $('#portfolio_collaborations_filter label input').attr({
          class: 'form-control input-sm searchCoursePortfolio Neutral-700-cl ms-0 mb-3',
          placeholder: msg.langSearch + '...'
        });
        $('#portfolio_collaborations_filter label').attr('aria-label', msg.langSearch);
        $('#portfolio_collaborations_filter label').prepend("<span class='sr-only'>" + msg.langSearch + "</span>")
      },
      "initComplete": build_notification_callback('collaboration'),
      "dom": msg.dataTablesDomParam,
      "oLanguage": languageOptions,
    });
}


function about_animation(){
    let about = document.querySelectorAll('.about-content');
    window.onscroll = () => {
        about.forEach(sec => {
            let top = window.scrollY;
            let offset = sec.offsetTop - 150;
            let height = sec.offsetHeight;

            if(top >= offset && top < offset + height){
                sec.classList.add('show-animate');
            }else{
                sec.classList.remove('show-animate');
            }
        })
    }
}
