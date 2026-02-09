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

    /**
     * @brief display radio buttons for certainty-based questions
     * @param $qid
     * @return string
     */
    public function CertaintyBasedButtons($qid, $user_choice): string
    {

        global $exerciseCalcGradeMethod, $langSure, $langNotSure, $langNotKnow, $langCertainty;

        $choice[1] = $choice[2] = $choice[3] = '';
        if (!is_null($user_choice)) {
            $choice[$user_choice] = 'checked';
        }

        if ($exerciseCalcGradeMethod == CALC_GRADE_METHOD_CERTAINTY_BASED) {
            if ($user_choice == 0) { // default value
                $choice[0] = 'checked';
                $choice[1] = $choice[2] = '';
            } else if ($user_choice == 1) {
                $choice[0] = '';
                $choice[1] = 'checked';
                $choice[2] = '';
            } else if ($user_choice == 2) {
                $choice[0] = '';
                $choice[1] = '';
                $choice[2] = 'checked';
            }
            return "<div class='card-footer d-flex flex-wrap bg-light justify-content-center border-0 mt-8 p-0 gap-2'>
                                <div class='radio d-flex align-items-center mt-1'>
                                    <span class='pe-4 fw-bold'>
                                        $langCertainty
                                    </span>
                                    <div class='form-check form-check-inline mb-0'>
                                        <input class='form-check-input' type='radio' name='certainty[$qid]' value='1' $choice[1]>
                                        <label class='form-check-label'>$langNotKnow</label>
                                    </div>                                                                        
                                    <div class='form-check form-check-inline mb-0'>
                                        <input class='form-check-input' type='radio' name='certainty[$qid]' value='2' $choice[2]>
                                        <label class='form-check-label'>$langNotSure</label>
                                    </div>
                                    <div class='form-check form-check-inline mb-0'>
                                        <input class='form-check-input' type='radio' name='certainty[$qid]' value='3' $choice[3]>

                                        <label class='form-check-label'>$langSure</label>
                                    </div>
                                </div>
                             </div>";
        } else {
            return '';
        }
    }
}
