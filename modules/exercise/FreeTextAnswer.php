<?php

require_once 'answer.class.php';

class FreeTextAnswer extends QuestionType
{
    public function __destruct() {
        unset($this->answer_object);
    }

    public function PreviewQuestion(): string
    {
        // TODO: Implement PreviewQuestion() method.
    }

    public function AnswerQuestion($question_number, $exerciseResult = [], $options = []): string
    {
        global $langFreeText;

        $questionId = $this->question_id;
        $text = '';
        $html_content = '';

        if (isset($exerciseResult[$questionId]) && $exerciseResult[$questionId] != '') {
            $text = $exerciseResult[$questionId];
        } 

        $html_content .= "
            <div class='col-12' id='freetext_{$questionId}'>
                " . rich_text_editor("choice[$questionId]", 14, 90, $text, options: $options) . "
            </div>";

        return $html_content;
    }


    public function QuestionResult($choice, $eurid, $regrade, $extra_type = ''): string
    {

        global $questionScore, $question_weight;

        $questionId = $this->question_id;
        $questionScore = $question_weight;
        
        $html_content = '';
        $text = '';

        $text = $choice; // plain text
        $html_content .= "<tr><td>" . purify($text). "</td></tr>";

        return $html_content;

    }
}
