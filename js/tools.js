
// javascript menu swapper

function move(fbox_id, tbox_id) {
    var arrFbox = new Array();
    var arrTbox = new Array();
    var arrLookup = new Array();
    var i;
    var fbox = document.getElementById(fbox_id);
    var tbox = document.getElementById(tbox_id);
    for (i = 0; i < tbox.options.length; i++) {
        arrLookup[tbox.options[i].text] = tbox.options[i].value;
        arrTbox[i] = tbox.options[i].text;
    }
    var fLength = 0;
    var tLength = arrTbox.length;
    for(i = 0; i < fbox.options.length; i++) {
        arrLookup[fbox.options[i].text] = fbox.options[i].value;
        if (fbox.options[i].selected && fbox.options[i].value != "") {
            arrTbox[tLength] = fbox.options[i].text;
            tLength++;
        } else {
            arrFbox[fLength] = fbox.options[i].text;
            fLength++;
        }
    }
    arrFbox.sort();
    arrTbox.sort();
    fbox.length = 0;
    tbox.length = 0;
    var c;
    for(c = 0; c < arrFbox.length; c++) {
        var no = new Option();
        no.value = arrLookup[arrFbox[c]];
        no.text = arrFbox[c];
        fbox[c] = no;
    }
    for(c = 0; c < arrTbox.length; c++) {
        var no = new Option();
        no.value = arrLookup[arrTbox[c]];
        no.text = arrTbox[c];
        tbox[c] = no;
    }
}

function selectAll(cbList_id,bSelect) {
  var cbList = document.getElementById(cbList_id);
  for (var i=0; i<cbList.length; i++)
    cbList[i].selected = cbList[i].checked = bSelect
}


function checkrequired(which, entry) {
    var pass=true;
    if (document.images) {
        for (i=0;i<which.length;i++) {
            var tempobj = which.elements[i];
            if (tempobj.name == entry) {
                if (tempobj.type == 'text' && tempobj.value == '') {
                    pass=false;
                    break;
                }
            }
        }
    }
    if (!pass) {
        alert(langEmptyGroupName);
        return false;
    } else {
        return true;
    }
}


function confirmation(message) {
    if (confirm(message)) {
        return true;
    } else {
        return false;
    }
}


function add_bookmark() {
	// add a "rel" attrib if Opera 7+
	if(window.opera) {
		if ($("a.jqbookmark").attr("rel") != ""){ // don't overwrite the rel attrib if already set
			$("a.jqbookmark").attr("rel","sidebar");
		}
	}

	$("a.jqbookmark").click(function(event){
		event.preventDefault(); // prevent the anchor tag from sending the user off to the link
		var url = this.href;
		var title = this.title;

		if (window.sidebar) { // Mozilla Firefox Bookmark
			window.sidebar.addPanel(title, url,"");
		} else if( window.external ) { // IE Favorite
			window.external.AddFavorite( url, title);
		} else if(window.opera) { // Opera 7+
			return false; // do nothing - the rel="sidebar" should do the trick
		} else { // for Safari, Konq etc - browsers who do not support bookmarking scripts (that i could find anyway)
			 alert('Unfortunately, this browser does not support the requested action,'
			 + ' please bookmark this page manually.');
		}

	});
}

function control_deactivate_off() {
        $("#unsubscontrols input").attr('disabled', 'disabled');
        $("#unsubscontrols").addClass('invisible');
}

// Deactivate course e-mail subscription controls
function control_deactivate() {
        control_deactivate_off();
        $("#unsub").change(function () {
                checkState = $(this).is(':checked');
                if (checkState) {
                        $("#unsubscontrols input").removeAttr('disabled');
                        $("#unsubscontrols").removeClass('invisible');
                } else {
                        control_deactivate_off();
                }
        });
}

// Activate/deactivate course selection controls in modules/admin/userlogs.php
function deactivate_course_log_controls() {
        $(".course select").attr('disabled', 'disabled');
        $(".course").addClass('invisible');
}

function activate_course_log_controls() {
        $(".course select").removeAttr('disabled');
        $(".course").removeClass('invisible');
}

function course_log_controls_init() {
        var select = $('[name=logtype]');
        select.change(function () {
                if (platform_actions.indexOf($(this).val()) >= 0) {
                        deactivate_course_log_controls();
                } else {
                        activate_course_log_controls();
                }
        })
}


// Course registration UI

function course_checkbox_disabled(id, state)
{
        $('input[type=checkbox][value='+id+']').prop('disabled', state);
}

function course_list_init()
{
        $.course_closed = [];
        var $dialog = $('<div></div>')
                        .html(lang.reregisterImpossible)
                        .dialog({
                                autoOpen: false,
                                title: lang.unregCourse,
                                buttons: [
                                    { text: lang.unCourse,
                                      click: function() {
                                                      $.trigger_checkbox
                                                       .change(course_list_handler)
                                                       .prop('checked', false).change();
                                                      $(this).dialog('close');
                                             }
                                    },
                                    { text: lang.cancel,
                                      click: function() { $(this).dialog('close'); }
                                    } ]
                        });
        $('input[type=submit]').remove();
        $('input[type=checkbox]').each(function () {
                var cid = $(this).val();
                $.course_closed[cid] = $(this).hasClass('reg_closed');
        }).not('.reg_closed').change(course_list_handler);
        $('input.reg_closed[type=checkbox]:checked').click(function() {
                $.trigger_checkbox = $(this);
                $dialog.dialog('open');
                return false;
        });
        $('input[type=password][value=]').each(function () {
                var id = $(this).attr('name').replace('pass', '');
                course_checkbox_disabled(id, true);
                $(this).on('keypress change paste', function () {
                        course_checkbox_disabled(id, false);
                });
                $(this).keydown(function(event) {
                        if (event.which == 13) {
                                if ($(this).val() != '') {
                                        $('input[type=checkbox][value='+id+']:not(:checked)')
                                                .prop('checked', true).change();
                                }
                                return false;
                        }
                });

        });
}

function course_list_handler()
{
        var cid = $(this).attr('value');
        var td = $(this).parent().next();
        $('#res'+cid).remove();
        if (!$('#ind'+cid).length) {
                td.append(' <img id="ind'+cid+'" src="'+themeimg+'/ajax_loader.gif" alt="">');
        }
        var submit_info = { cid: cid, state: $(this).prop('checked') };
        var passfield = $('input[name=pass'+cid+']');
        if (passfield.length) {
                submit_info.password = passfield.val();
        }
        $.post('course_submit.php',
               submit_info,
               function (result) {
                       $('#ind'+cid).remove();
                       if (result == 'registered') {
                               td.append(' <img id="res'+cid+'" src="'+themeimg+'/tick.png" alt="">');                               
                       } else {
                               $('input[type=checkbox][value='+cid+']').prop('checked', false);
                               if ($.course_closed[cid]) {
                                       course_checkbox_disabled(cid, true);
                               }
                               if (passfield.length && result == 'unauthorized') {
                                       $('<div></div>').html(lang.invalidCode)
                                                       .dialog({
                                                                buttons: [ { text: lang.close,
                                                                             click: function() {
                                                                                     $(this).dialog('close'); }
                                                                         } ]
                                                       });

                               }
                       }
               },
               'text');
}

// User profile UI

function profile_init()
{
        $(document).on('click', '#delete', function() {
                if (confirm(lang.confirmDelete)) {
                        var tr = $(this).closest('tr');
                        tr.children('th').html(lang.addPicture);
                        tr.find('span').remove();
                        $.post('profile.php', { delimage: true });
                }
        });
}

function toggleMenu(mid){
	menuObj = document.getElementById(mid);
	
	if(menuObj.style.display=='none')
		menuObj.style.display='block';
	else
		menuObj.style.display='none';
}

function exercise_enter_handler() {
    /* 
     * Make an array of possible inputs and everytime
     * ENTER is pressed, iterate to next availiable input
     * until you reach submit, when the form will be submited.
     */
    var inputs = $(this).find('.exercise input:not([type=hidden]), .exercise select');
    inputs.pivot = 0;
    $(inputs).keydown(function(event) {
        if(event.which == 13) {
            event.preventDefault();
            if($(this)[0].type != 'submit') {
                ++inputs.pivot;
                /*
                 * In order to avoid just going to next input, 
                 * iterate until you reach the next set of inputs.
                 */
                while(inputs[inputs.pivot].name == inputs[inputs.pivot-1].name)
                    inputs.pivot++;
                inputs[inputs.pivot].focus();
            } else {
                //Maybe some confirmation popup here
                $('.exercise').submit();
            }
        }
    });
    /*
     * When clicking on an input, pivot must point 
     * to the input clicked so when ENTER is pressed
     * will move to correct next input.
     */
    $(inputs).click(function() {
        pivot2 = 0;
        while(inputs[pivot2].name != $(this)[0].name)
            pivot2++;
        inputs[pivot2].select();
        inputs.pivot = pivot2;
        ck = $(':checked');
        answers = [];
        for(i = 0; i < ck.length; i++) {
        	ans = {
        		name : ck[i].name,
        		value : ck[i].value
        	}
        	answers.push(ans);
        }
        localStorage["answers"] = JSON.stringify(answers);
    });
}

function countdown(timer, callback) {
    int = setInterval(function() {
    	timer.text(secondsToHms(timer.time--));
        if (timer.time + 1 == 0) {
            clearInterval(int);
            // 600ms - width animation time
            callback && setTimeout(callback, 600);
        }
    }, 1000);
}

function secondsToHms(d) {
    d = Number(d);
    var h = Math.floor(d / 3600);
    var m = Math.floor(d % 3600 / 60);
    var s = Math.floor(d % 3600 % 60);
    return ((h > 0 ? h + ":" : "") + (m > 0 ? (h > 0 && m < 10 ? "0" : "") + m + ":" : "0:") + (s < 10 ? "0" : "") + s);
}
// Questionnaire / Poll

function poll_init() {
    var deleteIcon = $('#deleteIcon').html();
    var moveIcon = $('#moveIcon').html();
    $('input[type=submit][value="+"]').on('click', function (event) {
        var qid = this.name.substring(11); // name is "MoreAnswersNN", extract NN
        $(this).closest('tr').next().find('li').last()
            .before('<li><input type="text" name="answer' +
                qid + '[]" value="" size="50">' + deleteIcon + moveIcon +'</li>');
        event.preventDefault();
    });
    $('.poll_answers').sortable({items: "li:not(#unknown)"});
    $('.poll_answers li:not(#unknown)').append(deleteIcon).css('cursor', 'move').append(moveIcon);
    $('.poll_answers li:not(#unknown)').find('#moveIconImg').css('cursor', 'move');
    $('.poll_answers img').not('#moveIconImg').css('cursor', 'pointer').on('click', function () {
        $(this).closest('li').remove();
    });
    $('.poll_toolbar img').css('cursor', 'pointer').on('click', function () {
        var icon = icon_src_to_name($(this).attr('src'));
        var cur_tinymce_id = $(this).closest('td').next().find('textarea').attr('id');
        if (cur_tinymce_id) {
            tinyMCE.execCommand( 'mceRemoveControl', false, cur_tinymce_id );
        }
        var cur = $(this).closest('.poll_item');
        if (icon == 'up') {
            var prev = cur.prevAll('.poll_item');
            if (prev.length) {
                var prev_tinymce_id = prev.find('textarea').attr('id');
                if (prev_tinymce_id) {
                    tinyMCE.execCommand( 'mceRemoveControl', false, prev_tinymce_id );
                }                
                prev = prev.eq(0);
                var prev_contents = prev.clone(true);
                var cur_contents = cur.clone(true);
                cur.replaceWith(prev_contents);
                prev.replaceWith(cur_contents);
                if (cur_tinymce_id) {
                    tinyMCE.execCommand( 'mceAddControl', true, cur_tinymce_id );
                }
                if (prev_tinymce_id) {
                    tinyMCE.execCommand( 'mceAddControl', true, prev_tinymce_id );
                }                   
            }
        } else if (icon == 'down') {
            var next = cur.nextAll('.poll_item');
            if (next.length) {
                var next_tinymce_id = next.find('textarea').attr('id');
                if (next_tinymce_id) {
                    tinyMCE.execCommand( 'mceRemoveControl', false, next_tinymce_id );
                }                  
                next = next.eq(0);
                var next_contents = next.clone(true);
                var cur_contents = cur.clone(true);
                cur.replaceWith(next_contents);
                next.replaceWith(cur_contents);
                if (cur_tinymce_id) {
                    tinyMCE.execCommand( 'mceAddControl', true, cur_tinymce_id );
                }
                if (next_tinymce_id) {
                    tinyMCE.execCommand( 'mceAddControl', true, next_tinymce_id );
                }                    
            }
        } else if (icon == 'delete') {
            cur.prev('hr').remove();
            cur.prev('br').remove();
            cur.remove();
        }
    });
}
function icon_src_to_name(src) {
    var spl = src.split(/[\/.]/);
    return spl[spl.length - 2];
}