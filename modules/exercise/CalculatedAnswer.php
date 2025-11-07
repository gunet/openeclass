<?php

require_once 'answer.class.php';

class CalculatedAnswer extends \QuestionType
{

    public function __destruct() {
        unset($this->answer_object);
    }

    public function PreviewQuestion(): string
    {
        global $langScore, $langAnswer, $langComment;

        $html_content = "
            <tr>
                <td>#</td>
                <td><strong>$langAnswer</strong></td>
                <td><strong>$langComment</strong></td>
           </tr>";

        $nbrAnswers = $this->answer_object->selectNbrAnswers();
        $answer_ids = range(1, $nbrAnswers);

        foreach ($answer_ids as $answer_id) {
            $answerTitle = $this->answer_object->getTitle($answer_id);
            $arrTitle = explode(':', $answerTitle);
            $answerVal = ((count($arrTitle) > 1) ? $arrTitle[1] : ''); // Contains the correct answer of the question
            $answerComment = standard_text_escape($this->answer_object->getComment($answer_id));
            $answerCorrect = $this->answer_object->isCorrect($answer_id);
            $answerWeighting = $this->answer_object->getWeighting($answer_id);
            if ($answerCorrect) {
                $icon_choice = icon("fa-regular fa-square-check");
            } else {
                $icon_choice = icon("fa-regular fa-square");
            }
            $html_content .= "
                  <tr>
                    <td>$icon_choice</td>
                    <td>" . standard_text_escape($answerVal) . " <strong><small class='text-nowrap'>($langScore: $answerWeighting)</small></strong></td>
                    <td>" . $answerComment . "</td>
                  </tr>";
        }

        return $html_content;
    }

    public function AnswerQuestion($question_number, $exerciseResult = [], $options = []): string
    {
        global $langClearChoice;

        $html_content = "";

        $nbrAnswers = $this->answer_object->selectNbrAnswers();
        $answer_object_ids = range(1, $nbrAnswers);
        if (in_array('shuffle_answers', $options)) { // is option `shuffle answers` enabled?
            shuffle($answer_object_ids);
        }

        $q_data = Database::get()->querySingle("SELECT `description`,options FROM exercise_question WHERE id = ?d", $this->question_id);
        if ($q_data) {
            $des_arr = unserialize($q_data->description);
            $question_description = $des_arr['question_description'];
            $html_content .= "<div class='col-12 my-3'>$question_description</div>";
            $arithmetic_expression_str = $this->answer_object->replaceItemsBracesWithWildCards($des_arr['arithmetic_expression'], $this->question_id);
            $html_content .= "<div class='col-12 my-3'>$arithmetic_expression_str</div>";
        }

        $html_content .= "<input type='hidden' name='choice[{$this->question_id}]' value=''>";

        foreach ($answer_object_ids as $answerId) {
            $answerTitle = $this->answer_object->getTitle($answerId);
            $arrTitle = explode(':', $answerTitle);
            $answerVal = ((count($arrTitle) > 1) ? $arrTitle[1] : ''); // predefined answers
            $checked = '';
            $uniqueAnswer = '';
            if (isset($exerciseResult[$this->question_id]) && $exerciseResult[$this->question_id] != '') {
                $arrExerResults = explode('|', $exerciseResult[$this->question_id]);
                if (count($arrExerResults) == 2 && $arrExerResults[0] == $answerVal) {
                    $checked = 'checked';
                }
                $uniqueAnswer = $arrExerResults[0];
            }
            if (count($answer_object_ids) > 1) { // multiple answers with radios buttons
                $html_content .= "
                    <div class='radio mb-1'>
                        <label>
                            <input type='radio' name='choice[$this->question_id]' value='{$answerVal}|{$answerId}' $checked onClick='updateQuestionNavButton(" . $question_number . ");'>                        
                            " . standard_text_escape($answerVal) . "
                        </label>
                    </div>";
            } elseif (count($answer_object_ids) == 1) { // unique answer with text
                $html_content .= "<input type='hidden' name='answer_id_choice[$this->question_id]' value='{$answerId}'>";
                $html_content .= "<input type='text' class='form-control input-text-calculated' name='choice[$this->question_id]' value='{$uniqueAnswer}' onclick='updateListenerCalculated({$question_number})' style='max-width:300px;'>";
            }
        }

        if (count($answer_object_ids) > 1) {
            $html_content .= "<button class='float-end clearSelect btn btn-outline-secondary mt-0'><i class='fa fa-solid fa-xmark'></i>&nbsp;$langClearChoice</button>";
        }

        return $html_content;
    }

    public function QuestionResult($choice, $eurid, $regrade, $extra_type = ''): string
    {

        global $langSelect, $langCorrectS, $langIncorrectS, $questionScore, $langYourOwnAnswerIs;

        $html_content = '';

        $questionId = $this->answer_object->getQuestionId();

        $nbrAnswers = $this->answer_object->selectNbrAnswers();
        $answer_object_ids = range(1, $nbrAnswers);

        foreach ($answer_object_ids as $answerId) {
            $grade = 0;
            $answerTitle = standard_text_escape($this->answer_object->getTitle($answerId));
            $tmpArr = explode(':', $answerTitle);
            if (count($tmpArr) == 2) {
                $answerTitle = round(floatval($tmpArr[1]), 2);
            }
            $answerComment = $this->answer_object->getComment($answerId);
            if ($this->answer_object->get_user_calculated_answer($questionId, $eurid) != null) {
                if (round(floatval($this->answer_object->get_user_calculated_answer($questionId, $eurid)), 2) == $answerTitle) {
                    $answerCorrect = true;
                } else {
                    $answerCorrect = false;
                }
            } else {
                $answerCorrect = false;
            }

            $studentChoice = ($choice == $answerId) ? 1 : 0;
            if ($studentChoice) {
                // Get the user's grade.
                $answerWeighting = $this->answer_object->get_user_grade_for_answered_calculated_question($eurid, $questionId, $answerId);
                $questionScore += $answerWeighting;
                $grade = $answerWeighting;
            }

            if ($regrade) {
                Database::get()->query('UPDATE exercise_answer_record
                        SET weight = ?f
                        WHERE eurid = ?d AND question_id = ?d AND answer_id = ?d',
                    $grade, $eurid, $this->question_id, $answerId);
            }

            $html_content .= "<tr><td><div class='d-flex align-items-center'>";
            $answer_icon = '';
            if ($studentChoice) {
                $student_choice_icon = "fa-regular fa-square-check";
                $pdf_student_choice_icon = "<label class='label-container' aria-label='$langSelect'><input type='checkbox' checked='checked'><span class='checkmark'></span></label>";
                $style = '';
                if ($answerCorrect) {
                    $answer_icon = "fa-solid fa-check text-success";
                } else {
                    $answer_icon = "fa-solid fa-xmark text-danger";
                }
            } else {
                $student_choice_icon = "fa-regular fa-square";
                $pdf_student_choice_icon = "<label class='label-container' aria-label='$langSelect'><input type='checkbox'><span class='checkmark'></span></label>";
                $style = "visibility: hidden;";
            }
            if (isset($_GET['pdf'])) {
                $html_content .= "<span>$pdf_student_choice_icon</span>";
            } else {
                $html_content .= "<div class='d-flex align-items-center m-1 me-2'><span class='$student_choice_icon p-3'></span>";
                $html_content .= "<span style='$style' class='$answer_icon'></span></div>";
            }

            $html_content .= $answerTitle;
            if ($answerCorrect) {
                $html_content .= "&nbsp;<span class='text-success text-nowrap'><small class='text-success text-nowrap'>($langCorrectS)</small></span>";
            } else {
                $html_content .= "&nbsp;<span class='text-danger text-nowrap'><small class='text-danger text-nowrap'>($langIncorrectS)</small></span>";
            }
            $html_content .= "</div>";
            if ($studentChoice or $answerCorrect) {
                $html_content .= "<div class='d-flex align-items-center'><small><span class='help-block'>" . standard_text_escape(nl2br($answerComment)) . "</span></small></div>";
            }
            $html_content .= "</div>";
            $html_content .= "</td></tr>";

        }

        if (count($answer_object_ids) == 1) { // unique answer as text
            $userHasAnswered = $this->answer_object->get_user_calculated_answer($questionId, $eurid);
            $html_content .= "<tr><td>$langYourOwnAnswerIs <span class='TextBold'>$userHasAnswered</span></td></tr>";
        }

        return $html_content;

    }

}
