
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
        $('input[type=submit]').remove();
        $('input[type=checkbox]').change(course_list_handler);
        $('input[type=password][value=]').each(function () {
                var id = $(this).attr('name').replace('pass', '');
                course_checkbox_disabled(id, true);
                $(this).on('keypress change paste', function () {
                        course_checkbox_disabled(id, false);
                });
        });
}

function course_list_handler()
{
        var cid = $(this).attr('value');
        var td = $(this).parent().next();
        $('#res'+cid).remove();
        if (!$('#ind'+cid).length) {
                td.append('&nbsp;<img id="ind'+cid+'" src="'+themeimg+'/ajax_loader.gif" alt="">');
        }
        $.post('course_submit.php',
               { cid: cid,
                 state: $(this).prop('checked') },
               function (result) {
                       $('#ind'+cid).remove();
                       if (result == 'registered') {
                               td.append('&nbsp;<img id="res'+cid+'" src="'+themeimg+'/tick.png" alt="">');
                       } else {
                       }
               },
               'text');
}

