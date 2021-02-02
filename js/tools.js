
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
        alert("Αφήσατε κάποιο από τα υποχρεωτικά πεδία κενό!");
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
        });
}


// Course registration UI

function course_checkbox_disabled(id, state)
{
        $('input[type=checkbox][value='+id+']').prop('disabled', state);
}

function course_list_init() {
    $.course_closed = [];

    var dialog = $('<div class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modal-label" aria-hidden="true"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h4 class="modal-title" id="modal-label"></h4></div><div class="modal-body"></div><div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal"></button><button type="button" class="btn btn-primary"></button></div></div></div></div>');
    $('.modal-body', dialog).html(lang.reregisterImpossible);
    $('.modal-title', dialog).html(lang.unregCourse);
    $('.btn-default', dialog).html(lang.cancel);
    $('.btn-primary', dialog).html(lang.unCourse);
    $('.btn-primary', dialog).click(function() {
        $.trigger_checkbox
            .change(course_list_handler)
            .prop('checked', false).change();
        dialog.modal('hide');
    });

    $('input[type=submit]').remove();

    $('input[type=checkbox]').each(function () {
        var cid = $(this).val();
        $.course_closed[cid] = $(this).hasClass('reg_closed');
    })
        .not('.reg_closed')
        .change(course_list_handler);

    $('input.reg_closed[type=checkbox]:checked').click(function() {
        $.trigger_checkbox = $(this);
        dialog.modal("show");
        return false;
    });

    $('input[type=password]').each(function () {
        var id = $(this).attr('name').replace('pass', '');
        if ($(this).val() === '') {
            course_checkbox_disabled(id, true);
        }
        $(this).on('keypress change paste', function () {
            course_checkbox_disabled(id, false);
        });
        $(this).keydown(function(event) {
            if (event.which === 13) {
                if ($(this).val() !== '') {
                    $('input[type=checkbox][value='+id+']:not(:checked)')
                        .prop('checked', true)
                        .change();
                }
                return false;
            }
        });
    });
}

function course_list_handler() {
    var cid = $(this).attr('value');
    var td = $(this).parent().next();
    $('#res' + cid).remove();
    if (!$('#ind' + cid).length) {
        td.append(' <img id="ind' + cid + '" src="' + themeimg + '/ajax_loader.gif" alt="">');
    }
    var submit_info = {
      cid: cid,
      state: $(this).prop('checked'),
      token: $('input[name=token]').val() };
    var passfield = $('input[name=pass' + cid + ']');
    if (passfield.length) {
        submit_info.password = passfield.val();
    }
    $.post(urlAppend + 'modules/auth/course_submit.php',
        submit_info,
        function (result) {
            var title_span = $('#cid'+cid);
            $('#ind' + cid).remove();
            if (result === 'registered') {
                td.append(' <img id="res' + cid + '" src="' + themeimg + '/tick.png" alt="">');
                title_span.html($('<a>', {
                    href: urlAppend + 'courses/' + courses[cid][0] + '/',
                    text: title_span.text() }));

            } else {
                if (result === 'prereqsnotcomplete') {
                    alert(lang.prereqsNotComplete);
                }
                $('input[type=checkbox][value=' + cid + ']').prop('checked', false);
                if (courses[cid][1] != 2) {
                    title_span.text(title_span.text());
                }
                if ($.course_closed[cid]) {
                    course_checkbox_disabled(cid, true);
                }
                if (passfield.length && result === 'unauthorized') {
                    var dialog = $('<div class="modal fade" tabindex="-1" role="dialog" aria-hidden="true"><div class="modal-dialog"><div class="modal-content"><div class="modal-body"></div><div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal"></button></div></div></div></div>');
                    $('.modal-body', dialog).html(lang.invalidCode);
                    $('.btn-default', dialog).html(lang.close);
                    dialog.modal("show");
                }
            }
        },
        'text'
    );
}

// User profile UI

function profile_init() {
    $(document).on('click', '#delete', function() {
        if (confirm(lang.confirmDelete)) {
            var delBtn = $(this).closest("div");
            delBtn.find('span').remove();
            $("li#profile_menu_dropdown img").attr("src", "/openeclass/template/default/img/default_32.jpg");
            $.post('profile.php', { delimage: true });
        }
    });
}

function toggleMenu(mid) {
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
        $(inputs[pivot2]).select();
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

function exercise_init_countdown(params) {
    var exerciseId = params.exerciseId,
        eurid = params.eurid;

    // Don't submit question on enter keypress in input field
    $('.exercise input').keydown(function(event) {
            if (event.which === 13) {
                event.preventDefault();
                $(this).next('input').focus();
            }
        });

    var continueSubmit = function () {
        $(window).off();
        document.cookie = 'inExercise=; expires=Thu, 01 Jan 1970 00:00:01 GMT;';
        cancelCheck = true;
        $('.exercise').off().submit();
    }

    $(window).on('beforeunload', function(e) {
        var date = new Date();
        date.setTime(date.getTime() + 30 * 1000);
        var expires = '; expires=' + date.toGMTString();
        document.cookie = 'inExercise=' + exerciseId + expires;
        e.preventDefault();
        e.returnValue = params.warning;
        return params.warning;
    });
    $(window).on('unload', function() {
        $.ajax({
            type: 'POST',
            url: '',
            data: { action: 'endExerciseNoSubmit', eid: exerciseId, eurid: eurid },
            async: false
        });
    });

    var timer = $('#progresstime');
    timer.time = timer.text();
    timer.text(secondsToHms(timer.time--));
    var hidden_timer = $('#secsRemaining');
    hidden_timer.time = timer.time;
    setInterval(function() {
        hidden_timer.val(hidden_timer.time--);
        if (hidden_timer.time + 1 == 0) {
            clearInterval();
        }
    }, 1000);
    countdown(timer, function() {
        $('<input type="hidden" name="autoSubmit" value="true">').appendTo('.exercise');
        continueSubmit();
    });
    setInterval(function() {
        $.ajax({
          type: 'POST',
          data: { action: 'refreshSession'}
        });
    }, params.refreshTime);

    // Keep track of which questions have been answered
    var cancelCheck = false;
    var answered = {};
    if (params.answeredIds) {
        params.answeredIds.forEach(function (id) {
            answered[id] = true;
        });
    }

    var questionId = function (el) {
        return el.closest('.qPanel')[0].id.replace('qPanel', '');
    }
    var exerciseCheckUnanswered = function() {
        var qids = $('.qPanel').map(function () {
            return this.id.replace('qPanel', '');
        }).get();
        if (params.unansweredIds) {
            qids = qids.concat(params.unansweredIds.filter(function (id) {
                return qids.indexOf(id) < 0;
            }));
        }
        $('.qPanel :input').change(function () {
            var el = $(this);
            var id = questionId(el);
            answered[id] = true;
            if (el.attr('type') == 'text') {
                // Text inputs are fill-in-blanks questions:
                // if any remain empty, question remains unanswered
                el.siblings('input').each(function () {
                    if (this.value == '') {
                        answered[id] = false;
                    }
                });
            } else if (el.is('select')) {
                // Selects are matching questions:
                // if any remain unset, question remains unanswered
                el.closest('.qPanel').find('select').each(function () {
                    if (this.value == '0') {
                        answered[id] = false;
                    }
                });
            }
        });
        $('input[name=q_id], input.navbutton').click(function () {
            continueSubmit();
        });
        $('.exercise').submit(function (e) {
            var unansweredCount = 0, firstUnanswered;

            if ('tinymce' in window) {
                // Check for empty tinyMCE instances
                tinymce.get().forEach(function (e) {
                    if (e.getContent({format: 'text'}).trim() != '') {
                        var id = questionId($(e.contentAreaContainer));
                        answered[id] = true;
                    }
                });
            }
            qids.forEach(function (id) {
                if (!answered[id]) {
                    if (!firstUnanswered) {
                        firstUnanswered = id;
                    }
                    unansweredCount++;
                }
            });
            e.preventDefault();
            var message, title;

            if (unansweredCount === 0) {
                title = params.finalSubmit;
                message = params.finalSubmitWarn;
            } else {
                title = params.unansweredQuestions;
                message = ((unansweredCount === 1)?
                           message = params.oneUnanswered:
                           params.manyUnanswered.replace('_', unansweredCount)) +
                          ' ' + params.question;
            }
            $.unblockUI();
            bootbox.dialog({
                title: title,
                message:
                    '<div class="row">' +
                      '<div class="col-md-12">' +
                        '<h4>' + title + '</h4>' +
                        '<p>' + message + '</p>' +
                      '</div>' +
                    '</div>',
                buttons: {
                    goBack: {
                        label: params.goBack,
                        className: 'btn-success',
                        callback: function () {
                            var moveTo = $('#qPanel' + firstUnanswered);
                            if (moveTo.length) {
                                $('html').animate({
                                    scrollTop: moveTo.offset().top + 'px'
                                }, 'fast');
                            }
                        }
                    },
                    submit: {
                        label: params.submit,
                        className: 'btn-warning',
                        callback: function () {
                            $('<input type="hidden" name="buttonFinish" value="true">').appendTo('.exercise');
                            continueSubmit();
                        }
                    },
                }
            });
        });
    }
    var checkUnanswered = $('.qPanel').length >= 1;
    $('.btn[name=buttonSave]').click(continueSubmit);
    $('#cancelButton').click(function (e) {
      e.preventDefault();
      bootbox.confirm({
        message: params.cancelMessage,
        buttons: {
          confirm: {
            label: params.cancelAttempt,
            className: 'btn-danger'
          },
          cancel: {
            label: params.goBack,
            className: 'btn-default'
          }
        },
        callback: function(result) {
          if (result) {
            $('<input type="hidden" name="buttonCancel" value="true">').appendTo('.exercise');
            continueSubmit();
          }
        }
      });
    });
    if (checkUnanswered) {
        exerciseCheckUnanswered();
    } else {
        $('.exercise').submit(function () {
            $(window).off();
            document.cookie = 'inExercise=; expires=Thu, 01 Jan 1970 00:00:01 GMT;';
        });
    }
}


/**
 * @brief update question number button color (type = 'matching', 'fill-in-blanks')
 * @param question_number
 * @param question_id
 */
function questionUpdateListener(question_number, question_id) {

    var button_id = "q_num" + question_number;
    var qpanel_id = "qPanel" + question_id;
    var check_id = "qCheck" + question_number; // `check` icon

    $(function() {
        $.ajax({
            url: 'exercise_submit.php',
            success: function() {
                var el = $("#"+qpanel_id+" :input");
                answered = true;
                if (el.attr('type') == 'text') {
                    // Text inputs are fill-in-blanks questions:
                    // if any remain empty, question remains unanswered
                    el.siblings('input').each(function () {
                        if (this.value == '') {
                            answered = false;
                        }
                    });
                } else if (el.is('select')) {
                    // Selects are matching questions:
                    // if any remain unset, question remains unanswered
                    el.closest('.qPanel').find('select').each(function () {
                        if (this.value == '0') {
                            answered = false;
                        }
                    });
                }
                if (answered === true) {
                    $("#"+button_id).removeClass('btn-default').addClass('btn-info');
                    $("#"+check_id).addClass('fa fa-check');
                }
            }
        });
   });
}


/**
 * @brief update question number button color (type = 'multiple choice', 'true/false')
 * @param question_number
 */
function updateQuestionNavButton(question_number) {

    var button_id = "q_num" + question_number; // button
    var check_id = "qCheck" + question_number; // `check` icon
    $(function() {
        $.ajax({
            url: 'exercise_submit.php',
            success: function() {
                $("#"+button_id).removeClass('btn-default').addClass('btn-info');
                $("#"+check_id).addClass('fa fa-check');
            }
        });
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
    delete_init();
    $('input[type=submit][value="+"]').on('click', function (e) {
        e.preventDefault();
        var last_form_group = $(this).closest('div.form-group').siblings('.form-group:last');
        last_form_group.before("<div class='form-group answer-group'><div class='col-xs-11'><input class='form-control' type='text' name='answers[]' value=''></div><div class='col-xs-1 form-control-static'><a href='#' style='cursor: pointer;' class='del_btn'><i class='fa fa-times'></i></a></div></div>").next().find('input').removeAttr('value');
        delete_init();
    });
}
function delete_init(){
    $('.form-group a.del_btn').css('cursor', 'pointer').on('click', function (e) {
        e.preventDefault();
        $(this).closest('.form-group').remove();
    });
}
function icon_src_to_name(src) {
    var spl = src.split(/[\/.]/);
    return spl[spl.length - 2];
}

function checkFileSize(input, maxSize) {
    var file = input[0].files[0];
    if (file && (file.size > maxSize || file.fileSize > maxSize)) {
        alert(langMaxFileSizeExceeded);
        return false;
    }
    return true;
}
function enableCheckFileSize() {
    var input = $('input[type=file]');
    var form = input.closest('form');
    var maxSize = form.find('input[name=MAX_FILE_SIZE]').val();
    form.on('submit', function () {
        return checkFileSize(input, maxSize);
    });
}
