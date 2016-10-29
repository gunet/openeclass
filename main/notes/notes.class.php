<?php

/* ========================================================================
 * Open eClass 
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2014  Greek Universities Network - GUnet
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
 * ======================================================================== 
 */

/**
 * Eclass notes manipulation library as a common base for various modules
 *
 * @version 1.0
 * @absract
 * This class mainly contains static methods, so it could be defined simply
 * as a namespace.
 * However, it is created as a class for a possible need of instantiation of 
 * note objects in the future. Another scenario could be the creation
 * of a set of abstract methods to be implemented seperatelly per module.
 *  
 */

require_once 'include/log.class.php';
require_once 'modules/search/indexer.class.php';

class Notes {
    
    /********  Basic set of functions to be called from inside **************
     *********** notes module that manipulate note items ********************/
    
    /**
     * Get note details given the note id
     * @param int $noteid id in table note
     * @return array note tuple 
     */
    public static function get_note($noteid){
        global $uid;
        return Database::get()->querySingle("SELECT * FROM note WHERE id = ?d AND user_id = ?d", $noteid, $uid);
    }
    
    /**
     * Get notes with details for a given user
     * @param int $user_id if empty the session user is assumed
     * @return array of user notes with details 
     */
    public static function get_user_notes($user_id = NULL){
        global $uid;
        if(is_null($user_id)){
            $user_id = $uid;
        }
        return Database::get()->queryArray("SELECT * FROM note WHERE user_id = ?d  ORDER BY `order` DESC", $user_id);
    }
    
    /**
     * Get note count for a given user
     * @param int $user_id if empty the session user is assumed
     * @return int 
     */
    public static function count_user_notes($user_id = NULL){
        global $uid;
        if(is_null($user_id)){
            $user_id = $uid;
        }
        return Database::get()->querySingle("SELECT COUNT(*) AS count FROM note WHERE user_id = ?d", $user_id)->count;
    }
    
    
    /**
     * Inserts new note and logs the action
     * @param string $title note title
     * @param text $content note body
     * @param string $reference_obj_id refernced object by note containing object type (from $ref_object_types) and object id (is in the corresponding db table), e.g., video_link:5  
     * @return int $noteid which is the id of the new note
     */
    public static function add_note($title, $content, $reference_obj_id = NULL){
        global $uid;
        $refobjinfo = References::get_ref_obj_field_values($reference_obj_id);
        $orderMax = Database::get()->querySingle("SELECT MAX(`order`) AS maxorder FROM note
                                               WHERE user_id = ?d", $uid)->maxorder;
        $order = $orderMax + 1;
        // insert
        $noteid = Database::get()->query("INSERT INTO note
                                     SET content = ?s,
                                         title = ?s,
                                         user_id = ?d, 
                                         `order` = ?d,
                                         reference_obj_module = ?d,
                                         reference_obj_type = ?s,
                                         reference_obj_id = ?d,
                                         reference_obj_course = ?d", purify($content), $title, $uid, $order, $refobjinfo['objmodule'], $refobjinfo['objtype'], $refobjinfo['objid'], $refobjinfo['objcourse'])->lastInsertID;
        Indexer::queueAsync(Indexer::REQUEST_STORE, Indexer::RESOURCE_NOTE, $noteid);
        Log::record(0, MODULE_ID_NOTES, LOG_INSERT, array('user_id' => $uid, 'id' => $noteid,
        'title' => $title,
        'content' => ellipsize_html(canonicalize_whitespace(strip_tags($content)), 50, '+')));
        return $noteid;
    }
    
    /**
     * Update existing note and logs the action
     * @param int $noteid id in table note
     * @param string $title note title
     * @param text $content note body
     * @param string $reference_obj_id refernced object by note. It contains the object type (from $ref_object_types) and object id (id in the corresponding db table), e.g., video_link:5  
     */
    public static function update_note($noteid, $title, $content, $reference_obj_id = NULL){
        global $uid;
        $refobjinfo = References::get_ref_obj_field_values($reference_obj_id);
        Database::get()->query("UPDATE note SET title = ?s, content = ?s, reference_obj_module = ?d, reference_obj_type = ?s, reference_obj_id = ?d, reference_obj_course = ?d WHERE id = ?d", $title, purify($content), $refobjinfo['objmodule'], $refobjinfo['objtype'], $refobjinfo['objid'], $refobjinfo['objcourse'], $noteid);
        Indexer::queueAsync(Indexer::REQUEST_STORE, Indexer::RESOURCE_NOTE, $noteid);
        Log::record(0, MODULE_ID_NOTES, LOG_MODIFY, array('user_id' => $uid, 'id' => $noteid,
        'title' => $title,
        'content' => ellipsize_html(canonicalize_whitespace(strip_tags($content)), 50, '+')));
    }
    
    /**
     * Moves up a note in the notes presentation order 
     * @param int $noteid id in table note
     */
    public static function moveup_note($noteid){
        global $uid;
        $log_type = LOG_MODIFY;
        $thisorder = Database::get()->querySingle("SELECT `order` FROM note WHERE id=?d", $noteid);
        $swapnote = Database::get()->querySingle("SELECT id, `order` FROM note WHERE user_id = ?d AND `order` > ?d ORDER BY `order` LIMIT 1", $uid, $thisorder);
        if($swapnote){
            Database::get()->query("UPDATE note SET `order` = ?d WHERE id = ?d", $swapnote->order, $noteid);
            Database::get()->query("UPDATE note SET `order` = ?d WHERE id = ?d", $thisorder, $swapnote->id);
        }
        
    }
    
    /**
     * Moves down a note in the notes presentation order 
     * @param int $noteid id in table note
     */
    public static function movedown_note($noteid){
        global $uid;
        $log_type = LOG_MODIFY;
        $thisorder = Database::get()->querySingle("SELECT `order` FROM note WHERE id=?d", $noteid);
        $swapnote = Database::get()->querySingle("SELECT id, `order` FROM note WHERE  user_id = ?d AND `order` < ?d ORDER BY `order` DESC LIMIT 1", $uid, $thisorder);
        if($swapnote){
            Database::get()->query("UPDATE note SET `order` = ?d WHERE id = ?d", $swapnote->order, $noteid);
            Database::get()->query("UPDATE note SET `order` = ?d WHERE id = ?d", $thisorder, $swapnote->id);
        }
    }
    
    /**
     * Deletes an existing note and logs the action 
     * @param int $noteid id in table note
     */
    public static function delete_note($noteid){
        global $uid;
        $note = Database::get()->querySingle("SELECT title, content FROM note WHERE id = ?d ", $noteid);
        $content = ellipsize_html(canonicalize_whitespace(strip_tags($note->content)), 50, '+');
        Database::get()->query("DELETE FROM note WHERE id = ?d", $noteid);
        Indexer::queueAsync(Indexer::REQUEST_REMOVE, Indexer::RESOURCE_NOTE, $noteid);
        Log::record(0, MODULE_ID_NOTES, LOG_DELETE, array('user_id' => $uid, 'id' => $noteid,
            'title' => $note->title,
            'content' => $content));
    }
    
    /**
     * Delete all notes of a given user and logs the action
     * @param int $user_id if empty the session user is assumed
     */
    public static function delete_all_notes($user_id = NULL){
        global $uid;
        Database::get()->query("DELETE FROM note WHERE user_id = ?d", $uid);
        Indexer::queueAsync(Indexer::REQUEST_REMOVEBYUSER, Indexer::RESOURCE_NOTE, $uid);
        Log::record(0, MODULE_ID_NOTES, LOG_DELETE, array('user_id' => $uid, 'id' => 'all'));
    }
    
    /**************************************************************************/
    /*
     * Set of functions to be called from modules other than notes
     * in order to associate notes with module specific items
     */
    
    
    /** 
     * Get notes generally associated with a course. If no course is defined the current course is assumed.
     * @param int $cid the course id
     * @return array of notes 
     */
    public static function get_general_course_notes($cid = NULL){
       global $uid, $course_id;
       if(is_null($cid)){
           $cid = $course_id;
       }
       return Database::get()->queryArray("SELECT * FROM note WHERE user_id = ?d AND reference_obj_type = 'course' AND reference_obj_id = ?d", $uid, $cid);
    }
    
    /** Get notes associated with a course generally or with specific items of the course
     * @param int $cid the course id
     * @return array array of notes 
     */
    public static function get_all_course_notes($cid = NULL){
       global $uid, $course_id;
       if(is_null($cid)){
           $cid = $course_id;
       }
       return Database::get()->queryArray("SELECT * FROM note WHERE user_id = ?d AND reference_obj_course = ?d ", $uid, $cid);
    }
    
    /** 
     * Get notes associated with items of a specific module of a course. If course is not specified the current one is assumed. If module is not specified the whole course is assumed. 
     * @param int $module_id the id of the module
     * @param int $cid the course id
     * @return array of notes
     */
    public static function get_module_notes($cid = NULL, $module_id = NULL){
       global $uid, $course_id;
       if(is_null($cid)){
           $cid = $course_id;
       }
       if(is_null($module_id)){
           return self::get_all_course_notes($cid);
       }
       return Database::get()->queryArray("SELECT id, title, content FROM note WHERE user_id = ?d AND reference_obj_course = ?d ", $uid, $cid);
    }
    
    /** 
     * Get notes associated with a specific item of a module of a course 
     * If module or course are not specified the current ones are assumed.
     * Item type should be defined in case of a module being associated with more than one 
     * object types (e.g., video module that contains videos and links to videos)
     * @param integer $item_id the item id in the database
     * @param integer $module_id the module id
     * @param integer $course_id the course id
     * @param $item_type string with values: 'course'|'course_ebook'|'course_event'|'personalevent'|'course_assignment'|'course_document'|'course_link'|'course_exercise'|'course_learningpath'|'course_video'|'course_videolink'|'user'
     * @return array array of notes associated with the item
     */
    public static function get_item_notes($item_id, $module_id, $course_id, $item_type){
       global $uid;
       return Database::get()->queryArray("SELECT id, title, content FROM note WHERE user_id = ?d AND reference_obj_course = ?d AND reference_obj_module = ?d AND reference_obj_type = ?s AND reference_obj_id = ?d", $uid, $course_id, $module_id, $item_type, $item_id);
    }
    
     /** 
      * A boolean function to check if some item listed by a module's page is 
      * associated with any notes for the current user.
      * @param integer $item_id the item id in the database
      * @param integer $module_id the module id
      * @param integer $course_id the course id
      * @param $item_type string with values: 'course'|'course_ebook'|'course_event'|'personalevent'|'course_assignment'|'course_document'|'course_link'|'course_exercise'|'course_learningpath'|'course_video'|'course_videolink'|'user'
      * @return boolean true if notes exist for the specified item or false otherwise 
     */
    public static function item_has_notes($item_id, $module_id, $course_id, $item_type){
       return count_item_notes($item_id, $module_id, $course_id, $item_type) > 0;
    }
    
    /** 
      * A function to count the notes associated with some item listed by a module's page, for the current user.
      * @param integer $item_id the item id in the database
      * @param integer $module_id the module id
      * @param integer $course_id the course id
      * @param $item_type string with values: 'course'|'course_ebook'|'course_event'|'personalevent'|'course_assignment'|'course_document'|'course_link'|'course_exercise'|'course_learningpath'|'course_video'|'course_videolink'|'user'
      * @return object with `count` attribute containing the number of associated notes with the item 
     */
    public static function count_item_notes($item_id, $module_id, $course_id, $item_type){
        global $uid;
        return Database::get()->querySingle("SELECT count(*) `count` FROM note WHERE user_id = ?d AND reference_obj_course = ?d AND reference_obj_module = ?d AND reference_obj_type = ?s AND reference_obj_id = ?d", $uid, $course_id, $module_id, $item_type, $item_id);
    }
}
