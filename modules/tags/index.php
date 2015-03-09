<?php
/* ========================================================================
 * Open eClass 3.0
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
 * ======================================================================== */


$require_current_course = true;
$require_help = false;
$guest_allowed = true;


include '../../include/baseTheme.php';
require_once 'include/lib/textLib.inc.php';
require_once 'include/sendMail.inc.php';
require_once 'include/lib/modalboxhelper.class.php';
require_once 'include/lib/multimediahelper.class.php';
require_once 'include/log.php';
require_once 'modules/search/indexer.class.php';
// The following is added for statistics purposes
require_once 'include/action.php';

 if (isset($_GET['tag']) && strlen($_GET['tag'])) {        
    $tag = $_GET['tag'];
    $tool_content .= "<div class='panel'>";
    $tool_content .= "<div class='panel-body'>";
    $tool_content .= "<h3>".$_GET['tag']."</h3>";
        
    $tags_list = Database::get()->queryArray("SELECT * FROM tags WHERE tag = ?s AND course_id = ?d ORDER BY element_type", $tag, $course_id);
        
        //check the element type
        foreach($tags_list as $tag){
            if($tag->element_type == "announcement"){
                
                $announce = Database::get()->querySingle("SELECT title, content FROM announcement WHERE id = ?d ", $tag->element_id);
                
                $tool_content .= $langAnnouncement.": ";
                $tool_content .= "<a href='../../modules/announcements/?course=".$course_code."&an_id=".$tag->element_id."'>$announce->title</a><br>";
            }
            if($tag->element_type == "work"){
                
                $work = Database::get()->querySingle("SELECT title FROM assignment WHERE id = ?d ", $tag->element_id);
                
                $tool_content .= $langWork.": ";
                if($work->title){
                    $title = $work->title;
                }else{
                    $title = $langGradebookNoTitle;
                }
                $tool_content .= "<a href='../../modules/work/?course=".$course_code."&id=".$tag->element_id."'>$work->title</a><br>";
            }
            if($tag->element_type == "exe"){
                
                $exe = Database::get()->querySingle("SELECT title FROM exercise WHERE id = ?d ", $tag->element_id);
                
                $tool_content .= $langWork.": ";
                if($exe->title){
                    $title = $exe->title;
                }else{
                    $title = $langGradebookNoTitle;
                }
                $tool_content .= "<a href='../../modules/exercise/admin.php?course=".$course_code."&exerciseId=".$tag->element_id."'>$exe->title</a><br>";
            }
        }
        $tool_content .= "</div></div>";
}
    
    
draw($tool_content, 2, null, $head_content);
