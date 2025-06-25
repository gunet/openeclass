<?php

require_once 'answer.class.php';

class CalculatedAnswer extends \QuestionType
{

    public function __destruct() {
        unset($this->answer_object);
    }

    public function PreviewQuestion(): string
    {
        global $langAnswer;

        $html_content = "<tr class='active'><td><strong>$langAnswer</strong></td></tr>";

        // To be implemented

        return $html_content;
    }

    public function AnswerQuestion($question_number, $exerciseResult = [], $options = []): string
    {
        global $head_content, $course_code;

        $questionId = $this->question_id;
        $html_content = "";

        // To be implemented

        return $html_content;
    }

    public function QuestionResult($choice, $eurid, $regrade, $extra_type = ''): string
    {
        global $questionScore;

        $html_content = '';

        // To be implemented

        return $html_content;
    }
}
