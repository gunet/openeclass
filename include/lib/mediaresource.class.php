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

require_once 'include/lib/mediaresource.interface.php';

class MediaResource implements MediaResourceInterface {

    private $id;
    private $courseId;
    private $title;
    private $path;
    private $url;
    private $accessURL;
    private $playURL;

    public function __construct($id, $courseId, $title, $path, $url, $accessURL, $playURL) {
        $this->id = $id;
        $this->courseId = $courseId;
        $this->title = $title;
        $this->path = $path;
        $this->url = $url;
        $this->accessURL = $accessURL;
        $this->playURL = $playURL;
    }

    // Getters and Setters

    public function getId() {
        return $this->id;
    }

    public function getCourseId() {
        return $this->courseId;
    }

    public function getTitle() {
        return $this->title;
    }

    public function getPath() {
        return $this->path;
    }

    public function getUrl() {
        return $this->url;
    }

    public function getAccessURL() {
        return $this->accessURL;
    }

    public function setAccessURL($accessURL) {
        $this->accessURL = $accessURL;
    }

    public function getPlayURL() {
        return $this->playURL;
    }

    public function setPlayURL($playURL) {
        $this->playURL = $playURL;
    }

}
