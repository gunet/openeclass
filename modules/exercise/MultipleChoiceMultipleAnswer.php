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
        return $html_content;
    }
}
