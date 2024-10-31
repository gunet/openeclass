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

require_once 'genericrequiredparam.php';

class secondfaApp extends ExtApp {
    public static $ServiceNames = array("g2fa");

    public function __construct() {
        parent::__construct();
    }

    public function getLongDescription() {
        return $GLOBALS['langsecondfaDescription'];
    }

    public function getShortDescription() {
        return $GLOBALS['langsecondfaDescription'];
    }

    public function getDisplayName() {
        return '2FA';
    }

    public function getConfigUrl() {
        return 'modules/admin/secondfamoduleconf.php';
    }

    /**
     * Returns true if a compilation service has been selected
     *
     * @return boolean
     */
    public  function isConfigured() {
        return (q(get_config('secondfa_connector')) != null);
    }

    public static function getsecondfa() {
        $secondfa = ExtAppManager::getApp('2fa');
        $connector = q(get_config('secondfa_connector'));
        if(!$connector) {
            $connector = new g2fa();
        } else {
            $connector = new $connector();
        }
        $param = $connector->getParam('enabled');
        if ($param) {
            $param->setValue($secondfa->isEnabled());
        }
        return $connector;
    }

    public static function block($output) {
        require_once 'include/tools.php';
        Session::flash('message',$output);
        Session::flash('alert-class', 'alert-warning');
        header("Location: {$_SERVER['SCRIPT_NAME']}");
        exit();
    }
    /**
     * @return ExtApp[]
     */
    public static function getsecondfaServices() {
        if (self::$ServiceNames == null) {
            $apps = array();
            foreach (self::$ServiceNames as $serviceName) {
                $service = new $serviceName();
                $apps[$service->getName()] = $service;
            }
            self::$ServiceNames = $apps;
        }
        return self::$ServiceNames;
    }


    /* DB Functions */

/**
 * Get nearest value from specific key of a multidimensional array
 *
 * @param $key integer
 * @param $arr array
 * @return array
 */
    public static function initializedb(){
        Database::get()->query("CREATE TABLE IF NOT EXISTS secondfactorauth (id int(11) NOT NULL,secret varchar(100) NOT NULL,FOREIGN KEY (id) REFERENCES user(id) ON UPDATE CASCADE ON DELETE CASCADE)");
    }

    public static function storeSecret($userid, $sfa_secret){
        $_SESSION['sfakey'] = $sfa_secret;
        self::initializedb();
        Database::get()->query("INSERT INTO secondfactorauth SET
                                `id` = ?d,
                                `secret` = ?s
                                ", $userid, $sfa_secret);
    }

    public static function retrieveSecret($userid){
        $sfa_secret = "";
        if (isset($_SESSION['sfakey'])){
            $sfa_secret =  $_SESSION['sfakey'];
        } else{
            self::initializedb();
            $record = Database::get()->querySingle("SELECT secret FROM secondfactorauth WHERE id = ?s", $userid);
            if($record){
                $sfa_secret = $record->secret;
            }
        }
        return $sfa_secret;
    }


    /* User API Calls
     *  secondfaApp::showUserProfile()
     *  secondfaApp::saveUserProfile()
     *
     *  secondfaApp::showChallenge()
     *  secondfaApp::checkChallnge()
     */


    public static function showUserProfile($userid){
        $sfa_secret = self::retrieveSecret($userid);
        if ($sfa_secret and $sfa_secret!=""){
            return self::getUnitialize();
        } else {
            $user = Database::get()->querySingle("SELECT email FROM user WHERE id = ?s", $userid);
            $email = $user->email;
            $company = "GUNET eClass";
            return self::getInitialize($userid,$company,$email);
        }
    }

    public static function saveUserProfile($userid){
        $sfa_secret = self::retrieveSecret($userid);
        if ($sfa_secret and $sfa_secret!=""){
            return self::setUnitialize($userid);
        } else {
            return self::setInitialize($userid);
        }
    }


     public static function showChallenge($userid){
        $sfa_secret = self::retrieveSecret($userid);
        if ($sfa_secret and $sfa_secret!=""){
            return self::challenge();
        } else {
            return "";
        }
    }

    public static function checkChallenge($userid){
        global $langSFAfail;
        $sfa_secret = self::retrieveSecret($userid);
        if ($sfa_secret and $sfa_secret!=""){
            if(self::verify($userid, $sfa_secret)->status==="OK"){
                return "OK";
            }else{
                 require_once 'include/tools.php';
                Session::flash('message', $langSFAfail);
                Session::flash('alert-class', 'alert-danger');
                header("Location: {$_SERVER['SCRIPT_NAME']}");
                exit();
            }
        } else {
            return "OK";
        }
    }

    /* INITIALIZATION FUNCTIONS */

    public static function getUnitialize(){
        global $langSFAkeep,$langSFAremove;
        return "<select name='sfaremove' class='form-select'><option value='KEEP' selected>$langSFAkeep</option><option value='REMOVE'>$langSFAremove</option></select>";
    }

    public static function setUnitialize($userid){
        if(isset($_POST['sfaremove']) && !empty($_POST['sfaremove']) && $_POST['sfaremove']==='REMOVE'){
            self::storeSecret($userid, '');
        }
    }

    public static function getInitialize($userid,$company,$email){
        global $langSFAadd, $langSFAremove, $langSFAScan, $langSFATypeWYS, $langSFAInsert, $langConfigError;

        $keypack =  self::getsecondfa()->generateSecret($userid,$company,$email);
        if($keypack){
            $sfa_svg = $keypack[0];
            $sfa_secret = $keypack[1];
            $_SESSION['sfatempkey'] = $sfa_secret;
            return "<div name ='2fabutton'>
                    <input type='button'  class='btn' value='$langSFAadd' onclick='document.getElementsByName(\"2fa\")[0].style.display=\"block\";document.getElementsByName(\"2fa\")[0].style.visibility=\"visible\";document.getElementsByName(\"2fabutton\")[0].style.display=\"none\";document.getElementsByName(\"2fabutton\")[0].style.visibility=\"hidden\"'>
                    </div>
                    <div name='2fa' style='visibility:hidden;display:none'>
                    <input type='button'   class='btn' value='$langSFAremove' onclick='document.getElementsByName(\"2fa\")[0].style.display=\"none\";document.getElementsByName(\"2fa\")[0].style.visibility=\"hidden\";document.getElementsByName(\"2fabutton\")[0].style.display=\"block\";document.getElementsByName(\"2fabutton\")[0].style.visibility=\"visible\"''>
                    <table style='width:100%'>
                    <tr><p>$langSFAScan</p></tr>
                    <tr>$sfa_svg</tr>
                    <tr><p>$langSFAInsert</p></tr>
                    <p>
                    <div class=''>
                        <input class='form-control' type='text' autocomplete='off' name='sfasecret' disabled=disabled value='" . q($sfa_secret) . "'/></tr>
                    </div>
                    </p>
                    <tr><p>(Server Time) ".date("h:i:sa")."</p></tr>
                    <tr><p>$langSFATypeWYS</p></tr>
                    <tr>
                    <div class=''>
                        <input class='form-control' type='password' autocomplete='off' name='sfaanswer' value=''/></tr>
                    </div>
                    </tr>
                    </table>
                    </div>";
        } else {
            return "$langConfigError";
        }
    }

    public static function setInitialize($userid){
        global $langSFAfail;
        if(isset($_POST['sfaanswer']) && !empty($_POST['sfaanswer'])){
            $answer = $_POST['sfaanswer'];
            if ($answer!=""){
                if ($_SESSION['sfatempkey']!=""){
                    if (self::getsecondfa()->check($userid, $answer, $_SESSION['sfatempkey'])->status === "OK"){
                        self::storeSecret($userid, $_SESSION['sfatempkey']);
                    }else{
                        require_once 'include/tools.php';
                        Session::flash('message', $langSFAfail);
                        Session::flash('alert-class', 'alert-danger');
                        header("Location: {$_SERVER['SCRIPT_NAME']}");
                        exit();
                    }
                }else{
                    require_once 'include/tools.php';
                    Session::flash('message', $langSFAfail);
                    Session::flash('alert-class', 'alert-danger');
                    header("Location: {$_SERVER['SCRIPT_NAME']}");
                    exit();
                }
            }
        }
    }


    /* Challenge Responce Functions */

    public static function challenge(){
        return "<input type='password' class='form-control'  autocomplete='off' name='sfaanswer' value=''/>";
    }

    public static function verify($userid, $sfa_secret){
        $status = 0;
        if(isset($_POST['sfaanswer']) && !empty($_POST['sfaanswer'])){
            $answer = $_POST['sfaanswer'];
            if ($answer!=""){
                return $status = self::getsecondfa()->check($userid, $answer, $sfa_secret);
            }else{
                $output = new secondfaConnectorResult();
                $output->status = $output::STATUS_NOTCHECKED;
                $output->output = "UNKNOWN";
                return $output;
            }
        }else{
            $output = new secondfaConnectorResult();
            $output->status = $output::STATUS_NOTCHECKED;
            $output->output = "UNKNOWN";
            return $output;
        }
    }
}

interface secondfaConnector {
    public function check($userid, $answer, $sfa_secret);

    public function generateSecret($userid, $company, $email);

    public function getConfigFields();

    public function getName();
}

class secondfaConnectorResult {
    public $status;

    public $output;

    const STATUS_OK = 'OK';
    const STATUS_FAIL = 'FAIL';
    const STATUS_NOTCHECKED = 'COULD NOT BE CHECKED';
}

foreach (secondfaApp::$ServiceNames as $serviceName)
    require_once strtolower($serviceName) . '.php';
