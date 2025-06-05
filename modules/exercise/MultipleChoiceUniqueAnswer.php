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
                    <td>" . standard_text_escape($answerTitle) . " <strong><small>($langScore: $answerWeighting)</small></strong></td>
                    <td>" . $answerComment . "</td>
                  </tr>";
        }
        return $html_content;
    }

    public function AnswerQuestion($question_number, $exerciseResult = [], $options = []): string
    {
        global $langClearChoice;

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
        return $html_content;
    }

}
