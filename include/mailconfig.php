<?php
/* ========================================================================
 * Open eClass 3.3
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2016  Greek Universities Network - GUnet
 * A full copyright notice can be read in "/info/copyright.txt".
 * For a full list of contributors, see "credits.txt".
 *
 * Open eClass is an open platform distributed in the hope that it will
 * be useful (without any warranty), under the terms of the GNU (General
 * Public License) as published by the Free Software Foundation.
 * The full license can be read in "/info/license/license_gpl.txt".
 *
 * Contact address: GUnet Asynchronous eLearning Group,
 *                  Network Operations Center, University of Athens,
 *                  Panepistimiopolis Ilissia, 15784, Athens, Greece
 *                  e-mail: info@openeclass.org
 * ======================================================================== */

// Utils for email configuration, used during installation, upgrade and
// configuration

$mail_form_js = <<<EOF
    $('#formEmailTransport').change(function() {
        var type = $(this).val();
        if (type == 1) {
            $('.SMTP-settings').show();
            $('.Sendmail-settings').hide();
        } else if (type == 2) {
            $('.SMTP-settings').hide();
            $('.Sendmail-settings').show();
        } else {
            $('.SMTP-settings, .Sendmail-settings').hide();
        }
        if (type == 0 && $('#formEmailAnnounce').val() == '') {
            $('#emailSendWarn').show();
            $('#formEmailAnnounceGroup').addClass('has-error');
        } else {
            $('#emailSendWarn').hide();
            $('#formEmailAnnounceGroup').removeClass('has-error');
        }
    }).change();
    $('#revealPass').mousedown(function () {
        $('#formSMTPPassword').attr('type', 'text');
    }).mouseup(function () {
        $('#formSMTPPassword').attr('type', 'password');
    });
EOF;

// Store mail configuration from POST variables
function store_mail_config() {
    global $smtp_server, $smtp_port, $smtp_encryption, $smtp_username,
        $smtp_password, $dont_mail_unverified_mails, $email_from;

    register_posted_variables(array(
        'dont_mail_unverified_mails' => true,
        'email_from' => true), 'all', 'intval');
    set_config('dont_mail_unverified_mails', $dont_mail_unverified_mails);
    set_config('email_from', $email_from);
    set_config('email_announce', $_POST['email_announce']);
    set_config('email_bounces', $_POST['email_bounces']);
    if ($_POST['email_transport'] == 1) {
        set_config('email_transport', 'smtp');
        register_posted_variables(array('smtp_encryption' => true,
            'smtp_server' => true, 'smtp_port' => true,
            'smtp_username' => true, 'smtp_password' => true));
        $smtp_port = intval($smtp_port);
        if (!$smtp_port) {
            $smtp_port = 25;
        }
        set_config('smtp_server', $smtp_server);
        set_config('smtp_port', $smtp_port);
        set_config('smtp_username', $smtp_username);
        set_config('smtp_password', $smtp_password);
        if ($smtp_encryption == 1) {
            set_config('smtp_encryption', 'ssl');
        } elseif ($smtp_encryption == 2) {
            set_config('smtp_encryption', 'tls');
        } else {
            set_config('smtp_encryption', '');
        }
    } elseif ($_POST['email_transport'] == 2) {
        set_config('email_transport', 'sendmail');
        set_config('sendmail_command', $_POST['sendmail_command']);
    } else {
        set_config('email_transport', 'mail');
    }
}

function get_var($name, $default=null) {
    if (isset($GLOBALS['input_fields'])) {
        if (isset($GLOBALS[$name])) {
            return $GLOBALS[$name];
        } else {
            return $default;
        }
    } else {
        return get_config($name, $default);
    }
}

function mail_settings_form() {
    global $langEmailSettings, $lang_dont_mail_unverified_mails, $langNo,
        $lang_email_from, $langEmailAnnounce, $langUsername, $langPassword,
        $langEmailSendmail, $langEmailTransport, $langEmailSMTPServer,
        $langEmailSMTPPort, $langEmailEncryption, $langEmailSendWarn,
        $langPreviousStep, $langNextStep, $tool_content, $langEmailBounces;

    // True if running initial install
    $install = isset($GLOBALS['input_fields']);

    $emailTransports = array(0 => 'PHP mail()', 1 => 'SMTP', 2 => 'sendmail');
    $email_transport = get_var('email_transport');
    if (!is_numeric($email_transport)) {
        if ($email_transport == 'smtp') {
            $email_transport = 1;
        } elseif ($email_transport == 'sendmail') {
            $email_transport = 2;
        } else {
            $email_transport = 0;
        }
    }
    $emailEncryption = array(0 => $langNo, 1 => 'SSL', 2 => 'TLS');
    $smtp_encryption = get_var('smtp_encryption');
    if ($smtp_encryption == 'ssl') {
        $smtp_encryption = 1;
    } elseif ($smtp_encryption == 'tls') {
        $smtp_encryption = 2;
    } else {
        $smtp_encryption = 0;
    }
    $cbox_dont_mail_unverified_mails = get_var('dont_mail_unverified_mails') ? 'checked' : '';
    $cbox_email_from = get_var('email_from') ? 'checked' : '';
    if (!$install) {
        $tool_content .= "
            <div class='panel panel-default' id='four'>
                <div class='panel-heading'>
                    <h2 class='panel-title'>$langEmailSettings</h2>
                </div>
                <div class='panel-body'>
                    <fieldset>";
    }
    $tool_content .= "
                        <div class='form-group'>
                           <div class='col-sm-12'>
                                <div class='checkbox'>
                                    <label>
                                        <input type='checkbox' name='dont_mail_unverified_mails' value='1' $cbox_dont_mail_unverified_mails>
                                        $lang_dont_mail_unverified_mails
                                    </label>
                                </div>
                                <div class='checkbox'>
                                    <label>
                                        <input type='checkbox' name='email_from' value='1' $cbox_email_from>
                                        $lang_email_from
                                    </label>
                                </div>
                           </div>
                        </div>
                        <div class='form-group' id='formEmailAnnounceGroup'>
                           <label for='formEmailAnnounce' class='col-sm-2 control-label'>$langEmailAnnounce:</label>
                           <div class='col-sm-10'>
                                <input type='text' class='form-control' name='email_announce' id='formEmailAnnounce' value='".q(get_var('email_announce'))."'>
                                <span class='help-block' id='emailSendWarn'>$langEmailSendWarn</span>
                           </div>
                        </div>
                        <div class='form-group'>
                           <label for='formEmailBounces' class='col-sm-2 control-label'>$langEmailBounces:</label>
                           <div class='col-sm-10'>
                                <input type='text' class='form-control' name='email_bounces' id='formEmailBounces' value='".q(get_var('email_bounces'))."'>
                           </div>
                        </div>
                        <div class='form-group'>
                           <label for='formEmailTransport' class='col-sm-2 control-label'>$langEmailTransport:</label>
                           <div class='col-sm-10'>" .
                               selection($emailTransports, 'email_transport', $email_transport,
                                         "class='form-control' id='formEmailTransport'") . "
                           </div>
                        </div>
                        <div class='form-group SMTP-settings'>
                           <label for='formSMTPServer' class='col-sm-2 control-label'>$langEmailSMTPServer:</label>
                           <div class='col-sm-10'>
                                <input type='text' class='form-control' name='smtp_server' id='formSMTPServer' value='".q(get_var('smtp_server'))."'>
                           </div>
                        </div>
                        <div class='form-group SMTP-settings'>
                           <label for='formSMTPPort' class='col-sm-2 control-label'>$langEmailSMTPPort:</label>
                           <div class='col-sm-10'>
                                <input type='text' class='form-control' name='smtp_port' id='formSMTPPort' value='".q(get_var('smtp_port', 25))."'>
                           </div>
                        </div>
                        <div class='form-group SMTP-settings'>
                           <label for='formEmailEncryption' class='col-sm-2 control-label'>$langEmailEncryption:</label>
                           <div class='col-sm-10'>" .
                               selection($emailEncryption, 'smtp_encryption', $smtp_encryption,
                                         "class='form-control' id='formEmailEncryption'") . "
                           </div>
                        </div>
                        <div class='form-group SMTP-settings'>
                           <label for='formSMTPUsername' class='col-sm-2 control-label'>$langUsername:</label>
                           <div class='col-sm-10'>
                                <input type='text' class='form-control' name='smtp_username' id='formSMTPUsername' value='".q(get_var('smtp_username'))."'>
                           </div>
                        </div>
                        <div class='form-group SMTP-settings'>
                           <label for='formSMTPPassword' class='col-sm-2 control-label'>$langPassword:</label>
                           <div class='col-sm-10'>
                                <div class='input-group'>
                                    <input type='password' class='form-control' name='smtp_password' id='formSMTPPassword' value='".q(get_var('smtp_password'))."'><span id='revealPass' class='input-group-addon'><span class='fa fa-eye'></span></span>
                                </div>
                           </div>
                        </div>
                        <div class='form-group Sendmail-settings'>
                           <label for='formSendmailCommand' class='col-sm-2 control-label'>$langEmailSendmail:</label>
                           <div class='col-sm-10'>
                                <input type='text' class='form-control' name='sendmail_command' id='formSendmailCommand' value='".q(get_var('sendmail_command', ini_get('sendmail_path')))."'>
                           </div>
                        </div>";
    if (!$install) {
        $tool_content .= "
                    </fieldset>
                </div>
            </div>";
    }
}
