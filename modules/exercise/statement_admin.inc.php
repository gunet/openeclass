<?php

/*
 *  ========================================================================
 *  * Open eClass
 *  * E-learning and Course Management System
 *  * ========================================================================
 *  * Copyright 2003-2024, Greek Universities Network - GUnet
 *  *
 *  * Open eClass is an open platform distributed in the hope that it will
 *  * be useful (without any warranty), under the terms of the GNU (General
 *  * Public License) as published by the Free Software Foundation.
 *  * The full license can be read in "/info/license/license_gpl.txt".
 *  *
 *  * Contact address: GUnet Asynchronous eLearning Group
 *  *                  e-mail: info@openeclass.org
 *  * ========================================================================
 *
 */

load_js('bootstrap-slider');
load_js('select2');

$head_content .= "
<script>
$(function() {
    $('#questionCat').select2();
    var diffArray = ['$langQuestionNotDefined','$langQuestionVeryEasy', '$langQuestionEasy', '$langQuestionModerate', '$langQuestionDifficult', '$langQuestionVeryDifficult']
    $('#questionDifficulty').slider({
        tooltip: 'hide',
    formatter: function(value) {
            $('#questionDifficultyValue').text(diffArray[value]);
            removeLabel();
            addLabel(value);
    }
    });
    $('#questionDifficulty').on('slide', function(slideEvt) {
        $('#questionDifficultyValue').text(diffArray[slideEvt.value]);
        removeLabel();
        addLabel(slideEvt.value);
    });
    function removeLabel() {
        $('#questionDifficultyValue').removeClass(function (index, css) {
            return (css.match (/\blabel-\S+/g) || []).join(' ');
        });
    }
    function addLabel(value) {
        if (value == 0) $('#questionDifficultyValue').addClass('label-default');
        else if(value>0  && value < 3) $('#questionDifficultyValue').addClass('label-success');
        else if (value == 3) $('#questionDifficultyValue').addClass('label-warning');
        else if (value > 3 && value <= 5) $('#questionDifficultyValue').addClass('label-danger');
    }
    $('input[name=answerType]').not('#free_text_selector').click(hideGrade);
    $('input[name=answerType]').not('#fill_in_blanks_selector').click(hideFillInChoices);
    $('input[name=answerType]#free_text_selector').click(showGrade);
    $('input[name=answerType]#fill_in_blanks_selector').click(showFillInChoices);
    $('input[name=fillInBlanksOptions]').change(updateFillInBlanksAnswerTypeValue);
    function hideGrade(){
        $('input[name=questionGrade]').prop('disabled', true).closest('div.form-group').addClass('hide');
    }
    function showGrade(){
        $('input[name=questionGrade]').prop('disabled', false).closest('div.form-group').removeClass('hide');
    }
    function showFillInChoices(){
        $('#fillInBlanksOptions').removeClass('hide');
    }
    function hideFillInChoices(){
        $('#fillInBlanksOptions').addClass('hide');
    }
    function updateFillInBlanksAnswerTypeValue(){
        $('input[name=answerType]#fill_in_blanks_selector').val($(this).val());
    }
 });
</script>
 ";
// the question form has been submitted
if (isset($_POST['submitQuestion'])) {

    $v = new Valitron\Validator($_POST);
    $v->rule('required', array('questionName'));
    $v->labels(array(
        'questionName' => "$langTheField $langQuestion"
    ));
    if ($v->validate()) {
        $questionName = canonicalize_whitespace($_POST['questionName']);
        $questionDescription = purify($_POST['questionDescription']);
        $questionFeedback = purify($_POST['questionFeedback']);
        $answerType = intval($_POST['answerType']);
        // no name given
        if (empty($questionName)) {
            $msgErr = $langGiveQuestion;
        }
        if (isset($_GET['modifyQuestion'])) {
            $objQuestion->read($_GET['modifyQuestion']);
        }
        $objQuestion->updateTitle($questionName);
        $objQuestion->updateDescription($questionDescription);
        $objQuestion->updateFeedback($questionFeedback);
        $objQuestion->updateType($answerType);
        $objQuestion->updateDifficulty($_POST['difficulty']);
        $objQuestion->updateCategory($_POST['category']);

        // If grade field set (only in Free text questions)
        if (isset($_POST['questionGrade'])) {
            $objQuestion->updateWeighting(str_replace(',', '.', $_POST['questionGrade']));
        }
        if (isset($_GET['exerciseId'])) {
            $exerciseId = intval($_GET['exerciseId']);
            $objQuestion->save($exerciseId);
        } else {
            $objQuestion->save();
        }
        $questionId = $objQuestion->selectId();
        // upload or delete picture
        if (isset($_POST['deletePicture'])) {
            $objQuestion->removePicture();
        } elseif (isset($_FILES['imageUpload']) && is_uploaded_file($_FILES['imageUpload']['tmp_name'])) {

            require_once 'include/lib/fileUploadLib.inc.php';
            validateUploadedFile($_FILES['imageUpload']['name'], 2);

            $type = $_FILES['imageUpload']['type'];
            if (!$objQuestion->uploadPicture($_FILES['imageUpload']['tmp_name'], $type)) {
                $tool_content .= "<div class='alert alert-danger'><i class='fa-solid fa-circle-xmark fa-lg'></i><span>$langInvalidPicture</span></div>";
            }
        }
        if (isset($exerciseId)) {
            // adds the question ID into the question list of the Exercise object
            if ($objExercise->addToList($questionId)) {
                $objExercise->save();
                $nbrQuestions++;
            }
        }
        // if the answer type is free text (which means it doesn't have predefined answers)
        // redirect to either pool or edit exercise page
        // else redirect to modify answers page in order to add answers to question
        if ($answerType == FREE_TEXT) {
            $redirect_url = (isset($exerciseId)) ? "modules/exercise/admin.php?course=$course_code&exerciseId=$exerciseId&htopic=6" : "modules/exercise/question_pool.php?course=$course_code";
        } else {
            if (isset($_GET['modifyQuestion'])) { // existing question
                $redirect_url = "modules/exercise/admin.php?course=$course_code".((isset($exerciseId))? "&exerciseId=$exerciseId" : "")."&modifyAnswers=$questionId";
            } else { // new question
                $redirect_url = "modules/exercise/admin.php?course=$course_code".((isset($exerciseId))? "&exerciseId=$exerciseId" : "")."&modifyAnswers=$questionId&htopic=$answerType";
            }
        }
        redirect_to_home_page($redirect_url);
    } else {
        Session::flashPost()->Messages($langFormErrors)->Errors($v->errors());
        $new_or_modif = isset($_GET['modifyQuestion']) ? "&modifyQuestion=$_GET[modifyQuestion]" : "&newQuestion=yes";
        $exercise_or_pool = isset($_GET['exerciseId']) ? "&exerciseId=$_GET[exerciseId]" : "";
        redirect_to_home_page("modules/exercise/admin.php?course={$course_code}{$exercise_or_pool}{$new_or_modif}");
    }
} else {
    // if we don't come here after having cancelled the warning message "used in several exercises"
    if (!isset($buttonBack)) {
        $questionName = $objQuestion->selectTitle();
        $questionDescription = $objQuestion->selectDescription();
        $questionFeedback = $objQuestion->selectFeedback();
        $answerType = $objQuestion->selectType();
        $difficulty = $objQuestion->selectDifficulty();
        $category = $objQuestion->selectCategory();
        $questionWeight = $objQuestion->selectWeighting();
    }
}
if (isset($_GET['newQuestion']) || isset($_GET['modifyQuestion'])) {
    $questionId = $objQuestion->selectId();
    // is picture set ?
    $okPicture = file_exists($picturePath . '/quiz-' . $questionId) ? true : false;
    // if there is an error message
    if (!empty($msgErr)) {
        $tool_content .= "<div class='alert alert-danger'><i class='fa-solid fa-circle-xmark fa-lg'></i><span>$msgErr</span></div>\n";
    }

    if (isset($_GET['newQuestion'])){
        $form_submit_action = "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;".((isset($exerciseId))? "exerciseId=$exerciseId" : "")."&amp;newQuestion=" . urlencode($_GET['newQuestion']);
        $link_back = isset($exerciseId) ? "admin.php?course=$course_code&exerciseId=$exerciseId" : "question_pool.php?course=$course_code";

    } else {
        $form_submit_action = "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;".((isset($exerciseId))? "exerciseId=$exerciseId" : "")."&amp;modifyQuestion=" . urlencode($_GET['modifyQuestion']);
        $link_back = "admin.php?course=$course_code".(isset($exerciseId) ? "&exerciseId=$exerciseId" : "")."&modifyAnswers=$_GET[modifyQuestion]";
    }
    $action_bar = action_bar(array(
        array('title' => $langBack,
            'url' => $link_back,
            'icon' => 'fa-reply',
            'level' => 'primary'
        )
    ));

    $tool_content .= $action_bar;
    $q_cats = Database::get()->queryArray("SELECT * FROM exercise_question_cats WHERE course_id = ?d ORDER BY question_cat_name", $course_id);

    if (Session::has('questionName')) {
        $questionName = Session::get('questionName');
    }
    if (Session::has('questionDescription')) {
        $questionDescription = Session::get('questionDescription');
    }
    if (Session::has('questionFeedback')) {
        $questionFeedback = Session::get('questionFeedback');
    }
    if (Session::has('category')) {
        $category = Session::get('category');
    }
    if (Session::has('difficulty')) {
        $difficulty = Session::get('difficulty');
    }
    if (Session::has('answerType')) {
        $answerType = Session::get('answerType');
    }
    $options = "<option value='0'>-- $langQuestionWithoutCat --</option>\n";
    foreach ($q_cats as $q_cat) {
        $options .= "<option value='{$q_cat->question_cat_id}' " . (($category == $q_cat->question_cat_id) ? 'selected' : '') . '>' . q($q_cat->question_cat_name) . "</option>\n";
    }
    enableCheckFileSize();
    $tool_content .= "
    <div class='d-lg-flex gap-4 mt-4'>
        <div class='flex-grow-1'><div class='form-wrapper form-edit rounded'>
            <form class='form-horizontal' role='form' enctype='multipart/form-data' method='post' action='$form_submit_action'> 
            <fieldset>
            <legend class='mb-0' aria-label='$langForm'></legend>                             
            <div class='row form-group ".(Session::getError('questionName') ? "has-error" : "")."'>
                <label for='questionName' class='col-12 control-label-notes mb-1'>$langQuestion <span class='asterisk Accent-200-cl'>(*)</span></label>
                <div class='col-12'>
                      <input name='questionName' type='text' class='form-control' id='questionName' placeholder='$langQuestion' value='" . q($questionName) . "'>
                      <span class='help-block Accent-200-cl'>".Session::getError('questionName')."</span>
                </div>
            </div>
            <div class='row form-group mt-4'>
                <label for='imageUpload' class='col-12 control-label-notes mb-1'>".(($okPicture) ? $langReplacePicture : $langAddPicture)."</label>
                <div class='col-12'>" .

                (($okPicture) ? "<img src='../../$picturePath/quiz-$questionId'><br><br>" : "") .
                fileSizeHidenInput() . "
                  <input type='file' name='imageUpload' id='imageUpload'>
                </div>
            </div>";
            if ($okPicture) {
                $tool_content .= "
                    <div class='row form-group mt-4'>
                        <div class='col-12'>
                            <div class='checkbox'>
                                <label class='label-container' aria-label='$langSelect'>
                                    <input type='checkbox' name='deletePicture' value='1' ".(isset($_POST['deletePicture'])? "checked":"").">
                                    <span class='checkmark'></span>
                                    $langDeletePicture
                              </label>
                            </div>
                        </div>
                    </div>";
            }
            $tool_content .= "
                <div class='row form-group mt-4'>
                    <label for='questionDescription' class='col-12 control-label-notes mb-1'>$langQuestionDescription</label>
                    <div class='col-12'>

                      ". rich_text_editor('questionDescription', 4, 50, $questionDescription) ."
                    </div>
                </div>
                <div class='row form-group mt-4'>
                    <label for='questionCat' class='col-12 control-label-notes mb-1'>$langQuestionCat</label>
                    <div class='col-12'>
                        <select name='category' id='questionCat' class='form-select'>
                            $options
                        </select>
                    </div>
                </div>
                <div class='row form-group mt-4'>
                    <label for='questionFeedback' class='col-12 control-label-notes mb-1'>$langQuestionFeedback</label>
                    <div class='col-12'>
                      ". rich_text_editor('questionFeedback', 4, 50, $questionFeedback) ."
                    </div>
                </div>
                <div class='row form-group mt-4'>
                    <label for='questionDifficulty' class='col-12 control-label-notes mb-1'>$langQuestionDiffGrade</label>
                    <div class='col-12'>
                        <div class='col-sm-2'>
                            <input id='questionDifficulty' name='difficulty' data-slider-id='ex1Slider' type='text' data-slider-min='0' data-slider-max='5' data-slider-step='1' data-slider-value='$difficulty'/>
                        </div>
                        <div class='col-sm-10'>
                            <span id='questionDifficultyValue' class='label label-default'></span>
                        </div>
                    </div>                    
                </div>
            ";
        $tool_content .= "<div class='row form-group mt-4'>
                <div class='col-12 control-label-notes mb-1'>$langAnswerType</div>
                <div class='col-12'>
                    <div class='radio'>
                      <label>
                        <input type='radio' name='answerType' value='1' ". (($answerType == UNIQUE_ANSWER) ? "checked" : "") .">
                        $langUniqueSelect
                      </label>
                    </div>
                    <div class='radio'>
                      <label>
                        <input type='radio' name='answerType' value='2' ". (($answerType == MULTIPLE_ANSWER) ? "checked" : "") .">
                       $langMultipleSelect
                      </label>
                    </div>
                    <div class='radio'>
                      <label>
                        <input type='radio' name='answerType' value='". (($answerType == FILL_IN_BLANKS || $answerType == FILL_IN_BLANKS_TOLERANT || $answerType == FILL_IN_FROM_PREDEFINED_ANSWERS) ? $answerType : 3) ."' id='fill_in_blanks_selector' ". (($answerType == FILL_IN_BLANKS || $answerType == FILL_IN_BLANKS_TOLERANT || $answerType == FILL_IN_FROM_PREDEFINED_ANSWERS) ? "checked" : "") .">
                       $langFillBlanks
                      </label>
                    </div>
                    <div class='row'>
                        <div class='col-10 offset-2 d-flex justify-content-center'>
                            <div class='form-group ".(($answerType != FILL_IN_BLANKS && $answerType != FILL_IN_BLANKS_TOLERANT && $answerType != FILL_IN_FROM_PREDEFINED_ANSWERS) ? "hide": "")."' id='fillInBlanksOptions'>
                                <div class='col-12'>
                                    <div class='radio mb-2'>
                                        <label>
                                            <input type='radio' name='fillInBlanksOptions' value='".FILL_IN_BLANKS."' ". (($answerType != FILL_IN_BLANKS_TOLERANT) ? "checked" : "") .">
                                            $langFillBlanksStrict $langFillBlanksStrictExample
                                        </label>
                                    </div>
                                    <div class='radio mb-2'>
                                        <label>
                                            <input type='radio' name='fillInBlanksOptions' value='".FILL_IN_BLANKS_TOLERANT."' ". (($answerType == FILL_IN_BLANKS_TOLERANT) ? "checked" : "") .">
                                            $langFillBlanksTolerant $langFillBlanksTolerantExample

                                        </label>
                                    </div>
                                    <div class='radio'>
                                        <label>
                                            <input type='radio' name='fillInBlanksOptions' value='".FILL_IN_FROM_PREDEFINED_ANSWERS."' ". (($answerType == FILL_IN_FROM_PREDEFINED_ANSWERS) ? "checked" : "") .">
                                            $langFillFromSelectedWords
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class='radio'>
                          <label>
                            <input type='radio' name='answerType' value='".MATCHING."' ". (($answerType == MATCHING) ? "checked" : "") .">
                           $langMatching
                          </label>
                        </div>
                        <div class='radio'>
                          <label>
                            <input type='radio' name='answerType' value='".TRUE_FALSE. "' ". (($answerType == TRUE_FALSE) ? "checked" : "") .">
                           $langTrueFalse
                          </label>
                        </div>
                        <div class='radio'>
                          <label>
                            <input type='radio' id='free_text_selector' name='answerType' value='".FREE_TEXT."' ". (($answerType == FREE_TEXT) ? "checked" : "") .">
                           $langFreeText
                          </label>
                        </div>
                        <div class='radio'>
                            <label>
                                <input type='radio' name='answerType' value='9' ". (($answerType == DRAG_AND_DROP_TEXT) ? "checked" : "") .">
                                $langDragAndDropText
                            </label>
                        </div>
                        <div class='radio'>
                            <label>
                                <input type='radio' name='answerType' value='10' ". (($answerType == DRAG_AND_DROP_MARKERS) ? "checked" : "") .">
                                $langDragAndDropMarkers
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <div class='row form-group ".(($answerType != FREE_TEXT) ? "hide": "")." mt-4'>
                <label for='questionGrade' class='col-12 control-label-notes mb-1'>$langGradebookGrade</label>
                <div class='col-12'>
                  <input name='questionGrade' type='text' class='form-control' id='questionGrade' placeholder='$langGradebookGrade' value='$questionWeight'".(($answerType != 6) ? " disabled": "").">
                </div>
            </div>
            <div class='row'>
                <div class='col-12 mt-5 d-flex justify-content-end align-items-center gap-2'>                                          
                        <input type='submit' class='btn submitAdminBtn' name='submitQuestion' value='$langCreate'>                     
                        <a href='$link_back' class='btn cancelAdminBtn'>$langCancel</a>                    
                </div>
            </div>
          </fieldset>
        </form>
        </div></div><div class='d-none d-lg-block'>
            <img class='form-image-modules' src='".get_form_image()."' alt='$langImgFormsDes'>
        </div>
    </div>";
}
