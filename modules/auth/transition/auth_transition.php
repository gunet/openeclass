<?php
/**
 * Created by PhpStorm.
 * User: jexi
 * Date: 10/9/18
 * Time: 10:50 AM
 */

$require_login = true;
$transition_script = true;
require_once '../../../include/baseTheme.php';
require_once 'modules/auth/transition/Transition.class.php';
$pageName = "Μετάβαση";

if (!defined('SSO_TRANSITION')) {
    redirect("{$urlServer}index.php?logout=yes");
}

$_SESSION['SSO_USER_TRANSITION'] = true;

$tool_content .= action_bar(array(
    array('title' => $langBack,
        'url' => "{$urlAppend}index.php?logout=yes",
        'icon' => 'fa-reply',
        'level' => 'primary-label')
),false);

$auth_transition = new Transition($uid);
if ($auth_transition->get_sso_exception_status() == SSO_TRANSITION_EXCEPTION_BLOCKED) {
    $tool_content .= "<div class='alert alert-danger'>Η πρόσβαση στην πλατφόρμα έχει αποκλειστεί. 
                    Μπορείτε να επικοινωνήσετε με τους διαχειριστές της πλατφόρμας στο <strong>" . get_config('email_helpdesk') . "</strong>.</div>";
} else if ($auth_transition->get_sso_exception_status() == SSO_TRANSITION_EXCEPTION_PENDING) { // sso exception pending
    $tool_content .= "<div class='alert alert-warning'>Έχετε ήδη υποβάλλει αίτημα εξαίρεσης το οποίο ακόμα δεν έχει διεκπεραιωθεί. 
                       Η πρόσβαση στην πλατφόρμα προσωρινά δεν είναι δυνατή. 
                       Μπορείτε να επικοινωνήσετε με τους διαχειριστές της πλατφόρμας στο <strong>" . get_config('email_helpdesk') . "</strong>.</div>";
} else if (isset($_GET['exception'])) {
    $auth_transition->add_sso_exception($_POST['comments']);
    $tool_content .= "<div class='alert alert-info'>Το αίτημα εξαίρεσης κατοχυρώθηκε.</div>";
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
                        <div class='alert alert-info'>Η διαδικασία μετάβασης έχει ξεκινήσει</div>
                        <div class='text-justify'>
                            <a href='$_SERVER[SCRIPT_NAME]?t=true' class='btn btn-success' role='button'>Μετάβαση</a>
                            <a href='$_SERVER[SCRIPT_NAME]?t=false' class='btn btn-warning' role='button'>Αίτημα εξαίρεσης</a>
                        </div>
                    </div>
                </div>";
}

draw($tool_content, 1);

/**
 * @brief display form sso exception
 */
function display_sso_exception_form($uid) {

    global $tool_content, $langName, $langComments, $langSend, $urlAppend;

    $firstname = uid_to_name($uid);
    $tool_content .= "
        <div class='alert alert-info'>Παρακαλώ εξηγήστε τους λόγους εξαίρεσης.</div>
        <div class='form-wrapper'>
            <form class='form-horizontal' action='$_SERVER[SCRIPT_NAME]?exception=TRUE' method='post'>
            <fieldset>
                <div class='form-group'>
                    <label class='col-sm-2 control-label'>$langName:</label>
                    <div class='col-sm-10'>
                        <input class='form-control' type='text' name='$langName' value='" . q($firstname) . "' disabled  />
                    </div>
                </div>                          
                <div class='form-group'>
                    <label for='$langComments' class='col-sm-2 control-label'>$langComments:</label>
                    <div class='col-sm-10'>
                        <textarea class='form-control' rows='6' name='comments'></textarea>
                    </div>
                </div>
                <div class='form-group'>
                <div class='col-sm-10 col-sm-offset-2'>".
                    form_buttons(array(
                        array(
                            'text'  => $langSend,
                            'name'  => 'submit',
                            'value' => $langSend,
                        ),
                        array(
                            'href' => $urlAppend
                        )
                    ))
                    ."</div>
                </div>
                </fieldset>
            </form>
        </div>";
}