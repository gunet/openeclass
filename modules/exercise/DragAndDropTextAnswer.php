<?php

require_once 'answer.class.php';

class DragAndDropTextAnswer extends \QuestionType
{

    public function __destruct() {
        unset($this->answer_object);
    }

    public function PreviewQuestion(): string
    {
        global $langAnswer, $langScore, $langBracket, $langThisAnswerIsNotCorrect;

        $html_content = "<tr class='active'><td><strong>$langAnswer</strong></td></tr>";

        $textWithAnswers = $this->answer_object->get_drag_and_drop_text_with_answers();
        $textWithGrades = $this->answer_object->get_drag_and_drop_text_with_grades();
        $gradesOfAnswers = $this->answer_object->get_drag_and_drop_answer_grade();
        $AnswersGradeArr= [];
        foreach ($gradesOfAnswers as $gr) {
            $AnswersGradeArr[] = $gr;
        }
        $AnswersGrade = implode(':', $AnswersGradeArr);
        $html_content .= "<tr>
                           <td>
                            <strong><small class='text-nowrap'>($langScore: $AnswersGrade)</small></strong>";
                    foreach ($textWithAnswers as $index => $val) {
                        $html_content .= "<div class='mt-2'>$langBracket [$index] = $val";
                        if (isset($textWithGrades[$index]) && $textWithGrades[$index] == 0) {
                            $html_content .= "&nbsp;&nbsp;<span class='Accent-200-cl'>($langThisAnswerIsNotCorrect)</span>";
                        }
                        $html_content .= "</div>";
                    }
        $html_content .= "</td>
                         </tr>";

        return $html_content;
    }

    public function AnswerQuestion($question_number, $exerciseResult = [], $options = []): string
    {
        global $langCalcelDroppableItem, $head_content, $course_code, $uid,
               $langConfirmDelete, $langAnalyticsConfirm, $langMarkerDeleted, $langMarkerDeletedError, $langImageUploaded,
               $langImageNotSelected, $langInvalidAnswerValue, $langBlankNotEmpty, $langBlankOtherQuestion, 
               $chooseShapeAndAnswerToContinue, $chooseDrawAShapeForTheAnswerToContinue;

        $questionId = $this->question_id;
        $question_text = $this->answer_object->get_drag_and_drop_text();
        $list_answers = $this->answer_object->get_drag_and_drop_answer_text();
        $question_text = replaceBracketsWithBlanks($question_text, $this->question_id);

        $html_content = "<div class='col-12 mb-4'><small class='Accent-200-cl'>(*)$langCalcelDroppableItem</small></div>";
        $html_content .= "<div class='col-12'>$question_text</div>";
        $html_content .= "<div class='col-12 d-flex justify-content-start align-items-center gap-4 flex-wrap mt-4 border-top-default pt-4' id='words_{$questionId}'>";
        foreach ($list_answers as $an) {
            $html_content .= "<div class='draggable' data-word='{$an}' data-pool-id='words_{$questionId}' onmouseup='updateListenerDragAndDrop({$question_number}, {$questionId})' onclick='updateListenerDragAndDrop({$question_number}, {$questionId})'>$an</div>";
        }
        $html_content .= "</div>";
        $html_content .= "<input type='hidden' name='choice[$questionId]' id='arrInput_{$questionId}'>";

        if (isset($exerciseResult[$questionId]) && !empty($exerciseResult[$questionId])) {
            $userAnswerAsArray = json_decode($exerciseResult[$questionId]);
            $uHasAnswered = json_encode($userAnswerAsArray);
            $html_content .= "<input type='hidden' id='userHasAnswered-$questionId' value='{$uHasAnswered}'>
                              <input type='hidden' class='CourseCodeNow' value='{$course_code}'>";
            $html_content .= "<input type='hidden' id='typeQuestion-$questionId' value='".DRAG_AND_DROP_TEXT."'>";
        }

        $head_content .= "<script type='text/javascript'>        
                                var lang = {
                                    confirmdelete: '" . js_escape($langConfirmDelete) . "',
                                    confirm: '" . js_escape($langAnalyticsConfirm) . "',
                                    markerdeleted: '" . js_escape($langMarkerDeleted) . "',
                                    markerdeletederror: '" . js_escape($langMarkerDeletedError) . "',
                                    imageuploaded: '" . js_escape($langImageUploaded) . "',
                                    imagenotselected: '" . js_escape($langImageNotSelected) . "',
                                    invalidanswervalue : '" . js_escape($langInvalidAnswerValue) . "',
                                    blanknotempty: '" . js_escape($langBlankNotEmpty) . "',
                                    blankotherquestion: '" . js_escape($langBlankOtherQuestion) . "',
                                    chooseShapeAndAnswerToContinue: '" .js_escape($chooseShapeAndAnswerToContinue). "',
                                    chooseDrawAShapeForTheAnswerToContinue: '" . js_escape($chooseDrawAShapeForTheAnswerToContinue) . "'
                                };
                            </script>";
        load_js('drag-and-drop-shapes');

        $head_content .= "<script>
                            document.addEventListener('DOMContentLoaded', function() {
                                drag_and_drop_process();
                            });
                          </script>";

        if (isset($uHasAnswered)) {
        $head_content .= "<script>
                            document.addEventListener('DOMContentLoaded', function() {
                                save_user_answers($questionId);
                            });
                          </script>";
        }

        return $html_content;
    }

    public function QuestionResult($choice, $eurid, $regrade, $extra_type = ''): string
    {
        global $questionScore;

        $html_content = '';
        $arrResult = drag_and_drop_user_results_as_text($eurid, $this->question_id);
        $answer = $arrResult[0]['aboutUserAnswers'];
        $questionScore = $arrResult[0]['aboutUserGrade'];
        $html_content .= "<tr><td>$answer</td></tr>";

        return $html_content;
    }
}
