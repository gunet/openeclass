<?php

require_once 'answer.class.php';

class OrderingAnswer extends \QuestionType
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

        return $html_content;
    }

    public function AnswerQuestion($question_number, $exerciseResult = [], $options = []): string
    {
        global $head_content, $course_code, $langClearChoice;

        $html_content = "";

        $nbrAnswers = $this->answer_object->selectNbrAnswers();

        return $html_content;
    }

    public function QuestionResult($choice, $eurid, $regrade, $extra_type = ''): string
    {

        global $langSelect, $langCorrectS, $langIncorrectS, $questionScore, $langYourOwnAnswerIs;

        $html_content = '';

        $questionId = $this->answer_object->getQuestionId();

        $nbrAnswers = $this->answer_object->selectNbrAnswers();


        return $html_content;

    }

}
