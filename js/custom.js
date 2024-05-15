
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

    
    /////////////////////////////////////////////////// initial datatable /////////////////////////////////////////////////

	$('#courses_table_pag').DataTable();

    /////////////////////////////////////////////////////// call functions /////////////////////////////////////////////////

	act_confirm();
    tooltip_init();
    popover_init();
    truncate_toggle('.more_less_btn', '#truncated', '#not_truncated', '#descr_content');
    validator_rubric();
    topFunction();
    nextAuthedicationMethod();
    lesson_notifications();
    collaboration_notifications();


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
            title: $icon+"<h3 class='modal-title-default text-center mb-0'>"+title+"</h3>",
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

// When the user clicks on the button, scroll to the top of the document
function topFunction() {
    document.body.scrollTop = 0;
    document.documentElement.scrollTop = 0;
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

function lesson_notifications(){
    let current_url = document.URL;
    if(current_url.includes('/main/portfolio.php')){
        var courseIDs = [];
        $(".lesson-notifications").each(function () {
            courseIDs.push($(this).data('id'));
        });
        $.ajax({
            type: "GET",
            url: notificationsCourses.getNotifications,
            dataType: "json",
            data: {courseIDs: courseIDs},
            success: function (data) {
                // For cards
                $(".lesson-notifications").each(function () {
                    var id = $(this).data('id');
                    if (data.notifications_courses[id]) {
                        $(this).html(data.notifications_courses[id]['notification_content']);
                        var noexistNotification = document.getElementsByClassName('no_exist_notification_'+id);
                        if (noexistNotification.length > 0) {
                            $('#btnNotificationCards_'+id).css('display','none');
                        }
                    }
                });
                // For datatable
                var portFolioTable = $('#portfolio_lessons').dataTable();
                var rowcollection = portFolioTable.$(".lesson-notifications", {"page": "all"});
                rowcollection.each(function(index,elem){
                    var id = $(elem).data('id');
                    if (data.notifications_courses[id]) {
                        $(elem).html(data.notifications_courses[id]['notification_content']);
                        var noexistNotification = document.getElementsByClassName('no_exist_notification_'+id);
                        if (noexistNotification.length > 0) { 
                            var Table = $('#portfolio_lessons').dataTable();
                            var row = Table.$(".btn-notification-course", {"page": "all"});
                            row.each(function(index,element){ 
                                var id_btn = $(element).attr('id');
                                if(id_btn == 'btnNotification_'+id){
                                    $(element).css('display','none');
                                }
                            });
                        }
                    }
                });
            }
        });
    }
}

function collaboration_notifications(){
    let current_url = document.URL;
    if(current_url.includes('/main/portfolio.php')){
        var collabotationIDs = [];
        $(".collaboration-notifications").each(function () {
            collabotationIDs.push($(this).data('id'));
        });
        $.ajax({
            type: "GET",
            url: notificationsCourses.getNotifications,
            dataType: "json",
            data: {courseIDs: collabotationIDs},
            success: function (data) {
                // For cards
                $(".collaboration-notifications").each(function () {
                    var id = $(this).data('id');
                    if (data.notifications_courses[id]) {
                        $(this).html(data.notifications_courses[id]['notification_content']);
                        var noexistNotification = document.getElementsByClassName('no_exist_notification_'+id);
                        if (noexistNotification.length > 0) {
                            $('#btnNotificationCards_'+id).css('display','none');
                        }
                    }
                });
                // For datatable
                var portFolioTable = $('#portfolio_collaborations').dataTable();
                var rowcollection = portFolioTable.$(".collaboration-notifications", {"page": "all"});
                rowcollection.each(function(index,elem){
                    var id = $(elem).data('id');
                    if (data.notifications_courses[id]) {
                        $(elem).html(data.notifications_courses[id]['notification_content']);
                        var noexistNotification = document.getElementsByClassName('no_exist_notification_'+id);
                        if (noexistNotification.length > 0) { 
                            var Table = $('#portfolio_collaborations').dataTable();
                            var row = Table.$(".btn-notification-collaboration", {"page": "all"});
                            row.each(function(index,element){ 
                                var id_btn = $(element).attr('id');
                                if(id_btn == 'btnNotification_'+id){
                                    $(element).css('display','none');
                                }
                            });
                        }
                    }
                });
            }
        });
    }
}