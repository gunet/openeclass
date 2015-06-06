<?php

/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2012  Greek Universities Network - GUnet
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

$path = realpath(dirname(__FILE__));
require_once $path . '/../../config/config.php';
require_once $path . '/../../modules/admin/debug.php';
require_once $path . '/../db/database.php';
require_once $path . '/../admin/extconfig/externals.php';
foreach (CloudDriveManager::$DRIVENAMES as $driveName)
    require_once 'plugins/' . strtolower($driveName) . '.php';

load_js('filetree');

final class CloudDriveManager {

    public static $DRIVENAMES = array("GoogleDrive", "OneDrive", "Dropbox", "OwnCloud", "WebDAV", "FTP");

    const DRIVE = "clouddrive";
    const FILEPENDING = "pendingcloud";
    const CALLBACK = "callbackcloud";

    private static $DRIVES = null;

    /**
     * @return CloudDrive
     */
    public static function getValidDrives() {
        if (CloudDriveManager::$DRIVES == null) {
            $drives = array();
            foreach (CloudDriveManager::$DRIVENAMES as $driveName) {
                $drive = new $driveName();
                if ($drive->isPresent())
                    $drives[$drive->getName()] = $drive;
            }
            CloudDriveManager::$DRIVES = $drives;
        }
        return CloudDriveManager::$DRIVES;
    }

    public static function renderAsButtons() {
        global $langPathUploadFile;
        $result = "
<script>
    function authorizeDrive(driveType) {
        win = window.open('../drives/popup.php?" . CloudDriveManager::CALLBACK . "=' + encodeURIComponent(window.location.href) + '&" . CloudDriveManager::DRIVE . "=' + driveType, 'Connecting... ' ,'height=600,width=400,scrollbars=yes');
        var timer = setInterval(function() {   
            if(win.closed) {  
                clearInterval(timer);
                window.location.reload();
            }
        }, 1000);
    }    
    function callback(file) {
        window.location.href = window.location.href + '&" . CloudDriveManager::FILEPENDING . "=' + encodeURIComponent(file);
    }
    $(document).ready(function(){
        $('#tree_container').on('show.bs.modal', function (event) {
            var source = $(event.relatedTarget); 
            $('#fileTreeDemo').fileTree({root: '/', script: '../drives/fileprovider.php?' + source.data('drive') , loadMessage: 'Please wait...'}, function (file) {
                $('tree_container').modal('hide');
                callback(file);
            });
        });
    });
</script>
<div class='modal fade' id='tree_container' tabindex='-1' role='dialog' aria-labelledby='myModalLabel' aria-hidden='true'>
  <div class='modal-dialog'>
    <div class='modal-content'>
      <div class='modal-header'>
        <button type='button' class='close' data-dismiss='modal' aria-label='Close'><span aria-hidden='true'>&times;</span></button>
        <h4 class='modal-title' id='myModalLabel'>" . $langPathUploadFile . "</h4>
      </div>
      <div class='modal-body' style=' overflow:auto;'>
        <div id='fileTreeDemo' class='browsearea' ></div>
      </div>      
    </div>
  </div>
</div>";
        //href=\"../drives/filebrowser.php?" . $drive->getDriveDefaultParameter() . "\"
        foreach (CloudDriveManager::getValidDrives() as $drive) {
            if ($drive->isAuthorized()) {
                $result .="<a class='btn btn-default vagelis' href='javascript:void(0);'  data-toggle=\"modal\" data-target=\"#tree_container\" data-drive=\"" . $drive->getDriveDefaultParameter() . "\"><i class='fa fa-file space-after-icon'/></i>" . $drive->getDisplayName() . "</a> \n";
            } else {
                $result .="<a class='btn btn-default' href=\"javascript:void(0)\" onclick=\"authorizeDrive('" . $drive->getName() . "')\"><i class='fa fa-plug space-after-icon'></i>" . $drive->getDisplayName() . "</a> \n";
            }
        }
        return "\n" . $result;
    }

    public static function getSessionDrive() {
        return CloudDriveManager::getDrive($drive_name = isset($_GET[CloudDriveManager::DRIVE]) ? $_GET[CloudDriveManager::DRIVE] : null);
    }

    public static function getDrive($drivename) {
        if ($drivename == null) {
            die("Error while retrieving cloud connectivity");
        }
        $drives = CloudDriveManager::getValidDrives();
        return $drives[$drivename];
    }

    public static function getFileUploadPending() {
        return isset($_GET[CloudDriveManager::FILEPENDING]) ? $_GET[CloudDriveManager::FILEPENDING] : null;
    }

}

abstract class CloudDrive {

    /**
     * @var ExtApp 
     */
    private $extapp;

    public function getName() {
        return strtolower(str_replace(' ', '', $this->getDisplayName()));
    }

    public function getDriveDefaultParameter() {
        return CloudDriveManager::DRIVE . "=" . $this->getName();
    }

    protected function getExtApp() {
        if (!$this->extapp) {
            $this->extapp = ExtAppManager::getApp($this->getName());
        }
        return $this->extapp;
    }

    protected function downloadToFile($url, $filename, $post = null, $credentials = null) {
        try {
            $fout = fopen($filename, "w+b");
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            if ($post)
                curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
            if ($credentials)
                curl_setopt($ch, CURLOPT_USERPWD, $credentials);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_MAXREDIRS, 20);
            curl_setopt($ch, CURLOPT_FILE, $fout);
            $result = curl_exec($ch);
            curl_close($ch);
            fclose($fout);
        } catch (Exception $ex) {
            return CloudDriveResponse::FILE_NOT_SAVED;
        }
        if ($result)
            return CloudDriveResponse::OK;
        return CloudDriveResponse::FILE_NOT_FOUND;
    }

    protected function downloadToOutput($url, $post = null) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        if ($post)
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 20);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

    public function isPresent() {
        $extApp = $this->getExtApp();
        return $extApp != null && $extApp->getParam(ExtApp::ENABLED) && strcmp($extApp->getParam(ExtApp::ENABLED)->value(), "1") == 0;
    }

    public function getCallbackName() {
        return "code";
    }

    public function getCallbackToken() {
        $name = $this->getCallbackName();
        return isset($_GET[$name]) ? $_GET[$name] : null;
    }

    protected function getAuthorizeName() {
        return $this->getName() . "_session_authorize";
    }

    protected function setAuthorizeToken($code) {
        $_SESSION[$this->getAuthorizeName()] = $code;
    }

    public function getAuthorizeToken() {
        $name = $this->getAuthorizeName();
        return isset($_SESSION[$name]) ? $_SESSION[$name] : null;
    }

    public abstract function store($cloudfile, $path);

    public abstract function getDisplayName();

    public abstract function isAuthorized();

    public abstract function getAuthURL();

    public abstract function authorize($callbackToken);

    public abstract function getFiles($dir);
}

final class CloudFile {

    private $name;
    private $id;
    private $isFolder;
    private $size;
    private $drivename;

    public function __construct($name, $id, $isFolder, $size, $drivename) {
        $this->name = $name;
        $this->id = $id;
        $this->isFolder = $isFolder;
        $this->size = $size;
        $this->drivename = $drivename;
    }

    public static function fromJSON($json) {
        $values = json_decode($json);
        return new CloudFile($values->name, $values->id, false, $values->size, $values->drivename);
    }

    public function isFolder() {
        return $this->isFolder;
    }

    public function name() {
        return $this->name;
    }

    public function id() {
        return $this->id;
    }

    public function size() {
        return $this->size;
    }

    public function drive() {
        return CloudDriveManager::getDrive($this->drivename);
    }

    public function storeToLocalFile($file) {
        return $this->drive()->store($this, $file);
    }

    public function toJSON() {
        return json_encode(array('name' => $this->name, 'id' => $this->id, 'size' => $this->size, 'drivename' => $this->drivename));
    }

}

class CloudDriveResponse {

    const OK = 0;
    const FILE_NOT_FOUND = 1;
    const FILE_NOT_SAVED = 2;
    const AUTHORIZATION_ERROR = 3;

}
