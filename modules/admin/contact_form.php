<?php

require_once '../../include/baseTheme.php';
require_once 'include/sendMail.inc.php';

if (isset($_POST['send_message'])) {

    $v = new Valitron\Validator($_POST);
    $v->rule('required', array('contact_name'));
    $v->rule('required', array('contact_surname'));
    $v->rule('required', array('contact_email'));
    $v->rule('required', array('contact_subject'));
    $v->rule('required', array('contact_message'));

    $v->labels(array(
        'contact_name' => "$langTheField $langName",
        'contact_surname' => "$langTheField $langSurname",
        'contact_email' => "$langTheField $langEmail",
        'contact_subject' => "$langTheField $langSubject",
        'contact_message' => "$langTheField $langMessage"
    ));

    if($v->validate()) {

        $email_user = $_POST['contact_email'];
        if (!filter_var($email_user, FILTER_VALIDATE_EMAIL)) {
            Session::flash('message', $langInvalidEmail);
            Session::flash('alert-class', 'alert-danger');
            redirect_to_home_page('info/contact.php');
        }

        $name = q($_POST['contact_name']);
        $surname = q($_POST['contact_surname']);
        $subject = q($_POST['contact_subject']);
        $message = purify($_POST['contact_message']);
        $help_desk_email = get_config('email_helpdesk');

        $emailHeader = "        
                <div id='mail-header'>
                    <br>
                        <div id='header-title'>$langSent $langFrom2: <span>$name $surname ($email_user)</span></div>
                </div>";

        $emailMain = "
            <div id='mail-body'>
                <br>
                <div id='mail-body-inner'>
                <span>$message</span>
                </div>
            </div>";

        $emailsubject = $subject;
        $emailbody = $emailHeader.$emailMain;
        $emailPlainBody = html2text($emailbody);

        send_mail_multipart('', '', '', $help_desk_email, $emailsubject, $emailPlainBody, $emailbody);

        Session::flash('message', $langStored);
        Session::flash('alert-class', 'alert-success');

    } else {
        Session::flashPost()->Messages($langFormErrors)->Errors($v->errors());
    }
}

redirect_to_home_page('info/contact.php');
