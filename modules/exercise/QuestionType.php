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
            return "<div class='card-footer d-flex flex-wrap justify-content-start align-items-center gap-3'>
                        <span class='fw-bold text-nowrap'>
                            $langCertainty
                        </span>
                        <div class='radio d-flex justify-content-start align-items-center gap-2 flex-wrap mt-1'>
                            <div class='form-check form-check-inline mb-0 d-flex justify-content-start align-items-start'>
                                <input id='AccessibilityCertaintyCheck_{$qid}_1' class='form-check-input' type='radio' name='certainty[$qid]' value='1' $choice[1]>
                                <label for='AccessibilityCertaintyCheck_{$qid}_1' class='form-check-label'>$langNotKnow</label>
                            </div>
                            <div class='form-check form-check-inline mb-0 d-flex justify-content-start align-items-start'>
                                <input id='AccessibilityCertaintyCheck_{$qid}_2' class='form-check-input' type='radio' name='certainty[$qid]' value='2' $choice[2]>
                                <label for='AccessibilityCertaintyCheck_{$qid}_2' class='form-check-label'>$langNotSure</label>
                            </div>
                            <div class='form-check form-check-inline mb-0 d-flex justify-content-start align-items-start'>
                                <input id='AccessibilityCertaintyCheck_{$qid}_3' class='form-check-input' type='radio' name='certainty[$qid]' value='3' $choice[3]>
                                <label for='AccessibilityCertaintyCheck_{$qid}_3' class='form-check-label'>$langSure</label>
                            </div>
                        </div>
                    </div>";
        } else {
            return '';
        }
    }
}
