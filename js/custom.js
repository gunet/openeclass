
$(document).ready(function(){

    $(".login-form-submit").on('click',function(){
        localStorage.clear();
    })

    /////////////////////////change Gui of selected language////////////////////////

    $(".GreekButton").on('click',function(){
        localStorage.setItem('GreekLanguage',1);
        localStorage.setItem('EnglishLanguage',0);
    })
    $(".EnglishButton").on('click',function(){
        localStorage.setItem('EnglishLanguage',1);
        localStorage.setItem('GreekLanguage',0);
    })
    if(localStorage.getItem('GreekLanguage') == 1){
        $(".GreekButton").css('background-color','blue');
        $(".GreekButton").css('color','white');
        $(".EnglishButton").css('background-color','transparent');
        $(".EnglishButton").css('color','white');
    }
    if(localStorage.getItem('EnglishLanguage') == 1){
        $(".EnglishButton").css('background-color','blue');
        $(".EnglishButton").css('color','white');
        $(".GreekButton").css('background-color','transparent');
        $(".GreekButton").css('color','white');
    }
    //////////////////////////////////////////////////////////////////////////////

    $('#getTopicButton').on('click',function(){
        $('#getTopicModal').modal("show");
    });

    var action_bar = $('.action_bar').length;
    if (!action_bar) {
        $('div.title-row').css('margin-bottom', '24px');
    }

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
	})

	$('.user-details-trigger a').on('click',function()
	{
		var headertext = $('.user-details > h2').text();
		$('.user-details > h2').text( headertext == "ΣΥΝΟΠΤΙΚΟ ΠΡΟΦΙΛ" ? "ΠΡΟΦΙΛ ΧΡΗΣΤΗ" : "ΣΥΝΟΠΤΙΚΟ ΠΡΟΦΙΛ" );
		var text = $('.user-details-trigger a').text();
		$(this).text( text == "ΠΕΡΙΣΣΟΤΕΡΕΣ ΠΛΗΡΟΦΟΡΙΕΣ" ? "ΣΥΝΟΠΤΙΚΟ ΠΡΟΦΙΛ" : "ΠΕΡΙΣΣΟΤΕΡΕΣ ΠΛΗΡΟΦΟΡΙΕΣ");
		$('.user-details').toggleClass('expanded');
	})

	$('.user-menu-trigger a').on('click',function(){
		var text = $('.user-menu-trigger a').text();
		$(this).text( text == "ΠΕΡΙΣΣΟΤΕΡΕΣ ΕΠΙΛΟΓΕΣ" ? "ΛΙΓΟΤΕΡΕΣ ΕΠΙΛΟΓΕΣ" : "ΠΕΡΙΣΣΟΤΕΡΕΣ ΕΠΙΛΟΓΕΣ");
		$('.user-menu').toggleClass('expanded');
	})


    /////////////////////////////// play testinomonials ////////////////////////////////////////////////////////////////////////

  	$('.testimonials').slick({
		autoplay:true,
		autoplaySpeed:1500,
		centerMode: true,
		centerPadding: '25vw',
		slidesToShow: 1,
		responsive: [{
			breakpoint: 1024,
			settings: { centerPadding: '15vw', }
		}]
	});

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	var counter_button_collapse_main_section = 0;
	$('.button_collapse_main_section').on('click',function(){
		counter_button_collapse_main_section++;
		if(counter_button_collapse_main_section%2==1){
               $('.button_collapse_main_section .fas.fa-chevron-down').hide();
		       $('.button_collapse_main_section .fas.fa-chevron-up').show();
		}else{
			$('.button_collapse_main_section .fas.fa-chevron-down').show();
			$('.button_collapse_main_section .fas.fa-chevron-up').hide();
		}

	})


	var counter_button_collapse_energy_tools = 0;
	$('.container_chevrons .fas.fa-chevron-up').hide();
    $('.container_chevrons').click(function(){
        counter_button_collapse_energy_tools++;
        if(counter_button_collapse_energy_tools%2==1){
            $('.container_chevrons .fas.fa-chevron-down').hide();
            $('.container_chevrons .fas.fa-chevron-up').show();
        }else{
            $('.container_chevrons .fas.fa-chevron-down').show();
            $('.container_chevrons .fas.fa-chevron-up').hide();
        }
    })

	var counter_button_collapse_managment_tools = 0;
	$('.container_chevrons_managment .fas.fa-chevron-up').hide();
    $('.container_chevrons_managment').click(function(){
        counter_button_collapse_managment_tools++;
        if(counter_button_collapse_managment_tools%2==1){
            $('.container_chevrons_managment .fas.fa-chevron-down').hide();
            $('.container_chevrons_managment .fas.fa-chevron-up').show();
        }else{
            $('.container_chevrons_managment .fas.fa-chevron-down').show();
            $('.container_chevrons_managment .fas.fa-chevron-up').hide();
        }
    })

	var disable_counter_button_collapse_managment_tools = 0;
	$('.disable_container_chevrons .fas.fa-chevron-up').hide();
    $('.disable_container_chevrons').click(function(){
        disable_counter_button_collapse_managment_tools++;
        if(disable_counter_button_collapse_managment_tools%2==1){
            $('.disable_container_chevrons .fas.fa-chevron-down').hide();
            $('.disable_container_chevrons .fas.fa-chevron-up').show();
        }else{
            $('.disable_container_chevrons .fas.fa-chevron-down').show();
            $('.disable_container_chevrons .fas.fa-chevron-up').hide();
        }
    })


    /////////////////////////////////////////////////// initial datatable /////////////////////////////////////////////////

	$('#cources-bars-button2').trigger('click');
	$('#courses_table_pag').DataTable();
	$('#mynotes_table').DataTable();
	$('#myannouncements_table').DataTable();
	$('#glossary_table').DataTable();
	$('.user-menu-collapse-more').trigger('click');
	$('.user-menu-collapse-less').show();
	$('.user-menu-collapse-more').hide();

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


    /////////////////////////////////////////////////////// call functions /////////////////////////////////////////////////

	act_confirm();
    tooltip_init();
    popover_init();
	open_document();
    truncate_toggle('.more_less_btn', '#truncated', '#not_truncated', '#descr_content');
    validator_rubric();

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////



});




// portfolio //////////////////////////////////////////////////////////////////////////////////////////////////////////
// portfolio //////////////////////////////////////////////////////////////////////////////////////////////////////////


function switch_user_menu_toggle() {

	if( $('.user-menu-collapse-more').is(":visible") )
	{
		$('.user-menu-collapse-more').hide();
		$('.user-menu-collapse-less').show();
	} else {
		$('.user-menu-collapse-more').show();
		$('.user-menu-collapse-less').hide();
	}
}

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
                    className: "btn-secondary"
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


function open_document(){
    $('.fileModal').click(function (e)
    {
        e.preventDefault();
        var fileURL = $(this).attr('href');
        var downloadURL = $(this).prev('input').val();
        var fileTitle = $(this).attr('title');



        if(downloadURL == null){
            downloadURL = $(this).attr('data-download');
        }

        console.log('the fileURL:'+fileURL);
        console.log('the downloadURL:'+downloadURL);

        // BUTTONS declare
        var bts = {
            download: {
                label: '<span class="fa fa-download"></span> Ληψη',
                className: 'btn-success',
                callback: function (d) {
                    window.location = downloadURL;
                }
            },
            print: {
                label: '<span class="fa fa-print"></span> Εκτυπωση',
                className: 'btn-primary',
                callback: function (d) {
                    var iframe = document.getElementById('fileFrame');
                    iframe.contentWindow.print();
                }
            }
        };
        if (screenfull.enabled) {
            bts.fullscreen = {
                label: '<span class="fa fa-arrows-alt"></span> Πληρης οθονη',
                className: 'btn-primary',
                callback: function() {
                    screenfull.request(document.getElementById('fileFrame'));
                    return false;
                }
            };
        }
        bts.newtab = {
            label: '<span class="fa fa-plus"></span> Νεο παραθυρο',
            className: 'btn-primary',
            callback: function() {
                window.open(fileURL);
                return false;
            }
        };
        bts.cancel = {
            label: 'Ακυρωση',
            className: 'btn-secondary'
        };

        bootbox.dialog({
            size: 'large',
            title: fileTitle,
            message: '<div class="row">'+
                        '<div class="col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">'+
                            '<div class="iframe-container" style="height:500px;"><iframe id="fileFrame" src="'+fileURL+'" style="width:100%; height:500px;"></iframe></div>'+
                        '</div>'+
                    '</div>',
            buttons: bts
        });
    });
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




