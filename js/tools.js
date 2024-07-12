
// javascript menu swapper
function move(fbox_id, tbox_id) {
    var fbox = $(document.getElementById(fbox_id)),
        tbox = $(document.getElementById(tbox_id)),
        options = fbox.find('option:selected').detach().toArray();
    options = options.concat(tbox.find('option').detach().toArray());
    options.sort(function(a, b) {
        return a.text > b.text ? 1 : -1;
    });
    tbox.append(options);
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

    var dialog = $('<div class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modal-label" aria-hidden="true"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h4 class="modal-title" id="modal-label"></h4></div><div class="modal-body"></div><div class="modal-footer"><button type="button" class="btn btn-default" data-bs-dismiss="modal"></button><button type="button" class="btn btn-primary"></button></div></div></div></div>');
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
                title_span.html($('<a>', {
                    href: urlAppend + 'courses/' + courses[cid][0] + '/',
                    text: title_span.text()
                }
                ));
                title_span.append(' <i class="fa solid fa-check">');
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
                    var dialog = $('<div class="modal fade" tabindex="-1" role="dialog" aria-hidden="true"><div class="modal-dialog"><div class="modal-content"><div class="modal-body"></div><div class="modal-footer"><button type="button" class="btn btn-default" data-bs-dismiss="modal"></button></div></div></div></div>');
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
            $("img.user-icon-filename").attr("src", urlAppend + "template/modern/img/default_32.png");
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

    $('.clearSelect').click(function (e) {
        e.preventDefault();
        $(this).closest('.panel-body').find('input[type=radio]').prop('checked', false);
    });

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

    if ($('#secsRemaining').length) {
        var timer = $('#progresstime');
        timer.remaining = $('#secsRemaining').val() - 1;
        timer.text(secondsToHms(timer.remaining));
        timer.start = performance.now();
        timer.interval = setInterval(function() {
            var elapsed = (performance.now() - timer.start) / 1000.0;
            if (elapsed > timer.remaining) {
                clearInterval(timer.interval);
                $('<input type="hidden" name="autoSubmit" value="true">').appendTo('.exercise');
                continueSubmit();
            } else {
                timer.text(secondsToHms(timer.remaining - elapsed));
            }
        }, 1000);
    }

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
            return parseInt(this.id.replace('qPanel', ''));
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
        if (!params.checkSinglePage) {
            $('input[name=q_id], input.navbutton').click(function () {
                continueSubmit();
            });
        }
        var finishClicked = false;
        if (params.checkSinglePage) {
            $('.btn[name=buttonFinish]').click(function () {
                finishClicked = true;
            });
        }
        $('.exercise').submit(function (e) {
            var unansweredCount = 0;
            var firstUnanswered;
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

            if (finishClicked) {
                title = params.finalSubmit;
                message = (params.isFinalQuestion?
                            (unansweredCount === 0? '': params.oneUnanswered):
                            params.unseenQuestions) + ' ' +
                    params.finalSubmitWarn;
            } else if (unansweredCount === 0) {
                if (params.checkSinglePage && !params.isFinalQuestion) {
                    continueSubmit();
                    return;
                } else {
                    title = params.finalSubmit;
                    message = params.finalSubmitWarn;
                }
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
                        '<p>' + message + '</p>' +
                      '</div>' +
                    '</div>',
                buttons: {
                    goBack: {
                        label: params.goBack,
                        className: 'submitAdminBtn',
                        callback: function () {
                            finishClicked = false;
                            var moveTo = $('#qPanel' + firstUnanswered);
                            if (moveTo.length) {
                                $('html').animate({
                                    scrollTop: moveTo.offset().top + 'px'
                                }, 'fast');
                            }
                        }
                    },
                    submit: {
                        label: finishClicked? params.finalSubmit: params.submit,
                        className: (params.checkSinglePage && !params.isFinalQuestion && !finishClicked)? 'submitAdminBtn': 'successAdminBtn',
                        callback: function () {
                            if (finishClicked || !params.checkSinglePage || params.isFinalQuestion) {
                                $('<input type="hidden" name="buttonFinish" value="true">').appendTo('.exercise');
                            }
                            continueSubmit();
                        },
                        onHide: function () {
                            finishClicked = false;
                        },
                    },
                }
            });
        });
    }
    var checkUnanswered = params.checkSinglePage || $('.qPanel').length >= 1;
    $('.btn[name=buttonSave]').click(continueSubmit);
    $('#cancelButton').click(function (e) {
      e.preventDefault();
      bootbox.confirm({
        closeButton: false,
        message: params.cancelMessage,
        buttons: {
          confirm: {
            label: params.cancelAttempt,
            className: 'deleteAdminBtn'
          },
          cancel: {
            label: params.goBack,
            className: 'cancelAdminBtn'
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
    var button_id = "#q_num" + question_number;
    var qpanel_id = "#qPanel" + question_id;
    var check_id = "#qCheck" + question_number; // `check` icon
    var el = $(qpanel_id + " :input");
    var answered = true; // by default we assume that an interaction has answered the question

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

    if (answered) {
        $(button_id).removeClass('btn-default').addClass('btn-info')
            .attr('data-original-title', langHasAnswered).tooltip('setContent');
        $(check_id).addClass('fa fa-check');
    }
}


/**
 * @brief update question number button color (type = 'multiple choice', 'true/false')
 * @param question_number
 */
function updateQuestionNavButton(question_number) {
    var button_id = "#q_num" + question_number; // button
    var check_id = "#qCheck" + question_number; // `check` icon

    $(button_id).removeClass('btn-default').addClass('btn-info')
        .attr('data-original-title', langHasAnswered).tooltip('setContent');
    $(check_id).addClass('fa fa-check');
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
        last_form_group.before("<div class='form-group input-group mt-3'><input class='form-control mt-0' type='text' name='answers[]' value=''><div class='form-control-static input-group-text h-40px bg-white input-border-color'><a href='#' style='cursor: pointer;' class='del_btn'><i class='fa-solid fa-xmark Accent-200-cl'></i></a></div></div>").next().find('input').removeAttr('value');
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

// Multiple file submission support for assignments
function initialize_multifile_submission(max) {
    var formGroup = $('input[type=file]').closest('.form-group');
    var fileInputCount = function () {
        return formGroup.find('input[type=file]').length;
    };
    formGroup.on('change', 'input[type=file]', function () {
        var emptyInputs = 0;
        formGroup.find('input[type=file]').each(function () {
            if (!$(this).val()) {
                emptyInputs++;
            }
        });
        if (emptyInputs == 0) {
            $('.moreFiles.submitAdminBtn').click();
        }
    });
    $('body').on('click', '.moreFiles.submitAdminBtn', function (e) {
        e.preventDefault();
        fileInputs = fileInputCount();
        if (fileInputs < max) {
            var newInput = $(this).closest('.col-sm-10').clone();
            $(newInput).addClass('col-sm-offset-2 mt-2').find('input').val(null);
            if (fileInputs == max - 1) {
                $(newInput).find('button').prop('disabled', true);
            }
            formGroup.append(newInput);
            $(this).removeClass('submitAdminBtn').addClass('deleteAdminBtn')
                .find('.fa-plus').removeClass('fa-plus').addClass('fa-xmark');
        }
    });
    $('body').on('click', '.moreFiles.deleteAdminBtn', function (e) {
        e.preventDefault();
        fileInputs = fileInputCount();
        if (fileInputs == max) {
            formGroup.find('button').prop('disabled', false);
        }
        $(this).closest('.col-sm-10').remove();
        formGroup.children('.col-sm-10').first().removeClass('col-sm-offset-2');
    });
}

var filemodal_initialized = false;
function initialize_filemodal(lang) {
  if (filemodal_initialized) {
    return;
  } else {
    filemodal_initialized = true;
  }
  $('.fileModal').click(function (e) {
    e.preventDefault();
    var fileURL = $(this).attr('href');
    var downloadURL = fileURL + '&download=true';
    var fileTitle = $(this).text();
    var buttons = {};
    buttons.download = {
      label: '<i class="fa fa-download"></i> ' + lang.download,
      className: 'submitAdminBtn gap-1',
      callback: function (d) {
        window.location = downloadURL;
      }
    };
    buttons.print = {
      label: '<i class="fa fa-print"></i> ' + lang.print,
      className: 'submitAdminBtn gap-1',
      callback: function (d) {
        var iframe = document.getElementById('fileFrame');
        iframe.contentWindow.print();
      }
    };
    if (screenfull.enabled) {
      buttons.fullscreen = {
        label: '<i class="fa fa-arrows-alt"></i> ' + lang.fullScreen,
        className: 'submitAdminBtn gap-1',
        callback: function() {
          screenfull.request(document.getElementById('fileFrame'));
          return false;
        }
      };
    }
    buttons.newtab = {
      label: '<i class="fa fa-plus"></i> ' + lang.newTab,
      className: 'submitAdminBtn gap-1',
      callback: function() {
        window.open(fileURL);
        return false;
      }
    };
    buttons.cancel = {
      label: lang.cancel,
      className: 'cancelAdminBtn'
    };
    bootbox.dialog({
      size: 'large',
      title: fileTitle,
      message: '<div class="row">'+
        '<div class="col-sm-12">'+
        '<div class="iframe-container" style="height:500px;"><iframe id="fileFrame" src="'+fileURL+'" style="width:100%; height:500px;"></iframe></div>'+
        '</div>'+
        '</div>',
      buttons: buttons
    });
  });
}

function unit_password_bootbox(e) {
  var el = $(this),
      link = el.attr('href'),
      passwordForm = '',
      passwordCallback = null,
      notice = '',
      title;

  if (el.hasClass('paused_exercise')) {
    lang.submit = title = lang.continueAttempt;
    notice = '<p>' + lang.temporarySaveNotice + '</p>';
  } else if (el.hasClass('active_exercise')) {
    lang.submit = title = lang.continueAttempt;
    notice = '<p>' + lang.continueAttemptNotice + '</p>';
  }
  if (el.hasClass('password_protected')) {
    passwordForm = (notice? ('<p>' + lang.exercisePasswordModalTitle + '</p>'): '')+
      '<form class="form-horizontal" role="form" action="'+link+'" method="post" id="password_form">'+
        '<div class="form-group">'+
          '<div class="col-sm-12">'+
            '<input type="text" class="form-control" id="password" name="password">'+
          '</div>'+
        '</div>'+
      '</form>';
    passwordCallback = function () {
      var password = $('#password').val();
      if (password != '') {
        $('#password_form').submit();
      } else {
        if (!$('#password').siblings('.help-block').length) {
          $('#password').after('<p class="help-block">'+lang.theFieldIsRequired+'</p>');
        }
        $('#password').closest('.form-group').addClass('has-error');
        return false;
      }
    };
    if (!title) {
      title = el.hasClass('ex_settings')?
        lang.exercisePasswordModalTitle:
        lang.assignmentPasswordModalTitle;
    }
  }

  if (!title) {
    return;
  }

  if (!passwordCallback) {
    passwordCallback = function () {
      window.location = link;
    };
  }

  e.preventDefault();
  bootbox.dialog({
    closeButton: false,
    title: title,
    message: notice + passwordForm,
    buttons: {
      cancel: {
        label: lang.cancel,
        className: 'cancelAdminBtn'
      },
      success: {
        label: lang.submit,
        className: 'submitAdminBtn',
        callback: passwordCallback,
      }
    }
  });
}

//checks on a form if is checked at least one radiobox or checkbox
function formReqChecker(formID,alertMSG) {

    $(formID).submit(function(event) {
        const checkboxesChecked = $('input[type=checkbox]:checked').length > 0;
        const radiosChecked = $('input[type=radio]:checked').length > 0;

        if (!checkboxesChecked && !radiosChecked) {
            event.preventDefault();
            alert(alertMSG);
        } else {
            $(formID).submit();
        }
    });

}
