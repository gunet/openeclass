<?php

/* ========================================================================
 * Open eClass 3.7
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2018  Greek Universities Network - GUnet
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

/**
  @file class.wikipage.php
  @author: Frederic Minne <zefredz@gmail.com>
           Open eClass Team <eclass@gunet.gr>
 */

define("PAGE_NO_TITLE_ERROR", "Missing title");
define("PAGE_ALREADY_EXISTS_ERROR", "Page already exists");
define("PAGE_CANNOT_BE_UPDATED_ERROR", "Page cannot be updated");
define("PAGE_NOT_FOUND_ERROR", "Page not found");


/**
 * This class represents page of a Wiki
 */
class WikiPage {

    // public fields
    var $pageId = 0;            // attr_reader:
    var $title = '';            // attr_accessor:
    var $content = '';          // attr_accessor:
    var $ownerId = 0;           // attr_accessor:
    var $creationTime = '';     // attr_reader:
    var $lastEditorId = 0;      // attr_accessor:
    var $lastEditTime = '';     // attr_reader:
    var $lastVersionId = 0;     // attr_reader:
    var $wikiId = 0;            // attr_reader:
    var $currentVersionMtime = '0000-00-00 00:00:00'; // attr_reader:
    var $currentVersionEditorId = 0; // attr_reader:

    // error handling
    var $error = '';

    /**
     * Constructor
     * @param DatabaseConnection con connection to the database
     * @param array config associative array containing tables name
     */
    public function __construct($wikiId = 0) {
        $this->wikiId = $wikiId;
    }

    // public methods

    /**
     * Edit an existing page
     * @param int editorId ID of the user who edits the page
     * @param string content page content
     * @param string mtime modification time YYYY-MM-DD hh:mm:ss
     * @param boolean auto_save save automaticaly the modification
     *      to database if set to true (default false)
     * @return boolean true on success, false on failure
     */
    function edit($editorId, $content = '', $changelog = '', $mtime = '', $auto_save = false) {
        if (( $auto_save === true ) && (!$this->pageExists($this->getTitle()) )) {
            $this->setError(PAGE_NOT_FOUND_ERROR);
            return false;
        } else if (( $auto_save === false ) && ( $this->getTitle() === '' )) {
            $this->setError(PAGE_NO_TITLE_ERROR);
            return false;
        } else {
            $this->setEditorId($editorId);
            $this->setLastEditTime($mtime);
            $this->setContent($content);
            if ($auto_save === true) {
                return $this->save($changelog);
            } else {
                return true;
            }
        }
    }

    /**
     * Create a new page
     * @param int ownerId ID of the user who creates the page
     * @param string title title of the page
     * @param string content page content
     * @param string ctime creation time YYYY-MM-DD hh:mm:ss
     * @param boolean auto_save save automaticaly the page
     *      to database if set to true (default false)
     * @return boolean true on success, false on failure
     */
    function create($ownerId, $title, $content = '', $changelog = '', $ctime = '', $auto_save = false) {
        if (!$title) {
            $this->setError(PAGE_NO_TITLE_ERROR);
            return false;
        } else {
            if (( $auto_save === true ) && ( $this->pageExists($title) )) {
                $this->setError(PAGE_ALREADY_EXISTS_ERROR);
                return false;
            } else {
                $this->setOwnerId($ownerId);
                $this->setTitle($title);
                $this->setContent($content);
                $this->setCreationTime($ctime);
                $this->setEditorId($ownerId);
                $this->setLastEditTime($ctime);

                if ($auto_save === true) {
                    return $this->save($changelog);
                } else {
                    return true;
                }
            }
        }
    }

    /**
     * Delete the page
     * @return boolean true on success, false on failure
     */
    function delete() {
        // (OPT) backup last version
        // 1st delete page info
        $sql = "DELETE FROM `wiki_pages` "
                . "WHERE `id` = ?d"
        ;

        $that = $this;
        Database::get()->query($sql, function ($errormsg) use ($that) {
                    $that->setError($errormsg);
                }, $this->getPageId());

        if (!$this->hasError()) {
            // 2nd delete page versions
            $sql = "DELETE FROM `wiki_pages_content` "
                    . "WHERE `pid` = ?d"
            ;

            $that = $this;
            Database::get()->query($sql, function ($errormsg) use ($that) {
                    $that->setError($errormsg);
                }, $this->getPageId());

            $this->_setPageId(0);
            $this->_setLastVersionId(0);
            return (!$this->hasError());
        } else {
            return false;
        }
    }

    /**
     * Save the page
     * @return boolean true on success, false on failure
     */
    function save($changelog = '') {

        if ($this->getCreationTime() === '') {
            $this->setCreationTime(date("Y-m-d H:i:s"));
        }

        if ($this->getLastEditTime() === '') {
            $this->setLastEditTime(date("Y-m-d H:i:s"));
        }

        if ($this->getPageId() === 0) {
            if ($this->pageExists($this->getTitle())) {
                $this->setError(PAGE_ALREADY_EXISTS_ERROR, PAGE_ALREADY_EXISTS_ERRNO);
                return false;
            } else {
                // insert new page
                // 1st insert page info
                $sql = "INSERT INTO `wiki_pages`"
                        . "(`wiki_id`, `owner_id`,`title`,`ctime`, `last_mtime`) "
                        . "VALUES(?d,?d,?s,?t,?t)"
                ;

                $that = $this;
                $result = Database::get()->query($sql, function ($errormsg) use ($that) {
                    $that->setError($errormsg);
                }, $this->getWikiId(), $this->getOwnerId(), $this->getTitle(), $this->getCreationTime(), $this->getLastEditTime());

                // 2nd update pageId
                $pageId = $result->lastInsertID;
                $this->_setPageId($pageId);

                // 3rd update version
                return $this->_updateVersion($changelog);
            }
        } else {
            // update version
            return $this->_updateVersion($changelog);
        }
    }

    /**
     * Get page version history
     * @return array page history on success, null on failure
     */
    function history($offset = 0, $limit = 0, $order = 'DESC') {

        $limit = ($limit == 0 && $offset == 0) ? "" : "LIMIT " . $offset . "," . $limit . " ";

        $order = ($order === 'ASC') ? " ORDER BY `id` ASC " : " ORDER BY `id` DESC ";
        // retreive versionId and editorId and mtime for each version
        // of the page

        $sql = "SELECT `id`, `editor_id`, `mtime`, `changelog` "
                . "FROM `wiki_pages_content` "
                . "WHERE `pid` = ?d"
                . $order
                . $limit
        ;

        $result = Database::get()->queryArray($sql, $this->getPageId());

        if (is_array($result)) {
            return $result;
        } else {
            return null;
        }
    }

    /**
     * Check if a page exists in the wiki
     * @param string title page title
     * @return boolean true on success, false on failure
     */
    function pageExists($title) {

        $sql = "SELECT COUNT(`id`) as `c` "
                . "FROM `wiki_pages` "
                . "WHERE BINARY `title` = ?s "
                . "AND `wiki_id` = ?d"
        ;

        $result = Database::get()->querySingle($sql, $title, $this->getWikiId());
        if($result->c > 0) {
            return true;
        } else {
            return false;
        }

    }

    // public factory methods

    /**
     * Load a page using its title
     * @param string title title of the page
     * @return boolean true on success, false on failure
     */
    function loadPage($title) {
        // retreive page (last version)
        $sql = "SELECT p.`id`, p.`owner_id`, p.`title`, "
                . "p.`ctime`, p.`last_version`, p.`last_mtime`, "
                . "c.`editor_id`, c.`content` "
                . "FROM `wiki_pages` p"
                . ", `wiki_pages_content` c "
                . "WHERE BINARY p.`title` = ?s "
                . "AND c.`id` = p.`last_version` "
                . "AND `wiki_id` = ?d"
        ;

        $params = array($title, $this->getWikiId());

        return $this->_updatePageFields($sql, 'loadPage', $params);
    }

    /**
     * Load a given version of a page using its title
     * @param int versionId ID of the version
     * @return boolean true on success, false on failure
     */
    function loadPageVersion($versionId) {
        // retreive page (given version)
        $sql = "SELECT p.`id`, p.`owner_id`, p.`title`, "
                . "p.`ctime`, p.`last_version`, p.`last_mtime`, "
                . "c.`editor_id`, c.`content`, c.`mtime` AS `current_mtime`, c.`id` AS `current_version` "
                . "FROM `wiki_pages` p, "
                . "`wiki_pages_content` c "
                . "WHERE c.`id` = ?d "
                . "AND p.`id` = c.`pid`"
        ;

        $params = array($versionId);

        if ($this->_updatePageFields($sql, 'loadPageVersion', $params)) {
            $this->_setCurrentVersionId($versionId);
            return true;
        } else {
            return false;
        }
    }

    /**
     * Load a page using its ID
     * @param int pageId ID of the page
     */
    function loadPageById($pageId) {

        // retreive page (last version)
        $sql = "SELECT p.`id`, p.`owner_id`, p.`title`, "
                . "p.`ctime`, p.`last_version`, p.`last_mtime`, "
                . "c.`editor_id`, c.`content` "
                . "FROM `wiki_pages` p,"
                . " `wiki_pages_content` c "
                . "WHERE p.`id` = ?d "
                . "AND c.`id` = p.`last_version`"
        ;

        $params = array($pageId);
        return $this->_updatePageFields($sql, 'loadPageById', $params);
    }

    /**
     * Restore a given version of the page
     * @param int editorId ID of the user who restores the page
     * @param int versionId ID of the version to restore
     */
    function restoreVersion($editorId, $versionId) {
        $this->loadPageVersion($versionId);
        $this->edit($editorId, $this->getContent(), date("Y-m-d H:i:s"), true);
    }

    // private methods

    /**
     * Update a page
     * @access private
     * @return boolean true on success, false on failure
     */
    function _updateVersion($changelog = '') {
        // 1st insert page content
        $sql = "INSERT INTO `wiki_pages_content`"
                . "(`pid`,`editor_id`,`mtime`, `content`, `changelog`) "
                . "VALUES(?d,?d,?t,?s,?s)";

        $that = $this;
        $result = Database::get()->query($sql, function ($errormsg) use ($that) {
                    $that->setError($errormsg);
                }, $this->getPageId(), $this->getEditorId(), $this->getLastEditTime(), $this->getContent(), $changelog);

        // update last version id
        $lastVersionId = $result->lastInsertID;

        $this->_setLastVersionId($lastVersionId);
        $this->_setCurrentVersionId($lastVersionId);

        // 2nd update page info
        $sql = "UPDATE `wiki_pages` "
                . "SET `last_version` = ?d ,"
                . "`last_mtime` = ?t "
                . "WHERE `id` = ?d"
        ;

        Database::get()->query($sql, function ($errormsg) use ($that) {
                $that->setError($errormsg);
            }, $this->getLastVersionId(), $this->getLastEditTime(), $this->getPageId());

        return !$this->hasError();
    }

    /**
     * Update the fields of the page
     * @access private
     * @param string sql SQL query
     * @return boolean true on success, false on failure
     */
    function _updatePageFields($sql, $orig, $params) {

        $that = $this;

        if ($orig == 'loadPage') {
            $page = Database::get()->querySingle($sql, function ($errormsg) use ($that) {
                    $that->setError($errormsg);
                }, $params[0], $params[1]);
        } elseif ($orig == 'loadPageVersion' || $orig == 'loadPageById') {
            $page = Database::get()->querySingle($sql, function ($errormsg) use ($that) {
            	    $that->setError($errormsg);
                }, $params[0]);
        }

        if (is_object($page)) {
            $this->_setPageId($page->id);
            $this->setOwnerId($page->owner_id);
            $this->setTitle($page->title);
            $this->_setLastVersionId($page->last_version);
            $this->_setCurrentVersionId($page->last_version);
            $this->setCreationTime($page->ctime);
            $this->setLastEditTime($page->last_mtime);
            $this->setEditorId($page->editor_id);
            $this->setContent($page->content);

            $this->currentVersionId = ( isset($page->current_version) ) ? $page->current_version : $page->last_version
            ;

            $this->currentVersionMtime = ( isset($page->current_mtime) ) ? $page->current_mtime : $page->last_mtime
            ;

            return $this;
        } else {
            if (!$this->hasError()) {
                $this->setError(PAGE_CANNOT_BE_UPDATED_ERROR);
            }
            return null;
        }
    }

    // error handling

    function setError($errmsg = '') {
        $this->error = ($errmsg != '') ? $errmsg : "Unknown error";
    }

    function getError() {
         if ($this->error != '') {
            $error = $this->error;
            $this->error = '';
            return $error;
        } else {
            return false;
        }
    }

    function hasError() {
        return ($this->error != '');
    }

    // public accessors

    function setTitle($title) {
        $this->title = $title;
    }

    function setContent($content) {
        $this->content = $content;
    }

    function setEditorId($editorId) {
        $this->lastEditorId = $editorId;
    }

    function setLastEditTime($mtime = '') {
        $this->lastEditTime = ($mtime == '') ? date("Y-m-d H:i:s") : $mtime;
    }

    function setOwnerId($ownerId) {
        $this->ownerId = $ownerId;
    }

    function setCreationTime($ctime = '') {
        $this->creationTime = ($ctime == '') ? date("Y-m-d H:i:s") : $ctime;
    }

    function getWikiId() {
        return $this->wikiId;
    }

    function getTitle() {
        return $this->title;
    }

    function getContent() {
        return $this->content;
    }

    function getEditorId() {
        return $this->lastEditorId;
    }

    function getOwnerId() {
        return $this->ownerId;
    }

    function getLastEditTime() {
        return $this->lastEditTime;
    }

    function getCreationTime() {
        return $this->creationTime;
    }

    function getLastVersionId() {
        return $this->lastVersionId;
    }

    function getCurrentVersionId() {
        return $this->currentVersionId;
    }

    function getCurrentVersionMtime() {
        return $this->currentVersionMtime;
    }

    function getPageId() {
        return $this->pageId;
    }
    
    function _setPageId($pageId) {
        $this->pageId = $pageId;
    }

    function _setLastVersionId($lastVersionId) {
        $this->lastVersionId = $lastVersionId;
    }

    function _setCurrentVersionId($currentVersionId) {
        $this->currentVersionId = $currentVersionId;
    }

    // static methods
    function stripSlashesForWiki($str) {

        return str_replace('\\', "\\", str_replace('\"', '"', $str));

    }

}