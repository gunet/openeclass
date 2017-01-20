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

require_once 'unplag/unplag.php';

abstract class Plagiarism {

    private static $current;

    /**
     * Get default plagiarism check tool
     * @return Plagiarism
     */
    public static function get() {
        if (!Plagiarism::$current)
            self::$current = new UnPlag ();
        return self::$current;
    }

    /**
     * Check if an eclass file has already been submitted
     * @param ID $fileID The eclass file id
     */
    public abstract function isFileSubmitted($fileID);

    /**
     * Register a local file with local ID;
     * @param ID $fileID the eclass-specific file id
     * @param string $fileLocation the eclass-specific file location
     * @param string $filename the name of the file
     * @return boolean true, if registration is successful
     */
    public abstract function submitFile($fileID, $fileLocation, $filename = null);

    /**
     * Get results of a previously submitted file.
     * @param string $fileID The file to check upon
     * @return PlagiarismResult Check result. Might be null
     */
    public abstract function getResults($fileID);

    protected function getFileID($fileID) {
        $res = Database::get()->querySingle("SELECT remote_file_id FROM ext_plag_connection WHERE type = ?d AND file_id = ?d", $this->getType(), $fileID);
        return $res ? $res->remote_file_id : null;
    }

    protected function getSubmissionID($fileID) {
        $res = Database::get()->querySingle("SELECT submission_id FROM ext_plag_connection WHERE type = ?d AND file_id = ?d", $this->getType(), $fileID);
        return $res ? $res->submission_id : null;
    }

    protected function createRemoteFileID($fileID, $remoteFileID) {
        Database::get()->query("INSERT INTO ext_plag_connection (file_id, type, remote_file_id) VALUES (?d, ?d, ?d)", $fileID, $this->getType(), $remoteFileID);
    }

    protected function createSubmission($fileID, $submissionID) {
        Database::get()->query("UPDATE ext_plag_connection SET submission_id = ?d WHERE type = ?d AND file_id = ?d", $submissionID, $this->getType(), $fileID);
    }

    protected abstract function getType();
}

class PlagiarismResult {

    /**
     * @var float Value could be between 0 and 1
     */
    public $progress;
    /*
     * @var boolean Could be true (process has finished successfully) or false
     */
    public $ready;

    /**
     * @var string The URL of the plagiarism detection
     */
    public $resultURL;

    /**
     * @var string The location of the PDF file of the result; the PDF might be available for a specific time only.
     */
    public $pdfURL;

    public function __construct($progress, $resultURL) {
        $this->progress = $progress;
        $this->resultURL = $resultURL;
    }

}
