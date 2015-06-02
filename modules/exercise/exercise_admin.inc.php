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

/**
 * @file exercise_admin.inc.php
 * @brief Create new exercise or modify an existing one
 */
require_once 'modules/search/indexer.class.php';
require_once 'modules/tags/moduleElement.class.php';

// the exercise form has been submitted
if (isset($_POST['submitExercise'])) {
    $v = new Valitron\Validator($_POST);
    $v->rule('required', array('exerciseTitle'));
    $v->rule('numeric', array('exerciseTimeConstraint', 'exerciseAttemptsAllowed'));
    $v->rule('date', array('exerciseEndDate', 'exerciseStartDate'));
    $v->labels(array(
        'exerciseTitle' => "$langTheField $langExerciseName",
        'exerciseTimeConstraint' => "$langTheField $langExerciseConstrain",
        'exerciseAttemptsAllowed' => "$langTheField $langExerciseAttemptsAllowed",
        'exerciseEndDate' => "$langTheField $langExerciseEnd",
        'exerciseStartDate' => "$langTheField $langExerciseStart"
    ));
    if($v->validate()) {
        $exerciseTitle = trim($exerciseTitle);
        $exerciseDescription = purify($exerciseDescription);
        $randomQuestions = (isset($_POST['questionDrawn'])) ? intval($_POST['questionDrawn']) : 0;
        $objExercise->updateTitle($exerciseTitle);
        $objExercise->updateDescription($exerciseDescription);
        $objExercise->updateType($exerciseType);
        if (isset($exerciseStartDate) and !empty($exerciseStartDate)) {
            $startDateTime_obj = DateTime::createFromFormat('d-m-Y H:i', $exerciseStartDate);
        } else {
            $startDateTime_obj = new DateTime('NOW');
        }
        $startDateTime_obj = $startDateTime_obj->format('Y-m-d H:i:s');
        $objExercise->updateStartDate($startDateTime_obj);
        $endDateTime_obj = isset($exerciseEndDate) && !empty($exerciseEndDate) ? DateTime::createFromFormat('d-m-Y H:i',$exerciseEndDate)->format('Y-m-d H:i:s') : NULL;
        $objExercise->updateEndDate($endDateTime_obj);
        $objExercise->updateTempSave($exerciseTempSave);
        $objExercise->updateTimeConstraint($exerciseTimeConstraint);
        $objExercise->updateAttemptsAllowed($exerciseAttemptsAllowed);
        $objExercise->setRandom($randomQuestions);
        $objExercise->updateResults($dispresults);
        $objExercise->updateScore($dispscore);
        $objExercise->save();
        // reads the exercise ID (only useful for a new exercise)
        $exerciseId = $objExercise->selectId();
        Indexer::queueAsync(Indexer::REQUEST_STORE, Indexer::RESOURCE_EXERCISE, $exerciseId);
        
        //tags
        if (isset($_POST['tags'])) {
            $tagsArray = explode(',', $_POST['tags']);
            $moduleTag = new ModuleElement($exerciseId);
            $moduleTag->syncTags($tagsArray);
        }
        
        redirect_to_home_page('modules/exercise/admin.php?course='.$course_code.'&exerciseId='.$exerciseId);        
    } else {
        $new_or_modify = isset($_GET['NewExercise']) ? "&NewExercise=Yes" : "&exerciseId=$_GET[exerciseId]&modifyExercise=yes";
        Session::flashPost()->Messages($langFormErrors)->Errors($v->errors());
        redirect_to_home_page('modules/exercise/admin.php?course='.$course_code.$new_or_modify);
    }    
} else {
    $exerciseId = $objExercise->selectId();
    $exerciseTitle = Session::has('exerciseTitle') ? Session::get('exerciseTitle') : $objExercise->selectTitle();
    $exerciseDescription = Session::has('exerciseDescription') ? Session::get('exerciseDescription') : $objExercise->selectDescription();
    $exerciseType = Session::has('exerciseType') ? Session::get('exerciseType') : $objExercise->selectType();
    //more repopulation need to be done
    $exerciseStartDate = Session::has('exerciseStartDate') ? Session::get('exerciseStartDate') : DateTime::createFromFormat('Y-m-d H:i:s', $objExercise->selectStartDate())->format('d-m-Y H:i');
    $exerciseEndDate = $objExercise->selectEndDate();
    if (is_null($exerciseEndDate) && !Session::has('exerciseEndDate')) {
        $exerciseEndDate = '';
    } else {
        $exerciseEndDate = Session::has('exerciseEndDate') ? Session::get('exerciseEndDate') : DateTime::createFromFormat('Y-m-d H:i:s', $objExercise->selectEndDate())->format('d-m-Y H:i');
    }
    $enableStartDate = Session::has('enableStartDate') ? Session::get('enableStartDate') : null;
    $enableEndDate = Session::has('enableEndDate') ? Session::get('enableEndDate') : ($exerciseEndDate ? 1 : 0);
    $exerciseTempSave = Session::has('exerciseTempSave') ? Session::get('exerciseTempSave') : $objExercise->selectTempSave();
    $exerciseTimeConstraint = Session::has('exerciseTimeConstraint') ? Session::get('exerciseTimeConstraint') : $objExercise->selectTimeConstraint();
    $exerciseAttemptsAllowed = Session::has('exerciseAttemptsAllowed') ? Session::get('exerciseAttemptsAllowed') : $objExercise->selectAttemptsAllowed();
    $randomQuestions = Session::has('questionDrawn') ? Session::get('questionDrawn') : $objExercise->isRandom();
    $displayResults = Session::has('dispresults') ? Session::get('dispresults') : $objExercise->selectResults();
    $displayScore = Session::has('dispscore') ? Session::get('dispscore') : $objExercise->selectScore();
}

// shows the form to modify the exercise
if (isset($_GET['modifyExercise']) or isset($_GET['NewExercise'])) {
        
    load_js('bootstrap-datetimepicker');
    load_js('select2');

    $head_content .= "<script type='text/javascript'>
        $(function() {
            $('#exerciseStartDate, #exerciseEndDate').datetimepicker({
                format: 'dd-mm-yyyy hh:ii', 
                pickerPosition: 'bottom-left', 
                language: '".$language."',
                autoclose: true    
            });
            $('#enableEndDate, #enableStartDate').change(function() {
                var dateType = $(this).prop('id').replace('enable', '');
                if($(this).prop('checked')) {
                    $('input#exercise'+dateType).prop('disabled', false);
                } else {
                    $('input#exercise'+dateType).prop('disabled', true);
                }
            });
            
            $('.questionDrawnRadio').change(function() {
                if($(this).val()==0){
                    $('#questionDrawnInput').val(''); 
                    $('#questionDrawnInput').prop('disabled', true);
                    $('#questionDrawnInput').closest('div.form-group').addClass('hidden');
                } else {
                    $('#questionDrawnInput').prop('disabled', true);
                    $('#questionDrawnInput').closest('div.form-group').removeClass('hidden');
                }
            });            
            $('#randomDrawnSubset').change(function() {
                if($(this).prop('checked')){                   
                    $('#questionDrawnInput').prop('disabled', false);   
                    $('.questionDrawnRadio').prop('disabled', true); 
                } else {
                    $('#questionDrawnInput').prop('disabled', true);
                    $('.questionDrawnRadio').prop('disabled', false); 
                }
            });
            $('#exerciseAttemptsAllowed').blur(function(){
                var attempts = $(this).val();
                if (attempts ==0) {
                    $('#answersDispLastAttempt').addClass('hidden');
                    if ($('input[name=\"dispresults\"]:checked').val() == 3) {
                        $('input[name=\"dispresults\"][value=\"1\"]').prop('checked', true);
                    }
                } else {
                    $('#answersDispLastAttempt').removeClass('hidden');
                }
            });
        });
    </script>";
    $tool_content .= action_bar(array(
        array('title' => $langBack,
            'url' => $exerciseId ? "admin.php?course=$course_code&exerciseId=$exerciseId" : "index.php?course=$course_code",
            'icon' => 'fa-reply',
            'level' => 'primary-label'
        )
    ));    
   $tool_content .= "
        <div class='form-wrapper'>
            <form class='form-horizontal' role='form' method='post' action='$_SERVER[SCRIPT_NAME]?course=$course_code".(isset($_GET['modifyExercise']) ? "&amp;exerciseId=$exerciseId" : "&amp;NewExercise=Yes")."'>
             <fieldset>       
                 <div class='form-group ".(Session::getError('exerciseTitle') ? "has-error" : "")."'>
                   <label for='exerciseTitle' class='col-sm-2 control-label'>$langExerciseName:</label>
                   <div class='col-sm-10'>
                     <input name='exerciseTitle' type='text' class='form-control' id='exerciseTitle' value='" . q($exerciseTitle) . "' placeholder='$langExerciseName'>
                     <span class='help-block'>".Session::getError('exerciseTitle')."</span>
                   </div>
                 </div>
                 <div class='form-group'>
                   <label for='exerciseDescription' class='col-sm-2 control-label'>$langExerciseDescription:</label>
                   <div class='col-sm-10'>
                   " . rich_text_editor('exerciseDescription', 4, 30, $exerciseDescription) . "
                   </div>
                 </div>
                 <div class='form-group'>
                     <label for='exerciseDescription' class='col-sm-2 control-label'>$langExerciseType:</label>
                     <div class='col-sm-10'>            
                         <div class='radio'>
                           <label>
                             <input type='radio' name='exerciseType' value='1' ".(($exerciseType <= 1)? 'checked' : '').">
                             $langSimpleExercise
                           </label>
                         </div>
                         <div class='radio'>
                           <label>
                             <input type='radio' name='exerciseType' value='2' ".(($exerciseType >= 2)? 'checked' : '').">
                             $langSequentialExercise
                           </label>
                         </div>
                     </div>
                 </div>              
                 <div class='input-append date form-group".(Session::getError('exerciseStartDate') ? " has-error" : "")."' id='startdatepicker' data-date='$exerciseStartDate' data-date-format='dd-mm-yyyy'>
                     <label for='exerciseStartDate' class='col-sm-2 control-label'>$langExerciseStart:</label>
                     <div class='col-sm-10'>
                        <div class='input-group'>
                            <span class='input-group-addon'>
                                <input style='cursor:pointer;' type='checkbox' id='enableStartDate' name='enableStartDate' value='1'".($enableStartDate ? ' checked' : '').">
                            </span>                        
                            <input class='form-control' name='exerciseStartDate' id='exerciseStartDate' type='text' value='$exerciseStartDate'".($enableStartDate ? '' : ' disabled').">
                        </div>
                        <span class='help-block'>".(Session::hasError('exerciseStartDate') ? Session::getError('exerciseStartDate') : "&nbsp;&nbsp;&nbsp;<i class='fa fa-share fa-rotate-270'></i> $langExerciseStartHelpBlock")."</span>
                     </div>
                 </div>            
                 <div class='input-append date form-group".(Session::getError('exerciseEndDate') ? " has-error" : "")."' id='enddatepicker' data-date='$exerciseEndDate' data-date-format='dd-mm-yyyy'>
                     <label for='exerciseEndDate' class='col-sm-2 control-label'>$langExerciseEnd:</label>
                     <div class='col-sm-10'>
                        <div class='input-group'>
                            <span class='input-group-addon'>
                              <input style='cursor:pointer;' type='checkbox' id='enableEndDate' name='enableEndDate' value='1'".($enableEndDate ? ' checked' : '').">
                            </span>                           
                            <input class='form-control' name='exerciseEndDate' id='exerciseEndDate' type='text' value='$exerciseEndDate'".($enableEndDate ? '' : ' disabled').">                                                         
                        </div>
                        <span class='help-block'>".(Session::hasError('exerciseEndDate') ? Session::getError('exerciseEndDate') : "&nbsp;&nbsp;&nbsp;<i class='fa fa-share fa-rotate-270'></i> $langExerciseEndHelpBlock")."</span>
                     </div>
                 </div>
                 <div class='form-group'>
                     <label for='exerciseTempSave' class='col-sm-2 control-label'>$langTemporarySave:</label>
                     <div class='col-sm-10'>            
                         <div class='radio'>
                           <label>
                             <input type='radio' name='exerciseTempSave' value='0' ".(($exerciseTempSave==0)? 'checked' : '').">
                             $langDeactivate
                           </label>
                         </div>
                         <div class='radio'>
                           <label>
                             <input type='radio' name='exerciseTempSave' value='1' ".(($exerciseTempSave==1)? 'checked' : '').">
                             $langActivate
                           </label>
                         </div>
                     </div>
                 </div>
                 <div class='form-group ".(Session::getError('exerciseTimeConstraint') ? "has-error" : "")."'>
                   <label for='exerciseTimeConstraint' class='col-sm-2 control-label'>$langExerciseConstrain:</label>
                   <div class='col-sm-10'>
                     <input type='text' class='form-control' name='exerciseTimeConstraint' id='exerciseTimeConstraint' value='$exerciseTimeConstraint' placeholder='$langExerciseConstrain'>
                     <span class='help-block'>".(Session::getError('exerciseTimeConstraint') ? Session::getError('exerciseTimeConstraint') : "$langExerciseConstrainUnit ($langExerciseConstrainExplanation)")."</span>
                   </div>
                 </div>
                 <div class='form-group ".(Session::getError('exerciseAttemptsAllowed') ? "has-error" : "")."'>
                   <label for='exerciseAttemptsAllowed' class='col-sm-2 control-label'>$langExerciseAttemptsAllowed:</label>
                   <div class='col-sm-10'>
                     <input type='text' class='form-control' name='exerciseAttemptsAllowed' id='exerciseAttemptsAllowed' value='$exerciseAttemptsAllowed' placeholder='$langExerciseConstrain'>
                     <span class='help-block'>".(Session::getError('exerciseAttemptsAllowed') ? Session::getError('exerciseAttemptsAllowed') : "$langExerciseAttemptsAllowedUnit ($langExerciseAttemptsAllowedExplanation)")."</span>
                   </div>
                 </div>
                 <div class='form-group'>
                     <label for='exerciseDescription' class='col-sm-2 control-label'>$langRandomQuestions:</label>
                     <div class='col-sm-10'>            
                         <div class='radio'>
                           <label>
                             <input type='radio' name='questionDrawn' class='questionDrawnRadio' value='0' ".(($randomQuestions == 0)? 'checked' : '').(($randomQuestions > 0 && $randomQuestions < 32767)? ' disabled' : '').">
                             $langDeactivate
                           </label>
                         </div>
                         <div class='radio'>
                           <label>
                             <input type='radio' name='questionDrawn' class='questionDrawnRadio' value='32767'".(($randomQuestions > 0)? ' checked' : '').(($randomQuestions > 0 && $randomQuestions < 32767)? ' disabled' : '').">
                             $langActivate
                           </label>
                         </div>
                     </div>
                 </div>                
                 <div class='form-group ".(($randomQuestions > 0)? '' : 'hidden')."'>
                    <div class='col-sm-5 col-sm-offset-2'>                 
                        <input type='text' class='form-control' name='questionDrawn' id='questionDrawnInput' value='".(($randomQuestions < 32767) ? $randomQuestions : null)."'".(($randomQuestions > 0 && $randomQuestions < 32767)? '' : 'disabled').">
                    </div>
                    <div class='col-sm-5'>                 
                        <div class='checkbox'>
                          <label>
                            <input id='randomDrawnSubset' value='1' type='checkbox' ".(($randomQuestions > 0 && $randomQuestions < 32767)? 'checked' : '').">
                            $langFromRandomQuestions
                          </label>
                        </div> 
                    </div>                   
                 </div>                    
                 <div class='form-group'>
                     <label for='dispresults' class='col-sm-2 control-label'>$langAnswers:</label>
                     <div class='col-sm-10'>            
                         <div class='radio'>
                           <label>
                             <input type='radio' name='dispresults' value='1' ".(($displayResults == 1)? 'checked' : '').">
                             $langAnswersDisp
                           </label>
                         </div>
                         <div class='radio'>
                           <label>
                             <input type='radio' name='dispresults' value='0' ".(($displayResults == 0)? 'checked' : '').">
                             $langAnswersNotDisp
                           </label>
                         </div>
                         <div id='answersDispLastAttempt' class='radio".($exerciseAttemptsAllowed ? '' : ' hidden')."'>
                           <label>
                             <input type='radio' name='dispresults' value='3' ".(($displayResults == 3)? 'checked' : '').">
                             $langAnswersDispLastAttempt
                           </label>
                         </div>                         
                     </div>
                 </div>
                 <div class='form-group'>
                     <label for='dispresults' class='col-sm-2 control-label'>$langScore:</label>
                     <div class='col-sm-10'>            
                         <div class='radio'>
                           <label>
                             <input type='radio' name='dispscore' value='1' ".(($displayScore == 1)? 'checked' : '').">
                             $langScoreDisp
                           </label>
                         </div>
                         <div class='radio'>
                           <label>
                             <input type='radio' name='dispscore' value='0' ".(($displayScore == 0)? 'checked' : '').">
                             $langScoreNotDisp
                           </label>
                         </div>
                     </div>
                 </div>
                 " . Tag::tagInput($exerciseId) . "

                 <div class='form-group'>
                   <div class='col-sm-offset-2 col-sm-10'>
                     <input type='submit' class='btn btn-primary' name='submitExercise' value='".(isset($_GET['NewExercise']) ? $langCreate : $langModify)."'>
                     <a href='".(($exerciseId) ? "admin.php?course=$course_code&exerciseId=$exerciseId" : "index.php?course=$course_code")."' class='btn btn-default'>$langCancel</a>    
                   </div>
                 </div>
                 
             </fieldset>
             </form>
        </div>";    
} else {
    if ($displayResults == 1) {
        $disp_results_message = $langAnswersDisp;
    } elseif ($displayResults == 0) {
        $disp_results_message = $langAnswersNotDisp;
    } else {
        $disp_results_message = $langAnswersDispLastAttempt;
    }
    $disp_score_message = ($displayScore == 1) ? $langScoreDisp : $langScoreNotDisp;
    $exerciseDescription = standard_text_escape($exerciseDescription);
    $exerciseStartDate = $exerciseStartDate;
    $exerciseEndDate = isset($exerciseEndDate) && !empty($exerciseEndDate) ? $exerciseEndDate : $m['no_deadline'];
    $exerciseType = ($exerciseType == 1) ? $langSimpleExercise : $langSequentialExercise ;
    $exerciseTempSave = ($exerciseTempSave ==1) ? $langActive : $langDeactivate;
    $moduleTag = new ModuleElement($exerciseId);    
    $tool_content .= action_bar(array(
        array('title' => $langBack,
            'url' => "index.php?course=$course_code",
            'icon' => 'fa-reply',
            'level' => 'primary-label'
        )
    ));    
    $tool_content .= "
    <div class='panel panel-primary'>
        <div class='panel-heading'>
            <h3 class='panel-title'>$langInfoExercise &nbsp;". icon('fa-edit', $langModify, "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;exerciseId=$exerciseId&amp;modifyExercise=yes") ."</h3>
        </div>
        <div class='panel-body'>
            <div class='row  margin-bottom-fat'>
                <div class='col-sm-3'>
                    <strong>$langExerciseName:</strong>
                </div>
                <div class='col-sm-9'>
                    " . q($exerciseTitle) . "
                </div>                
            </div>
            <div class='row  margin-bottom-fat'>
                <div class='col-sm-3'>
                    <strong>$langExerciseDescription:</strong>
                </div>
                <div class='col-sm-9'>
                    $exerciseDescription
                </div>                
            </div>
            <div class='row  margin-bottom-fat'>
                <div class='col-sm-3'>
                    <strong>$langExerciseType:</strong>
                </div>
                <div class='col-sm-9'>
                    $exerciseType
                </div>                
            </div>
            <div class='row  margin-bottom-fat'>
                <div class='col-sm-3'>
                    <strong>$langExerciseStart:</strong>
                </div>
                <div class='col-sm-9'>
                    $exerciseStartDate
                </div>                
            </div>
            <div class='row  margin-bottom-fat'>
                <div class='col-sm-3'>
                    <strong>$langExerciseEnd:</strong>
                </div>
                <div class='col-sm-9'>
                    $exerciseEndDate
                </div>                
            </div>  
            <div class='row  margin-bottom-fat'>
                <div class='col-sm-3'>
                    <strong>$langTemporarySave:</strong>
                </div>
                <div class='col-sm-9'>
                    $exerciseTempSave
                </div>                
            </div> 
            <div class='row  margin-bottom-fat'>
                <div class='col-sm-3'>
                    <strong>$langExerciseConstrain:</strong>
                </div>
                <div class='col-sm-9'>
                    $exerciseTimeConstraint $langExerciseConstrainUnit
                </div>                
            </div>
            <div class='row  margin-bottom-fat'>
                <div class='col-sm-3'>
                    <strong>$langExerciseAttemptsAllowed:</strong>
                </div>
                <div class='col-sm-9'>
                    $exerciseAttemptsAllowed $langExerciseAttemptsAllowedUnit
                </div>                
            </div>
            <div class='row  margin-bottom-fat'>
                <div class='col-sm-3'>
                    <strong>$langRandomQuestions:</strong>
                </div>
                <div class='col-sm-9'>
                    $langSelection $randomQuestions $langFromRandomQuestions
                </div>                
            </div> 
            <div class='row  margin-bottom-fat'>
                <div class='col-sm-3'>
                    <strong>$langAnswers:</strong>
                </div>
                <div class='col-sm-9'>
                    $disp_results_message
                </div>                
            </div>
            <div class='row  margin-bottom-fat'>
                <div class='col-sm-3'>
                    <strong>$langScore:</strong>
                </div>
                <div class='col-sm-9'>
                    $disp_score_message
                </div>                
            </div>";
        $tags_list = $moduleTag->showTags();
        if ($tags_list)            
            $tool_content .= "
            <div class='row  margin-bottom-fat'>
                <div class='col-sm-3'>
                    <strong>$langTags:</strong>
                </div>
                <div class='col-sm-9'>
                    $tags_list                       
                </div>                
            </div>";
            
    $tool_content .= "            
        </div>
    </div>";
}
