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

$require_current_course = TRUE;
$require_editor = true;
$require_help = TRUE;
$helpTopic = 'exercises';
$helpSubTopic = 'question_categories';

include '../../include/baseTheme.php';

if (isset($_POST['submitCat'])) {
    $v = new Valitron\Validator($_POST);
    $v->rule('required', array('questionCatName'));
    $v->labels(array(
        'questionCatName' => "$langTheField $langTitle"
    ));
    if($v->validate()) {
        $q_cat_name = $_POST['questionCatName'];
        if(isset($_GET['modifyCat'])) {
            $q_cat_id = $_GET['modifyCat'];
            Database::get()->query("UPDATE exercise_question_cats SET question_cat_name = ?s "
                    . "WHERE question_cat_id = ?d", $q_cat_name, $q_cat_id);
            Session::Messages($langEditCatSuccess, 'alert-success');
        } else {
            $PollActive = 1;
            $q_cat_id = Database::get()->query("INSERT INTO exercise_question_cats
                        (question_cat_name, course_id)
                        VALUES (?s, ?d)", $q_cat_name, $course_id)->lastInsertID;
            Session::Messages($langNewCatSuccess, 'alert-success');
        }
        redirect_to_home_page("modules/exercise/question_categories.php?course=$course_code");
    } else {
        Session::flashPost()->Messages($langFormErrors)->Errors($v->errors());
        if(isset($_GET['modifyCat'])) {
            $cat_id = intval($_GET['modifyCat']); 
            redirect_to_home_page("modules/exercise/question_categories.php?course=$course_code&modifyCat=$cat_id");
        } else {        
            redirect_to_home_page("modules/exercise/question_categories.php??course=$course_code&newCat=yes");
        }        
    }    
} elseif (isset($_GET['modifyCat']) || isset($_GET['newCat'])) {
    $pageName = isset($_GET['newCat']) ? $langNewCat : $langEditCat;
    $navigation = array(
                    array("url" => "index.php?course=$course_code", "name" => $langExercices),
                    array("url" => "question_categories.php?course=$course_code", "name" => $langQuestionCats)
                );
    $form_action_url = "$_SERVER[SCRIPT_NAME]?course=$course_code";
    $form_action_url .= isset($_GET['modifyCat']) ? "&modifyCat=".intval($_GET['modifyCat']) : "&newCat=yes";
    if (isset($_GET['modifyCat'])){
        $q_cat = Database::get()->querySingle("SELECT * FROM exercise_question_cats WHERE question_cat_id = ?d", $_GET['modifyCat']);
    }
    $questionCatName = Session::has('questionCatName') ? Session::get('questionCatName') : (isset($q_cat) ? $q_cat->question_cat_name : '');
    $tool_content .= action_bar(array(
        array(
            'title' => $langBack,
            'level' => 'primary-label',
            'icon' => 'fa-reply',
            'url' => "question_categories.php?course=$course_code"
        )
    ));
    $tool_content .= "
        <div class='form-wrapper'>
            <form class='form-horizontal' role='form' action='$form_action_url' method='post'>
                <div class='form-group ".(Session::getError('questionCatName') ? "has-error" : "")."'>
                    <label for='questionCatName' class='col-sm-2 control-label'>$langTitle:</label>
                    <div class='col-sm-10'>
                      <input name='questionCatName' type='text' class='form-control' id='questionCatName' placeholder='$langTitle' value='$questionCatName'>
                      <span class='help-block'>".Session::getError('questionCatName')."</span>
                    </div>
                </div>
                <div class='form-group'>
                    <div class='col-sm-10 col-sm-offset-2'>
                        <input class='btn btn-primary' name='submitCat' type='submit' value='$langSubmit'>
                        <a href='question_categories.php?course=$course_code' class='btn btn-default'>$langCancel</a>
                    </div>
                </div>                
            </form>
        </div>";
} elseif (isset($_GET['deleteCat'])) {
    $q_cat_id = $_GET['deleteCat'];
    if (Database::get()->query("DELETE FROM exercise_question_cats WHERE question_cat_id = ?d AND course_id = ?d", $q_cat_id, $course_id)->affectedRows > 0) { 
        Database::get()->query("UPDATE exercise_question SET category = ?d WHERE category = ?d AND course_id = ?d", 0, $q_cat_id, $course_id);
        Session::Messages($langDelCatSuccess, 'alert-success');
    }
    redirect_to_home_page("modules/exercise/question_categories.php?course=$course_code");
} else {
    $toolName = $langExercices;
    $pageName = $langQuestionCats;
    $navigation[] = array("url" => "index.php?course=$course_code", "name" => $langExercices);
    
    $tool_content .= action_bar(array(
        array(
            'title' => $langNewCat,
            'level' => 'primary-label',
            'icon' => 'fa-plus-circle',
            'url' => "question_categories.php?course=$course_code&newCat=yes",
            'button-class' => 'btn-success'
        ),
        array(
            'title' => $langBack,
            'level' => 'primary-label',
            'icon' => 'fa-reply',
            'url' => "index.php?course=$course_code"
        ),          
    ));

    $q_cats = Database::get()->queryArray("SELECT * FROM exercise_question_cats WHERE course_id = ?d", $course_id);
    if (count($q_cats) > 0) {
        $tool_content .= "
            <div class='table-responsive'>
                <table class='table-default'>
                    <tbody>
                        <tr>
                            <th>$langTitle</th>
                            <th class='text-center'>".icon('fa-gears')."</th>
                        </tr> 
                    ";
        foreach ($q_cats as $q_cat) {
            $action_button = action_button(array(
                array(
                    'title' => $langEditChange,
                    'url' => "question_categories.php?course=$course_code&modifyCat=$q_cat->question_cat_id",
                    'icon' => 'fa-edit'
                ),            
                array(
                    'title' => $langDelete,
                    'url' => "question_categories.php?course=$course_code&deleteCat=$q_cat->question_cat_id",
                    'icon' => 'fa-times',
                    'confirm' => $langQuestionCatDelConfirrm,
                    'class' => 'delete'
                )
            ));
            $tool_content .= "
                        <tr>
                            <td>$q_cat->question_cat_name</td>
                            <td class='option-btn-cell'>$action_button</td>
                        </tr>";
        }
        $tool_content .= "                
                    </tbody>
                </table>
            </div>";
    } else {
        $tool_content .= "<div class='alert alert-warning'>$langNoQuestionCats</div>";
    }
}
draw($tool_content, 2, null, $head_content);
