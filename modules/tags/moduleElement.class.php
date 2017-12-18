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
require_once 'eclasstag.class.php';
class ModuleElement {

    private $course_id;
    private $module_id;
    private $element_id;
    private $user_id;

    public function __construct($element_id) {
        global $course_id, $uid;
        $this->course_id = $course_id;
        $this->module_id = current_module_id();
        $this->element_id = $element_id;
        $this->user_id = $uid;
    }

    public function getTags() {
        $attached_tags = Database::get()->queryArray("SELECT `tag`.`id` AS id, `tag`.`name` AS name "
                . "FROM `tag_element_module`, `tag` "
                . "WHERE `tag_element_module`.`element_id` = ?d "
                . "AND `tag_element_module`.`module_id` = ?d "
                . "AND `tag_element_module`.`tag_id` = `tag`.`id`", $this->element_id, $this->module_id);
        $tags = array();
        if ($attached_tags) {
            foreach ($attached_tags as $attached_tag){
                $tags[$attached_tag->id] = $attached_tag->name;
            }
        }
        return $tags;
    }
    public function showTags() {
        global $course_code;
        $tags_array = $this->getTags();
        $total_tags = count($tags_array);
        $tag_list = '';
        $i=1;
        foreach($tags_array as $tag){
            $tag_list .= "<a href='../../modules/tags/?course=".$course_code."&amp;tag=".urlencode($tag)."'>".q($tag)."</a> ";
            if ($i !== $total_tags) $tag_list .= ', ';
            $i++;
        }
        return $tag_list;
    }
    public function attachTags($tagsArray) {
        foreach ($tagsArray as $tag_name){
            $tag = new eClassTag($tag_name);
            $tag_id = $tag->findOrCreate();
            if($tag_id){
                Database::get()->query("INSERT INTO `tag_element_module` (`course_id`, `module_id`, `element_id`, `user_id`, `date`, `tag_id`) VALUES (?d, ?d, ?d, ?d, NOW(), ?d)", $this->course_id, $this->module_id, $this->element_id, $this->user_id, $tag_id);
            }
        }
    }
    public function detachTags($tagsArray) {
        foreach ($tagsArray as $tag_name){
            $tag = new eClassTag($tag_name);
            $tag_id = $tag->findOrCreate();
            if ($tag_id) {
                Database::get()->query("DELETE FROM `tag_element_module` WHERE `course_id` = ?d AND `module_id` = ?d AND `element_id` = ?d AND `tag_id` = ?d", $this->course_id, $this->module_id, $this->element_id, $tag_id);
            }
        }
        Database::get()->query('DELETE FROM tag WHERE id NOT IN (SELECT DISTINCT tag_id FROM tag_element_module)');
    }
    public function syncTags($tagsArray) {
        $attached_tags = $this->getTags();
        $to_be_attached = array_diff($tagsArray, $attached_tags);
        $to_be_detached = array_diff($attached_tags, $tagsArray);
        $this->attachTags($to_be_attached);
        $this->detachTags($to_be_detached);
    }
}
