<?php

require_once 'answer.class.php';

class FillInPredefinedAnswer extends QuestionType
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
            if (!empty($answerTitle)) {
                $answer_array = unserialize($answerTitle);
                $answer_text = $answer_array[0]; // answer text
                $correct_answer = $answer_array[1]; // correct answer
                $answer_weight = implode(' : ', $answer_array[2]); // answer weight
            } else {
                break;
            }
            $possible_answers = [];
            // fetch all possible answers
            preg_match_all('/\[[^]]+\]/', $answer_text, $out);
            foreach ($out[0] as $output) {
                $possible_answers[] = explode("|", str_replace(array('[',']'), '', q($output)));
            }
            // find correct answers
            foreach ($possible_answers as $possible_answer_key => $possible_answer) {
                $possible_answer = reindex_array_keys_from_one($possible_answer);
                $correct_answer_string[] = '['. $possible_answer[$correct_answer[$possible_answer_key]] . ']';
            }

            $formatted_answer_text = preg_replace_callback($correct_answer_string,
                function ($string) {
                    return "<span style='color: red;'>$string[0]</span>";
                },
                standard_text_escape(nl2br($answer_text)));
            // format correct answers
            $html_content .= " 
                  <tr>
                    <td>$formatted_answer_text&nbsp;&nbsp;&nbsp;<strong><small>($langScore: $answer_weight)</small></strong>
                    </td>
                  </tr>";
        }
        return $html_content;
    }

    public function AnswerQuestion($question_number, $exerciseResult = [], $options = []): string
    {
        global $langSelect;

        $html_content = "<div class='container-fill-in-the-blank'>";

        $questionId = $this->question_id;
        $nbrAnswers = $this->answer_object->selectNbrAnswers();
        $answer_object_ids = range(1, $nbrAnswers);
        foreach ($answer_object_ids as $answerId) {
            $answerTitle = $this->answer_object->getTitle($answerId);
            if (is_null($this->answer_object) or $this->answer_object == '') {  // don't display blank or empty answers
                continue;
            }
            $temp_string = unserialize($answerTitle);
            $answer_string = $temp_string[0];
            // replaces [choices] with `select` field
            $replace_callback = function ($blank) use ($questionId, $exerciseResult, $question_number, $langSelect) {
                static $id = 0;
                $id++;
                $selection_text = explode("|", str_replace(array('[',']'), ' ', q($blank[0])));
                array_unshift($selection_text, "--- $langSelect ---");
                $value = (isset($exerciseResult[$questionId][$id])) ? ($exerciseResult[$questionId][$id]) : '';
                return selection($selection_text, "choice[$questionId][$id]", $value,"class='form-select fill-in-the-blank' onChange='questionUpdateListener($question_number, $questionId)'");
            };
            $answer_string = preg_replace_callback('/\[[^]]+\]/', $replace_callback, standard_text_escape($answer_string));
            $html_content .= $answer_string;
        }
        $html_content .= "</div>";
        return $html_content;
    }
}
