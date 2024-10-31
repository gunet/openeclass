<?php
/*
 *  ========================================================================
 *  * Open eClass
 *  * E-learning and Course Management System
 *  * ========================================================================
 *  * Copyright 2003-2024, Greek Universities Network - GUnet
 *  *
 *  * Open eClass is an open platform distributed in the hope that it will
 *  * be useful (without any warranty), under the terms of the GNU (General
 *  * Public License) as published by the Free Software Foundation.
 *  * The full license can be read in "/info/license/license_gpl.txt".
 *  *
 *  * Contact address: GUnet Asynchronous eLearning Group
 *  *                  e-mail: info@openeclass.org
 *  * ========================================================================
 *
 */

/**
 * Created by PhpStorm.
 * User: jexi
 * Date: 10/9/18
 * Time: 10:50 AM
 */

// ------------ feel free to change it ----------
define ('INTRO_SSO_MESSAGE', 'Η διαδικασία μετάβασης των λογαριασμών των χρηστών έχει ξεκινήσει! 
                            Κάντε κλικ στο `Μετάβαση`. Θα μεταφερθείτε στην κεντρική σελίδα Πιστοποίησης Λογαριασμού 
                            όπου θα δώσετε τα στοιχεία σας έτσι ώστε ο λογαριασμός σας στην πλατφόρμα να ενημερωθεί.');
// ----------------------------------------------
$require_login = true;
$transition_script = true;
require_once '../../../include/baseTheme.php';
require_once 'modules/auth/transition/Transition.class.php';
$pageName = "Μετάβαση";

if (!get_config('sso_transition')) {
    redirect("{$urlServer}index.php?logout=yes");
}

$_SESSION['SSO_USER_TRANSITION'] = true;

$tool_content .= action_bar(array(
    array('title' => $langBack,
        'url' => "{$urlAppend}index.php?logout=yes",
        'icon' => 'fa-reply',
        'level' => 'primary')
),false);

$auth_transition = new Transition($uid);
if ($auth_transition->get_sso_exception_status() == SSO_TRANSITION_EXCEPTION_BLOCKED) {
    $tool_content .= "<div class='alert alert-danger'><i class='fa-solid fa-circle-xmark fa-lg'></i><span>Η πρόσβαση στην πλατφόρμα έχει αποκλειστεί. 
                    Μπορείτε να επικοινωνήσετε με τους διαχειριστές της πλατφόρμας στο <strong>" . get_config('email_helpdesk') . "</strong>.</span></div>";
} else if ($auth_transition->get_sso_exception_status() == SSO_TRANSITION_EXCEPTION_PENDING) { // sso exception pending
    $tool_content .= "<div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>Έχετε ήδη υποβάλλει αίτημα εξαίρεσης το οποίο ακόμα δεν έχει διεκπεραιωθεί. 
                       Η πρόσβαση στην πλατφόρμα προσωρινά δεν είναι δυνατή. 
                       Μπορείτε να επικοινωνήσετε με τους διαχειριστές της πλατφόρμας στο <strong>" . get_config('email_helpdesk') . "</strong>.</span></div>";
} else if (isset($_GET['exception'])) {
    $auth_transition->add_sso_exception($_POST['comments']);
    $tool_content .= "<div class='alert alert-info'><i class='fa-solid fa-circle-info fa-lg'></i><span>Το αίτημα εξαίρεσης κατοχυρώθηκε.</span></div>";
} else if (isset($_GET['t'])) {
    if ($_GET['t'] == 'true') { // add exception request
        unset($_SESSION['SSO_USER_TRANSITION']);
        $auth_transition->sso_authenticate();
    } else { // display exception form
        display_sso_exception_form($uid);
    }
} else {  // intro message
    $tool_content .= "<div class='row'>
                    <div class='col-sm-12'>
                        <div class='alert alert-info'><i class='fa-solid fa-circle-info fa-lg'></i><span>" . INTRO_SSO_MESSAGE . "</span></div>
                        <div class='text-justify'>
                            <a href='$_SERVER[SCRIPT_NAME]?t=true' class='btn submitAdminBtn' role='button'>Μετάβαση</a>
                            <a href='$_SERVER[SCRIPT_NAME]?t=false' class='btn deleteAdminBtn' role='button'>Αίτημα εξαίρεσης</a>
                        </div>
                    </div>
                </div>";
}

draw($tool_content, 1);

/**
 * @brief display form sso exception
 */
function display_sso_exception_form($uid) {

    global $tool_content, $langName, $langComments, $langSend, $urlAppend, $langForm;

    $firstname = uid_to_name($uid);
    $tool_content .= "
        <div class='col-12'><div class='alert alert-info'><i class='fa-solid fa-circle-info fa-lg'></i><span>Παρακαλώ αναφέρατε τους λόγους εξαίρεσης.</span></div></div>
        <div class='col-12 mt-4'><div class='form-wrapper form-edit rounded'>
            <form class='form-horizontal' action='$_SERVER[SCRIPT_NAME]?exception=TRUE' method='post'>
            <fieldset>
                <legend class='mb-0' aria-label='$langForm'></legend>
                <div class='form-group'>
                    <label for='name_id' class='col-sm-12 control-label-notes'>$langName</label>
                    <div class='col-sm-12'>
                        <input id='name_id' class='form-control' type='text' name='$langName' value='" . q($firstname) . "' disabled  />
                    </div>
                </div>                          
                <div class='form-group mt-4'>
                    <label for='comments_id' class='col-sm-12 control-label-notes'>$langComments</label>
                    <div class='col-sm-12'>
                        <textarea id='comments_id' class='form-control' rows='6' name='comments'></textarea>
                    </div>
                </div>
                <div class='form-group mt-5'>
                <div class='col-12 d-flex justify-content-center align-items-center'>".
                    form_buttons(array(
                        array(
                            'class' => 'submitAdminBtn',
                            'text'  => $langSend,
                            'name'  => 'submit',
                            'value' => $langSend,
                        ),
                        array(
                            'class' => 'cancelAdminBtn ms-1',
                            'href' => $urlAppend
                        )
                    ))
                    ."</div>
                </div>
                </fieldset>
            </form>
        </div></div>";
}
