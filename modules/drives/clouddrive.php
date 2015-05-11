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

final class CloudDriveManager {

    public static $DRIVENAMES = array("GoogleDrive", "OneDrive", "Dropbox");

    const DRIVE = "clouddrive";
    const FILEPENDING = "pendingcloud";

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
        $result = "\n<script src=\"../../js/colorbox/jquery.colorbox.min.js\"></script>
<link rel=\"stylesheet\" href=\"../../js/colorbox/colorbox.css\"/>
<script>
    function authorizeDrive(driveType) {
        win = window.open('../drives/popup.php?" . CloudDriveManager::DRIVE . "=' + driveType, 'Connecting... ' ,'height=600,width=400,scrollbars=yes');
        var timer = setInterval(function() {   
            if(win.closed) {  
                clearInterval(timer);
                window.location.reload();
            }
        }, 1000);
    }
    $(function ()
    {
        $(\".driveconn\").colorbox({iframe:true, innerWidth:424, innerHeight:330});    
    })
    function callback(file) {
        window.location.href = window.location.href + '&" . CloudDriveManager::FILEPENDING . "=' + encodeURIComponent(file);
    }
</script>\n";

        foreach (CloudDriveManager::getValidDrives() as $drive) {
            if ($drive->isAuthorized()) {
                $result .="<a class='btn btn-default driveconn' href=\"../drives/filebrowser.php?" . $drive->getDriveDefaultParameter() . "\"><i class='fa fa-file space-after-icon'/></i>" . $drive->getDisplayName() . "</a> \n";
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

    protected function downloadToFile($url, $filename, $post = null) {
        try {
            $fout = fopen($filename, "w+b");
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            if ($post)
                curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
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
        echo $this->getExtApp();
        return $this->getExtApp() != null;
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
