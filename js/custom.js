
$(document).ready(function(){

    $(".login-form-submit").on('click',function(){
        localStorage.clear();
    })


    //////////////////////////////////////////////////////////////////////////////

    // var action_bar = $('.action_bar').length;
    // if (!action_bar) {
    //     $('div.title-row').css('margin-bottom', '24px');
    // 

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
});




// portfolio //////////////////////////////////////////////////////////////////////////////////////////////////////////
// portfolio //////////////////////////////////////////////////////////////////////////////////////////////////////////


function switch_user_details_toggle() {
		
	if( $('.user-details-collapse-more').is(":visible") ) 
	{
		$('.user-details-collapse-more').hide();
		$('.user-details-collapse-less').show();
	} else {
		$('.user-details-collapse-more').show();
		$('.user-details-collapse-less').hide();
	}
}

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

		console.log('the message:'+message);
		console.log('the title:'+title);
		console.log('the cancel_text:'+cancel_text);
		console.log('the action_text:'+action_text);
		console.log('the action_btn_class:'+action_btn_class);


        e.preventDefault();
        e.stopPropagation();

        bootbox.dialog({
            message: message,
            title: title,
            buttons: {
                cancel_btn: {
                    label: cancel_text,
                    className: "cancelAdminBtn"
                },
                action_btn: {
                    label: action_text,
                    className: action_btn_class,
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


