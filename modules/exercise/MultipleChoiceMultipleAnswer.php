<?php

require_once 'answer.class.php';

class MultipleChoiceMultipleAnswer extends QuestionType
{
    public function __destruct() {
        unset($this->answer_object);
    }

    /**
     * @brief preview question
     * @return string
     */
    public function PreviewQuestion(): string
    {
        // TODO: Implement PreviewQuestion() method.
    }

    /**
     * @brief display answer during execution
     * @param $question_number
     * @param $options
     * @return string
     */
    public function AnswerQuestion($question_number, $exerciseResult = [], $options = []): string
    {
        global $langSelect;

        $nbrAnswers = $this->answer_object->selectNbrAnswers();
        $answer_object_ids = range(1, $nbrAnswers);
        if (in_array('shuffle_answers', $options)) { // is option `shuffle answers` enabled?
            shuffle($answer_object_ids);
        }

        $html_content = "<input type='hidden' name='choice[$this->question_id]' value='0'>";

        foreach ($answer_object_ids as $answerId) {
            $answerTitle = $this->answer_object->getTitle($answerId);
            if (is_null($this->answer_object) or $this->answer_object == '') {  // don't display blank or empty answers
                continue;
            }
            $checked = (isset($exerciseResult[$this->question_id][$answerId]) && $exerciseResult[$this->question_id][$answerId] == 1) ? 'checked="checked"' : '';
            $html_content .= "
                        <div class='checkbox mb-1'>
                            <label class='label-container' aria-label='$langSelect'>
                                <input type='checkbox' name='choice[$this->question_id][$answerId]' value='1' $checked onClick='updateQuestionNavButton(" . $question_number . ");'>
                                <span class='checkmark'></span>
                                " . standard_text_escape($answerTitle) . "
                          </label>
                        </div>";
        }
        if (isset($eurid)) {
            $certainty_user_choice = $this->answer_object->get_user_certainty_answer_choice($this->question_id, $eurid);
        } else {
            $certainty_user_choice = null;
        }
        $html_content .= $this->CertaintyBasedButtons($this->question_id, $certainty_user_choice);
        return $html_content;
    }

    public function QuestionResult($choice, $eurid, $regrade, $extra_type = false): string
    {
        global $langSelect, $langCorrectS, $langIncorrectS, $questionScore, $langQuestionFeedback;

        $html_content = '';

        $nbrAnswers = $this->answer_object->selectNbrAnswers();
        $answer_object_ids = range(1, $nbrAnswers);

        foreach ($answer_object_ids as $answerId) {
            $grade = 0;
            $answerTitle = standard_text_escape($this->answer_object->getTitle($answerId));
            $answerComment = $this->answer_object->getComment($answerId);
            $answerCorrect = $this->answer_object->isCorrect($answerId);
            $answerWeighting = $this->answer_object->getWeighting($answerId);

            $studentChoice = @$choice[$answerId];
            if ($studentChoice) {
                $questionScore += $answerWeighting;
                $grade = $answerWeighting;
            }

            if ($regrade) {
                Database::get()->query('UPDATE exercise_answer_record
                        SET weight = ?f
                        WHERE eurid = ?d AND question_id = ?d AND answer_id = ?d',
                    $grade, $eurid, $this->question_id, $answerId);
            }

            $answer_class = '';
            $student_choice_icon = '';
            if ($answerCorrect) {
                $answer_class = "correct_answer";
                if ($studentChoice) {
                    $student_choice_icon = "fa-solid fa-check text-success";
                }
            }

            if ($studentChoice && !$answerCorrect) {
                $answer_class = "wrong_answer";
                $student_choice_icon = "fa-solid fa-xmark text-danger";
            }

            $selected = '';
            if ($studentChoice) {
                $selected = 'selected';
            }

//                if ($answerCorrect) {
//                    $answer_icon = "fa-solid fa-check text-success";
//                } else {
//                    $answer_icon = "fa-solid fa-xmark text-danger";
//                }

            $html_content .= "<tr><td class='" . ($studentChoice ? 'p-3' : 'p-2') . "'><div class='$answer_class $selected'><div class='d-flex justify-content-between align-items-center'><div class='d-flex align-items-center p-1'>";
            $answer_icon = '';
            if ($studentChoice) {

                $pdf_student_choice_icon = "<label class='label-container' aria-label='$langSelect'><input type='checkbox' checked='checked'><span class='checkmark'></span></label>";
                $style = '';

            } else {

                $pdf_student_choice_icon = "<label class='label-container' aria-label='$langSelect'><input type='checkbox'><span class='checkmark'></span></label>";
                $style = "visibility: hidden;";
            }
            if (isset($_GET['pdf'])) {
                $html_content .= "<span>$pdf_student_choice_icon</span>";
            } else {
                $html_content .= "<div class='d-flex align-items-center m-1 me-2'><span class='$student_choice_icon'></span>";
                $html_content .= "<span style='$style' class='$answer_icon'></span></div>";
            }

            if ($answerCorrect) {
                $html_content .= '<strong>'.$answerTitle.'</strong>';
            } else {
                $html_content .= $answerTitle;
            }
            $html_content .= "</div>";
            if ($studentChoice) {
                $html_content .= "<strong class='p-1 pe-2'>" . ($answerWeighting > 0 ? '+' : '') . $answerWeighting . "</strong>";
            }
//            if ($answerCorrect) {
//                $html_content .= "&nbsp;<span><small class='text-success text-nowrap'>($langCorrectS)</small></span>";
//            } else {
//                $html_content .= "&nbsp;<span><small class='text-danger text-nowrap'>($langIncorrectS)</small></span>";
//            }
            $html_content .= "</div>";
            if ($studentChoice && $answerComment != '') {
                $html_content .= "<div style='background-color: #e9ecef' class='p-1 ps-4'>" . $langQuestionFeedback . ": " . standard_text_escape(nl2br($answerComment)) . "</div>";
            }
            $html_content .= "</div>";
            $html_content .= "</div></td></tr>";

        }

        return $html_content;
    }
}
