<?php

require_once 'answer.class.php';

abstract class QuestionType {
    public Answer $answer_object;
    public int $question_id;

    public function __construct($qid) {
        $this->question_id = $qid;
        $this->answer_object = new Answer($this->question_id);
    }

    abstract public function PreviewQuestion(): string;
    abstract public function AnswerQuestion($question_number, $exerciseResult = [], $options = []): string;
    abstract public function QuestionResult($choice, $eurid, $regrade, $extra_type = ''): string;

}
