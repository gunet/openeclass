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

class References {
    /** @const integer a module id for a course generally
     */ 
    const COURSE = -1;
    
    /** @staticvar array Modules of eclass grouped in general and course types. For each module different module item types may exist (e.g., for video module video and video_link) For each item type the corresponding DB table is given and the following fields of this table: the item id, the item title and the course it belongs to
    */ 
    private static $ref_object_types = array(
        'course' => array(
            MODULE_ID_AGENDA => array('course_event' => array('objtable' => 'agenda', 'id_field' => 'id', 'title_field' => 'title', 'course_field' => 'course_id', 'relative_prefix_path' => 'modules/', 'relative_module_path' => 'agenda/', 'course_parameter' => 'course', 'item_id_parameter' => '')),
            MODULE_ID_DOCS => array('course_document' => array('objtable' => 'document', 'id_field' => 'id', 'title_field' => 'filename', 'course_field' => 'course_id', 'relative_prefix_path' => 'modules/', 'relative_module_path' => 'document/', 'course_parameter' => 'course', 'item_id_parameter' => '')),
            MODULE_ID_LINKS => array('course_link' => array('objtable' => 'link', 'id_field' => 'id', 'title_field' => 'title', 'course_field' => 'course_id', 'relative_prefix_path' => 'modules/', 'relative_module_path' => 'link/', 'course_parameter' => 'course', 'item_id_parameter' => 'id')),
            MODULE_ID_VIDEO => array('course_video' => array('objtable' => 'video', 'id_field' => 'id', 'title_field' => 'title', 'course_field' => 'course_id', 'relative_prefix_path' => 'modules/', 'relative_module_path' => 'video/play.php', 'course_parameter' => 'course', 'item_id_parameter' => 'id'),
                                    'course_videolink' => array('objtable' => 'videolink', 'id_field' => 'id', 'title_field' => 'title', 'course_field' => 'course_id', 'relative_prefix_path' => 'modules/', 'relative_module_path' => 'video/playlink.php', 'course_parameter' => 'course', 'item_id_parameter' => 'id')),
            MODULE_ID_ASSIGN => array('course_assignment' => array('objtable' => 'assignment', 'id_field' => 'id', 'title_field' => 'title', 'course_field' => 'course_id', 'relative_prefix_path' => 'modules/', 'relative_module_path' => 'work/', 'course_parameter' => 'course', 'item_id_parameter' => 'id')),
            MODULE_ID_EXERCISE => array('course_exercise' => array('objtable' => 'exercise', 'id_field' => 'id', 'title_field' => 'title', 'course_field' => 'course_id', 'relative_prefix_path' => 'modules/', 'relative_module_path' => 'exercise/', 'course_parameter' => 'course', 'item_id_parameter' => 'exerciseId')),
            MODULE_ID_EBOOK => array('course_ebook' => array('objtable' => 'ebook', 'id_field' => 'id', 'title_field' => 'title', 'course_field' => 'course_id', 'relative_prefix_path' => 'modules/', 'relative_module_path' => 'ebook/', 'course_parameter' => 'course', 'item_id_parameter' => 'id')),
            MODULE_ID_LP => array('course_learningpath' => array('objtable' => 'lp_learnPath', 'id_field' => 'learnPath_id', 'title_field' => 'name', 'course_field' => 'course_id', 'relative_prefix_path' => 'modules/', 'relative_module_path' => 'learnPath/learningPath.php', 'course_parameter' => 'course', 'item_id_parameter' => 'path_id'))),
        'general' => array(
            self::COURSE => array('course' => array('objtable' => 'course', 'id_field' => 'id', 'title_field' => 'title', 'course_field' => 'id', 'relative_prefix_path' => 'courses/', 'relative_module_path' => '%s/', 'course_parameter' => '', 'item_id_parameter' => '' )),
            MODULE_ID_USERS => array('user' => array('objtable' => 'user', 'id_field' => 'id', 'title_field' =>"CONCAT(givenname,' ',surname,' (',username,')')",'relative_prefix_path' => 'main/', 'relative_module_path' => 'profile/profile.php', 'course_parameter' => '', 'item_id_parameter' => 'id' )),
            MODULE_ID_PERSONALCALENDAR => array('personalevent' => array('objtable' => 'personal_calendar', 'id_field' => 'id', 'title_field' => 'title','relative_prefix_path' => 'main/', 'relative_module_path' => 'personal_calendar/', 'course_parameter' => '', 'item_id_parameter' => 'id' ))
        )
    );
    
    /** @staticvar array Language name of the specified modules
     */
    private static $lang_vars = array(
        MODULE_ID_AGENDA => 'langEvent',
        MODULE_ID_DOCS => 'langDocument',
        MODULE_ID_LINKS => 'langLink',
        MODULE_ID_VIDEO => 'langVideo',
        MODULE_ID_ASSIGN => 'langAssignment',
        MODULE_ID_EXERCISE => 'langExercise',
        MODULE_ID_EBOOK => 'langEBook',
        MODULE_ID_USERS => 'langUser',
        MODULE_ID_PERSONALCALENDAR => 'langPersonalEvent',
        MODULE_ID_DOCS => 'langDocument',
        self::COURSE => 'langCourse',
        MODULE_ID_LP => 'langLearningPath'
    );
    
    
     /*************************** References **********************************************************************/
    
    /**
     * Produces the set of select fields to make a reference between two eclass objects
     * @param int $gen_type_selected COURSE|MODULE_ID_USERS|MODULE_ID_PERSONALCALENDAR : the general type of the referenced object 
     * @param int $course_selected the course that the referenced object belongs to
     * @param string $type_selected string with values: 'course'|'course_ebook'|'course_event'|'personalevent'|'course_assignment'|'course_document'|'course_link'|'course_exercise'|'course_learningpath'|'course_video'|'course_videolink'|'user' : the type of the referenced object 
     * @param int $object_selected the id of the referenced object 
     */
    public static function build_object_referennce_fields($module_selected, $course_selected, $type_selected, $object_selected)
    {
        global $langSelectFromMenu;
        
        /* The first field contains general modules or -1 for "course" 
         * which is considered as a supermodule of course modules
         * values: general module ids or -1 for course
         */
        $gen_type_selected = (is_null($course_selected))? $module_selected:-1;
        $object_select_fields = "<span id='refobjgentypecont'><select class='form-control' id='refobjgentype' name='refobjgentype'>";
        $objgentypes = array(0 => $langSelectFromMenu) + self::get_object_general_types();
        foreach($objgentypes as $k => $v){
             $selected = ($k == $gen_type_selected)? " selected":"";
             $object_select_fields .= "<option value='$k' $selected>$v</option>";          
        }
        
        /*The second field displays the courses of the user 
         * values of the form course:1
         */
        $display = (is_null($course_selected))? "none":"block";
        $course = (is_null($course_selected))? null:"course:$course_selected";
        $object_select_fields .= "</select></span>"
             ."<span id='refcoursecont' style=\"display:$display;float:left;\"><select class='form-control' id='refcourse' name='refcourse'>";
        $refcourses = array(0 => $langSelectFromMenu) + self::get_user_courselist();
        foreach($refcourses as $k => $v){
            $selected = ($k == $course)? " selected":"";
            $object_select_fields .= "<option value='$k' $selected>$v</option>";          
        }
        
        /*The third field displays course modules (if course is selected)  
         * values: course module ids
         */
        $display = (is_null($type_selected) || is_null($course_selected))? "none":"block";
        $object_select_fields .= "</select></span>"
            ."<span id='refobjtypecont' style=\"display:$display;float:left;\"><select class='form-control' id='refobjtype' name='refobjtype'>";
        $objtypes = array(0 => $langSelectFromMenu) + self::get_course_modules($course_selected);
        foreach($objtypes as $k => $v){
            $selected = ($k == $module_selected)? " selected":"";
            $object_select_fields .= "<option value='$k' $selected>$v</option>";          
        }
        
        /*The fourth field lists all items of a specified module (general or course module)  
         * values of the form: course_videolink:3 or user:8
         */
        $display = (is_null($object_selected) || $module_selected == -1)? "none":"block";
        $object_select_fields .= "</select></span>"
            ."<span id='refobjidcont' style=\"display:$display;float:left;\"><select class='form-control' id='refobjid' name='refobjid'>";
        $objids = array(0 => $langSelectFromMenu);
        if(!is_null($module_selected) && $module_selected != -1){
            $objids += self::get_module_items($module_selected,$course_selected);
        }
        foreach($objids as $k => $v){
            $selected = ($k == $type_selected.":".$object_selected)? " selected":"";
            $object_select_fields .= "<option value='$k' $selected>$v</option>";          
        }
        $object_select_fields .= "</select></span>";
        
        return $object_select_fields;
    }
    
    /**
     * Find the module that this type of objects is produced by and return all related fields from $ref_object_types. 
     * @param int $objtype The object type (values from $ref_object_types). It contains the object type (from $ref_object_types) and object id (id in the corresponding db table), e.g., video_link:5
     * @return array of object type from $ref_object_types (i.e., array('objtable' => '', 'id_field' => '', 'title_field' => '', 'course_field' => ''))
     */
    public static function get_module_from_objtype($objtype){
        foreach(self::$ref_object_types as $gt => $m){
            foreach($m as $mid => $minfo){
                if(array_key_exists($objtype, $minfo)){
                    return array_merge(array('mid' => $mid, 'gentype' => $gt), $minfo[$objtype]);
                }
            }
        }
        return array();
    }
    
    /**
     * Find the module that this type of objects is produced by and return all related fields from $ref_object_types. 
     * @param int $oid The object id which is formed by the object type (from $ref_object_types) and the object id (id in the corresponding db table), e.g., video_link:5
     * @return array of object info from the object's DB table.
     */
    public static function get_ref_obj_field_values($oid){
        $objtype = NULL;
        $objid = NULL;
        $objcourse = NULL;
        $objmodule = NULL;
        if(stripos($oid,':') !== false){
            list($objtype, $objid) = explode(':',$oid);    
        }
        if(!is_null($objtype)){
            $objmoduleinfo = self::get_module_from_objtype($objtype);
            if(!empty($objmoduleinfo)){
                $objmodule = $objmoduleinfo['mid'];
                $objgentype = $objmoduleinfo['gentype'];
                if($objgentype == 'course' || $objtype == 'course'){
                   $objcourse = Database::get()->querySingle("SELECT {$objmoduleinfo['course_field']} cid FROM {$objmoduleinfo['objtable']} WHERE {$objmoduleinfo['id_field']} = ?d", $objid)->cid;
                }
            }
        }
        return array('objtype' => $objtype, 'objid' => $objid, 'objcourse' => $objcourse, 'objmodule' => $objmodule);
    }
    
    /**
     * Get list of current user courses (for the reference select list in the notes form)
     * @return array of course object ids and descriptions.
     */
    public static function get_user_courselist(){
        if (isset($_SESSION['uid']) AND $_SESSION['uid']) {
            $uc = Database::get()->queryArray("SELECT CONCAT('course:',c.id) id, CONCAT(c.title, ' (',c.code,')') name FROM course_user cu JOIN course c ON c.id=cu.course_id
                                             WHERE user_id = ?d AND visible > 0", $_SESSION['uid']);
            $user_courses = array();
            foreach($uc as $v){
                $user_courses[$v->id] = $v->name;
            }
            return $user_courses;
        }
        else{
            return array();
        }    
    }
    
    /**
     * Get list of modules activated for the given course
     * @return array of course modules
     */
    public static function get_course_modules($course){
        $modules = array();
        if (isset($_SESSION['uid']) AND $_SESSION['uid']) {
            $moduleIDs = Database::get()->queryArray("SELECT module_id FROM course_module cm JOIN course_user cu ON cm.course_id=cu.course_id
                                             WHERE visible = 1 AND
                                             cm.course_id = ?d AND user_id = ?d AND module_id IN (".self::get_module_list('course').")", $course, $_SESSION['uid']);
            foreach($moduleIDs as $mod){ 
                $tempname = array_keys(self::$ref_object_types['course'][$mod->module_id]);
                $modules[$mod->module_id] = isset($GLOBALS[self::$lang_vars[$mod->module_id]])? $GLOBALS[self::$lang_vars[$mod->module_id]]: $tempname[0];    
            }
        }
        return $modules;
    }
    
   /**
    * Get list of modules not related with a specific course
    * @return array of modules
    */
   public static function get_general_modules(){
       $modules = array();
       foreach(self::$ref_object_types['general'] as $mod){ 
           $tempname = array_keys(self::$ref_object_types['general'][$mod->module_id]);
           $modules[$mod->module_id] = isset($GLOBALS[self::$lang_vars[$mod->module_id]])? $GLOBALS[self::$lang_vars[$mod->module_id]]: $tempname[0];    
       }
       return $modules;
    }
    
    /**
     * Get list of object general types (for the 1st reference select list in the notes form )
     * @return array of object general types from $ref_object_types.
     */    
    public static function get_object_general_types(){
        global $is_editor;
        
        if (isset($_SESSION['uid']) AND $_SESSION['uid']) {
            $modules = array();
            foreach(self::$ref_object_types['general'] as $mid => $minfo){
                if($mid != MODULE_ID_USERS or $is_editor) {
                    $tempname = array_keys($minfo);
                    $modules[$mid] = isset(self::$lang_vars[$mid])? $GLOBALS[self::$lang_vars[$mid]]: $tempname[0];    
                }
            }
            return $modules;
        }
        else{
            return array();
        }    
    }
    
    /**
     * Get list of items created for a specific module (for the 2nd reference select list in the notes form)
     * @param int $course the course that the desired items belong to
     * @param int $module the module id that the desired items are creted by
     * @return array of modules' items as object id and object description 
     */
    public static function get_module_items($module, $course){
        if(is_null($course) || empty($course)){
            return self::get_general_module_items($module);
        }
        else{
            return self::get_course_module_items($course, $module);
        }
    }
    
    /**
     * Get list of course items created for a specific module (for the 2nd reference select list in the notes form)
     * @param int $course the course that the desired items belong to
     * @param int $module the module id that the desired items are creted by
     * @return array of modules items as object id and object description 
     */
    public static function get_general_module_items($module){
        $items = array();
        if (isset($_SESSION['uid']) && $_SESSION['uid']) {
            if(in_array($module, array_keys(self::$ref_object_types['general']))){
                if($module == MODULE_ID_USERS){
                    if(check_teacher()){
                        $objtype = 'user';
                        $objprops = self::$ref_object_types['general'][$module]['user'];
                        $q = "SELECT CONCAT('$objtype',':',u.{$objprops['id_field']}) id, {$objprops['title_field']} title "
                             . "  FROM course_user tutor_courses JOIN course_user student_courses ON tutor_courses.course_id = student_courses.course_id"
                             . " JOIN {$objprops['objtable']} u ON student_courses.user_id = u.{$objprops['id_field']} "
                             . " WHERE tutor_courses.status = 1 AND student_courses.status = 5";
                        $newitems = Database::get()->queryArray($q);
                        foreach($newitems as $ni){
                            $items[$ni->id] = $ni->title;
                        }
                    }
                    
                }
                else{
                    foreach(self::$ref_object_types['general'][$module] as $objtype => $objprops){
                        $newitems = Database::get()->queryArray("SELECT CONCAT('$objtype',':',{$objprops['id_field']}) id, {$objprops['title_field']} title FROM {$objprops['objtable']}");    
                        foreach($newitems as $ni){
                            $items[$ni->id] = $ni->title;
                        }
                    }
                }
            }
        }
        return $items;
    }
            
    /**
     * Get list of course items created for a specific module (for the 2nd reference select list in the notes form)
     * @param int $course the course that the desired items belong to
     * @param int $module the module id that the desired items are creted by
     * @return array of modules items as object id and object description 
     */
    public static function get_course_module_items($course, $module){
        if (isset($_SESSION['uid']) && $_SESSION['uid']) {
            $user_associated_to_course = Database::get()->querySingle("SELECT count(*) c FROM course_user WHERE course_id = ?d AND user_id = ?d", intval($course), intval($_SESSION['uid']))->c;
            if($user_associated_to_course == 1){
                $is_course_tool_visible = Database::get()->querySingle("SELECT visible FROM course_module WHERE course_id = ?d AND module_id = ?d", $course, $module)->visible;
                if(in_array($module, array_keys(self::$ref_object_types['course'])) && $is_course_tool_visible == 1){
                    $items = array();
                    foreach(self::$ref_object_types['course'][$module] as $objtype => $objprops){
                        $newitems = Database::get()->queryArray("SELECT CONCAT('$objtype',':',{$objprops['id_field']}) id, {$objprops['title_field']} title FROM {$objprops['objtable']} WHERE {$objprops['course_field']} = ?d", $course);    
                        foreach($newitems as $ni){
                            $items[$ni->id] = $ni->title;
                        }
                    }
                    if(!empty($items)){
                        return $items;
                    }
                }
            }
        }
        return array();
    }
    
     /**
     * Get list of modules either general or defined in terms of a course
     * @param int $mgroup general or course modules group as defined in $ref_object_types
     * @return string with comma separated module ids 
     */
    public static function get_module_list($mgroup){
        if(isset(self::$ref_object_types[$mgroup])){
            $mlist = "";
            $sep = "";
            foreach(self::$ref_object_types[$mgroup] as $mid => $minfo){
                $mlist .= $sep.$mid;
                $sep = ",";
            }
            return $mlist;
        }
        else{
            return "";
        }
    }
    
    /**
     * Build link to referenced object by a note
     * @param integer $module_id the module id
     * @param $item_type string with values: 'course'|'course_ebook'|'course_event'|'personalevent'|'course_assignment'|'course_document'|'course_link'|'course_exercise'|'course_learningpath'|'course_video'|'course_videolink'|'user'
     * @param integer $item_id the item id in the database
     * @param integer $course_id the course id
     * @return array of modules items as object id and object description 
     */
    public static function item_link($module_id, $item_type, $item_id, $course_id){
        
        global $urlServer;
        $itemurl = $urlServer;
        if(is_null($item_type) || empty($item_type)){
            return false;
        }
        $objprops = self::get_module_from_objtype($item_type);
        $res = Database::get()->queryArray("SELECT {$objprops['id_field']} id, {$objprops['title_field']} title FROM {$objprops['objtable']} WHERE {$objprops['id_field']} = ?d", $item_id);    
        if($res){
            $itemattributes = $res[0];
            if($item_type == 'course'){
                $itemurl .= $objprops['relative_prefix_path'].sprintf($objprops['relative_module_path'], course_id_to_code($course_id));
            }    
            else{
                $itemurl .= $objprops['relative_prefix_path'].$objprops['relative_module_path'].'?';
                if(!empty($course_id)){
                    $itemurl .= $objprops['course_parameter']."=".course_id_to_code($course_id);
                    if(!empty($objprops['item_id_parameter'])){
                        $itemurl .= '&amp;';
                    }
                }
                if(!empty($objprops['item_id_parameter'])){
                    $itemurl .= $objprops['item_id_parameter']."=".$item_id;
                }
            }

            $itemlink = '<a href="';
            $itemlink .= $itemurl;
            $itemlink .= '" target="_blank">';
            $itemlink .= $itemattributes->title;
            $itemlink .= "</a>";
            return $itemlink;
        }
        return false;
        
    }
    
    /*************************** References **********************************************************************/
}