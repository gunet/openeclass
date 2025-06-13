<?php

require_once 'answer.class.php';

class FillInBlanksAnswer extends QuestionType
{

    public function __destruct() {
        unset($this->answer_object);
    }

    public function PreviewQuestion(): string
    {
        global $langScore, $langAnswer;

        $html_content = "
            <tr class='active'>
                <td><strong>$langAnswer</strong></td>
            </tr>";

        $nbrAnswers = $this->answer_object->selectNbrAnswers();
        $answer_ids = range(1, $nbrAnswers);

        foreach ($answer_ids as $answer_id) {
            $answerTitle = $this->answer_object->getTitle($answer_id);
            list($answerTitle, $answerWeighting) = Question::blanksSplitAnswer($answerTitle);
            $html_content .= "
              <tr>
                <td>" . standard_text_escape(nl2br($answerTitle)) . " <strong><small class='text-nowrap'>($langScore: " . preg_replace('/,/', ' : ', "$answerWeighting") . ")</small></strong></td>
              </tr>";
        }
        return $html_content;
    }

    public function AnswerQuestion($question_number, $exerciseResult = [], $options = []): string
    {

        $html_content = "<div class='container-fill-in-the-blank'>";

        $questionId = $this->question_id;
        $nbrAnswers = $this->answer_object->selectNbrAnswers();
        $answer_object_ids = range(1, $nbrAnswers);
        foreach ($answer_object_ids as $answerId) {
            if (is_null($this->answer_object) or $this->answer_object == '') {  // don't display blank or empty answers
                continue;
            }
            $answerTitle = $this->answer_object->getTitle($answerId);
            // splits text and weightings that are joined with the character '::'
            list($answer) = Question::blanksSplitAnswer($answerTitle);
            // replaces [blank] by an input field
            $replace_callback = function () use ($questionId, $exerciseResult, $question_number) {
                static $id = 0;
                $id++;
                $value = (isset($exerciseResult[$questionId][$id])) ? ('value = "'.q($exerciseResult[$questionId][$id]) .'"') : '';
                return "<input class='form-control fill-in-the-blank' type='text' name='choice[$questionId][$id]' $value onChange='questionUpdateListener(". $question_number . ",". $questionId .");'>";
            };
            $answer = preg_replace_callback('/\[[^]]+\]/', $replace_callback, standard_text_escape($answer));
            $html_content .= $answer;
        }
        $html_content .= "</div>";

        return $html_content;
    }

}
