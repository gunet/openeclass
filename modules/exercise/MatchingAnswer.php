<?php

require_once 'answer.class.php';

class MatchingAnswer extends QuestionType
{
    public function __destruct() {
        unset($this->answer_object);
    }
    public function PreviewQuestion(): string
    {
        global $langScore, $langChoice, $langCorrespondsTo;

        $html_content = '';
        $html_content .= "
            <tr>
               <td><strong>$langChoice</strong></td>
               <td><strong>$langCorrespondsTo</strong></td>
            </tr>";

        $nbrAnswers = $this->answer_object->selectNbrAnswers();
        $answer_ids = range(1, $nbrAnswers);

        foreach ($answer_ids as $answer_id) {
            $answerTitle = $this->answer_object->getTitle($answer_id);
            $answerCorrect = $this->answer_object->isCorrect($answer_id);
            $answerWeighting = $this->answer_object->getWeighting($answer_id);
            if ($answerCorrect) {
                $html_content .= "
                  <tr>
                    <td>" . standard_text_escape($answerTitle) . "</td>
                    <td>{$answerTitle[$answerCorrect]}&nbsp;&nbsp;&nbsp;<strong><small class='text-nowrap'>($langScore: $answerWeighting)</small></strong></td>
                  </tr>";
            }
        }
        return $html_content;
    }

    public function AnswerQuestion($question_number, $exerciseResult = [], $options = []): string
    {
        global $langColumnA, $langColumnB, $langMakeCorrespond;

        $html_content = "<div class='table-responsive'><table class='table-default'>
                            <thead><tr class='list-header'>
                              <th>$langColumnA</th>
                              <th>$langMakeCorrespond</th>
                              <th>$langColumnB</th>
                            </tr></thead>";
        $cpt1 = 'A';
        $cpt2 = 1;
        $Select = array();
        $questionId = $this->question_id;
        $nbrAnswers = $this->answer_object->selectNbrAnswers();
        $answer_object_ids = range(1, $nbrAnswers);
        foreach ($answer_object_ids as $answerId) {
            $answerTitle = $this->answer_object->getTitle($answerId);
            if (is_null($this->answer_object) or $this->answer_object == '') {  // don't display blank or empty answers
                continue;
            }
            $answerCorrect = $this->answer_object->isCorrect($answerId);

            if (!$answerCorrect) {
                // options (A, B, C, ...) that will be put into the list-box
                $Select[$answerId]['Lettre'] = $cpt1++;
                // answers that will be shown on the right side
                $Select[$answerId]['Reponse'] = $answerTitle;
            } else {
                $html_content .= "<tr>
                                  <td><strong>$cpt2.</strong> " . q($answerTitle) . "</td>
                                  <td><div class='text-start'>
                                   <select class='form-select fill-predefined-answers' name='choice[$questionId][$answerId]' onChange='questionUpdateListener($question_number, $questionId);'>
                                     <option value='0'>--</option>";

                // fills the list-box
                foreach ($Select as $key => $val) {
                    $selected = (isset($exerciseResult[$questionId][$answerId]) && $exerciseResult[$questionId][$answerId] == $key) ? 'selected="selected"' : '';
                    $html_content .= "<option value=\"" . q($key) . "\" $selected>{$val['Lettre']}</option>";
                }
                $html_content .= "</select></div></td><td>";
                if (isset($Select[$cpt2])) {
                    $html_content .= '<strong>' . q($Select[$cpt2]['Lettre']) . '.</strong> ' . q($Select[$cpt2]['Reponse']);
                } else {
                    $html_content .= '&nbsp;';
                }
                $html_content .= "</td></tr>";
                $cpt2++;
                // if the left side of the "matching" has been completely shown
                if ($answerId == $nbrAnswers) {
                    // if it remains answers to shown on the right side
                    while (isset($Select[$cpt2])) {
                        $html_content .= "<tr class='even'>
                                              <td>&nbsp;</td>
                                              <td>&nbsp;</td>
                                              <td>" . "<strong>" . q($Select[$cpt2]['Lettre']) . ".</strong> " . q($Select[$cpt2]['Reponse']) . "</td>
                                          </tr>";
                        $cpt2++;
                    } // end while()
                }  // end if()
            }
        }

        if ($nbrAnswers > 0) {
            $html_content .= "</table></div>";
        }
        return $html_content;
    }
}
