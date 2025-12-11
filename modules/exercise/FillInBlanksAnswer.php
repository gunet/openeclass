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

    public function QuestionResult($choice, $eurid, $regrade, $extra_type = false): string
    {
        global $langOr, $langSelect, $questionScore;

        $html_content = '';

        $nbrAnswers = $this->answer_object->selectNbrAnswers();
        $answer_object_ids = range(1, $nbrAnswers);

        foreach ($answer_object_ids as $answerId) {
            $answerTitle = standard_text_escape($this->answer_object->getTitle($answerId));
            list($answer, $answerWeighting) = Question::blanksSplitAnswer($answerTitle);
            // splits weightings that are joined with a comma
            $answerWeighting = explode(',', $answerWeighting);
            // we save the answer because it will be modified
            $temp = $answer;
            $answer = '';
            $j = 1;
            // the loop will stop at the end of the text
            while (true) {
                // quits the loop if there are no more blanks
                if (($pos = strpos($temp, '[')) === false) {
                    // adds the end of the text
                    $answer .= q($temp);
                    break;
                }
                // adds the piece of text that is before the blank and ended by [
                $answer .= substr($temp, 0, $pos + 1);
                $temp = substr($temp, $pos + 1);
                // quits the loop if there are no more blanks
                if (($pos = strpos($temp, ']')) === false) {
                    // adds the end of the text
                    $answer .= q($temp);
                    break;
                }
                $choice[$j] = canonicalize_whitespace($choice[$j]);
                // if the word entered is the same as the one defined by the professor
                $canonical_choice = $extra_type == 'tolerant' ? remove_accents($choice[$j]) : $choice[$j];
                $canonical_match = $extra_type == 'tolerant' ? remove_accents(substr($temp, 0, $pos)) : substr($temp, 0, $pos);
                $right_answers = array_map('canonicalize_whitespace',
                    preg_split('/\s*\|\s*/', $canonical_match));
                if (in_array($canonical_choice, $right_answers)) {
                    // gives the related weighting to the student
                    $questionScore += $answerWeighting[$j-1];
                    if ($regrade) {
                        Database::get()->query('UPDATE exercise_answer_record
                                        SET weight = ?f
                                        WHERE eurid = ?d AND question_id = ?d AND answer_id = ?d',
                            $answerWeighting[$j-1], $eurid, $this->question_id, $j);
                    }
                    // increments total score
                    // adds the word in green at the end of the string
                    $answer .= '<strong>' . q($choice[$j]) . '</strong>';
                    if (isset($_GET['pdf'])) {
                        $icon = "<label class='label-container' aria-label='$langSelect'><input type='checkbox' checked='checked'><span class='checkmark'></span></label>";
                    } else {
                        $icon = "<span class='fa-solid fa-check text-success'></span>";
                    }
                }
                // else if the word entered is different from the one defined by the professor
                elseif ($choice[$j] !== '') {
                    // adds the word in red at the end of the string and strikes it
                    $answer .= '<span class="text-danger"><s>' . q($choice[$j]) . '</s></span>';
                    $icon = "<span class='fa-solid fa-xmark text-danger'></span>";
                } else {
                    // adds tabulation if no word has been typed by the student
                    $answer .= '&nbsp;&nbsp;&nbsp;';
                    $icon = "<span class='fa-solid fa-xmark text-danger'></span>";
                }
                // adds the correct word, followed by ] to close the blank
                $answer .= ' / <span class="text-success"><strong>' .
                    preg_replace('/\s*\|\s*/', " </strong>$langOr<strong> ", q(substr($temp, 0, $pos))) .
                    '</strong></span>';
                $answer .= "]";
                $answer .= "&nbsp;&nbsp;$icon";
                $j++;
                $temp = substr($temp, $pos + 1);
            }
            $html_content .= "<tr><td>" . standard_text_escape(nl2br($answer)) . "</td></tr>";
        }
        return $html_content;
    }

}
