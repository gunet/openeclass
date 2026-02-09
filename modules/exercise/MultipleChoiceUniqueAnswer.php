<?php

require_once 'answer.class.php';
class MultipleChoiceUniqueAnswer extends QuestionType
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
                    <td>" . standard_text_escape($answerTitle) . " <strong><small class='text-nowrap'>($langScore: $answerWeighting)</small></strong></td>
                    <td>" . $answerComment . "</td>
                  </tr>";
        }
        return $html_content;
    }

    public function AnswerQuestion($question_number, $exerciseResult = [], $options = []): string
    {
        global $langClearChoice, $eurid;

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

            $checked = (isset($exerciseResult[$this->question_id]) && $exerciseResult[$this->question_id] == $answerId) ? 'checked="checked"' : '';
            $html_content .= "
                <div class='radio mb-1'>
                    <label>
                        <input type='radio' name='choice[$this->question_id]' value='$answerId' $checked onClick='updateQuestionNavButton(" . $question_number . ");'>                        
                        " . standard_text_escape($answerTitle) . "
                    </label>
                </div>";
        }
        $html_content .= "<button class='float-end clearSelect btn btn-outline-secondary mt-0'><i class='fa fa-solid fa-xmark'></i>&nbsp;$langClearChoice</button>";
        $certainty_user_choice = $this->answer_object->get_user_certainty_answer_choice($this->question_id, $eurid);
        $html_content .= $this->CertaintyBasedButtons($this->question_id, $certainty_user_choice);

        return $html_content;
    }


    public function QuestionResult($choice, $eurid, $regrade, $extra_type = ''): string
    {

        global $langSelect, $langCorrectS, $langIncorrectS, $questionScore;

        $html_content = '';

        $nbrAnswers = $this->answer_object->selectNbrAnswers();
        $answer_object_ids = range(1, $nbrAnswers);

        foreach ($answer_object_ids as $answerId) {
            $grade = 0;
            $answerTitle = standard_text_escape($this->answer_object->getTitle($answerId));
            $answerComment = $this->answer_object->getComment($answerId);
            $answerCorrect = $this->answer_object->isCorrect($answerId);
            $answerWeighting = $this->answer_object->getWeighting($answerId);

            $studentChoice = ($choice == $answerId) ? 1 : 0;
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
                $html_content .= "&nbsp;<span><small class='text-success text-nowrap'>($langCorrectS)</small></span>";
            } else {
                $html_content .= "&nbsp;<span><small class='text-danger text-nowrap'>($langIncorrectS)</small></span>";
            }
            $html_content .= "</div>";

            if ($studentChoice && $answerComment != '') {
                $html_content .= "<div style='background-color: #e9ecef' class='p-1 ps-4'>" . standard_text_escape(nl2br($answerComment)) . "</div>";
            }
            $html_content .= "</div>";
            $html_content .= "</td></tr>";
        }

        return $html_content;
    }

}
