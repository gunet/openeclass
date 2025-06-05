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
        $questionId = $this->question_id;
        $text = (isset($exerciseResult[$questionId])) ? $exerciseResult[$questionId] : '';
        return rich_text_editor("choice[$questionId]", 14, 90, $text, options: $options);
    }
}
