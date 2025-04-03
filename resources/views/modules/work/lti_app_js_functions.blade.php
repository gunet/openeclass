<script type='text/javascript'>
//<![CDATA[

// Formats a date string that can be parsed, i.e. as derived from remote LTI content selection, into specific format (DD-MM-YYYY HH:mm).
function formatReturnDate(dt) {
    const unixtime = Date.parse(dt);
    const dtobject = new Date(unixtime);
    const day = dtobject.getDate().toString().padStart(2, '0');
    const month = (dtobject.getMonth() + 1).toString().padStart(2, '0');
    const year = dtobject.getFullYear().toString();
    const hours = dtobject.getHours().toString().padStart(2, '0');
    const minutes = dtobject.getMinutes().toString().padStart(2, '0');
    return day + '-' + month + '-' + year + ' ' + hours + ':' + minutes;
}

// populate local assignment form from remote LTI content selection and hide modal
window.processContentItemReturnData = function(returnData) {
    if (returnData === undefined) {
        return;
    }
    if (returnData.title !== undefined) {
        $('#title').val(returnData.title);
    }
    if (returnData.maxscore !== undefined) {
        $('#max_grade').val(returnData.maxscore);
    }
    if (returnData.startdate !== undefined) {
        $('#WorkStart').val(formatReturnDate(returnData.startdate));
    }
    if (returnData.enddate !== undefined) {
        $('#WorkEnd').val(formatReturnDate(returnData.enddate));
    }
    if (returnData.feedbackdate !== undefined) {
        $('#tii_feedbackreleasedate').val(formatReturnDate(returnData.feedbackdate));
    }
    if (returnData.instructorcustomparameters !== undefined) {
        $('#tii_instructorcustomparameters').val(returnData.instructorcustomparameters);
    }
    $('#SelectContentModal').modal('hide');
    let selected_content_indicator = $("{!! get_selected_content_indicator() !!}");
    $('#tii_selected_content_span').html('').append(selected_content_indicator);
}

function checkLtiSelectContentRequired() {
    const ltiTemplate1P3Ids = [{!! resolve_lti_template_1P3_ids_js($lti_templates) !!}];
    let selectedTemplate = $('#lti_templates').find(':selected').val();
    if (ltiTemplate1P3Ids.includes(Number(selectedTemplate))) {
        $('#SelectContentModalDiv').removeClass('hidden');
        hideLti1Fields();
        showLti13Fields();
    } else {
        $('#SelectContentModalDiv').addClass('hidden');
        showLti1Fields();
        hideLti13Fields();
    }
}

function setSelectContentFrameHtml(data) {
    let doc = document.getElementById('SelectContentModalBodyContentFrame').contentWindow.document;
    doc.open();
    doc.write('<html><head><title></title></head><body>' + data + '</body></html>');
    doc.close();
}

function hideLtiCommonFields() {
    $('#lti_label')
        .prop('disabled', true)
        .closest('div.form-group')
        .addClass('hidden');
    $('#lti_templates')
        .prop('disabled', true)
        .closest('div.form-group')
        .addClass('hidden');
    $('#lti_launchcontainer')
        .prop('disabled', true)
        .closest('div.form-group')
        .addClass('hidden');
    $('#tii_feedbackreleasedate')
        .closest('div.form-group')
        .addClass('hidden');
}

function showLtiCommonFields() {
    $('#lti_label')
        .prop('disabled', false)
        .closest('div.form-group')
        .removeClass('hidden');
    $('#lti_templates')
        .prop('disabled', false)
        .closest('div.form-group')
        .removeClass('hidden');
    $('#lti_launchcontainer')
        .prop('disabled', false)
        .closest('div.form-group')
        .removeClass('hidden');
    $('#tii_feedbackreleasedate')
        .closest('div.form-group')
        .removeClass('hidden');
}

function hideLti1Fields() {
    $('#tii_internetcheck')
        .prop('disabled', true)
        .closest('div.form-group')
        .addClass('hidden');
    /*$('#tii_institutioncheck')
        .prop('disabled', true)
        .closest('div.form-group')
        .addClass('hidden');*/
    $('#tii_journalcheck')
        .prop('disabled', true)
        .closest('div.form-group')
        .addClass('hidden');
    $('#tii_report_gen_speed')
        .prop('disabled', true)
        .closest('div.form-group')
        .addClass('hidden');
    $('#tii_s_view_reports')
        .prop('disabled', true)
        .closest('div.form-group')
        .addClass('hidden');
    $('#tii_studentpapercheck')
        .prop('disabled', true)
        .closest('div.form-group')
        .addClass('hidden');
    /*$('#tii_submit_papers_to')
        .prop('disabled', true)
        .closest('div.form-group')
        .addClass('hidden');*/
    $('#tii_use_biblio_exclusion')
        .prop('disabled', true)
        .closest('div.form-group')
        .addClass('hidden');
    $('#tii_use_quoted_exclusion')
        .prop('disabled', true)
        .closest('div.form-group')
        .addClass('hidden');
    $('#tii_use_small_exclusion')
        .prop('disabled', true)
        .closest('div.form-group')
        .addClass('hidden');
}

function showLti1Fields() {
    $('#tii_internetcheck')
        .prop('disabled', false)
        .closest('div.form-group')
        .removeClass('hidden');
    /*$('#tii_institutioncheck')
        .prop('disabled', false)
        .closest('div.form-group')
        .removeClass('hidden');*/
    $('#tii_journalcheck')
        .prop('disabled', false)
        .closest('div.form-group')
        .removeClass('hidden');
    $('#tii_report_gen_speed')
        .prop('disabled', false)
        .closest('div.form-group')
        .removeClass('hidden');
    $('#tii_s_view_reports')
        .prop('disabled', false)
        .closest('div.form-group')
        .removeClass('hidden');
    $('#tii_studentpapercheck')
        .prop('disabled', false)
        .closest('div.form-group')
        .removeClass('hidden');
    /*$('#tii_submit_papers_to')
        .prop('disabled', false)
        .closest('div.form-group')
        .removeClass('hidden');*/
    $('#tii_use_biblio_exclusion')
        .prop('disabled', false)
        .closest('div.form-group')
        .removeClass('hidden');
    $('#tii_use_quoted_exclusion')
        .prop('disabled', false)
        .closest('div.form-group')
        .removeClass('hidden');
    $('#tii_use_small_exclusion')
        .prop('disabled', false)
        .closest('div.form-group')
        .removeClass('hidden');
}

function hideLti13Fields() {
    $('#tii_instructorcustomparameters')
        .prop('disabled', true)
        .closest('div.form-group')
        .addClass('hidden');
}

function showLti13Fields() {
    $('#tii_instructorcustomparameters')
        .prop('disabled', false)
        .closest('div.form-group')
        .removeClass('hidden');
}

function hideLtiAllFields() {
    hideLtiCommonFields();
    hideLti1Fields();
}

function showLtiAllFields() {
    showLtiCommonFields();
    showLti1Fields();
}

//]]
</script>
