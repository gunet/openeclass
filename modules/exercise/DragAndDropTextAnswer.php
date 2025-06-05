<?php

require_once 'answer.class.php';

class DragAndDropTextAnswer extends \QuestionType
{

    public function __destruct() {
        unset($this->answer_object);
    }

    public function PreviewQuestion(): string
    {
        global $langAnswer, $langScore;

        $html_content = "<tr class='active'><td><strong>$langAnswer</strong></td></tr>";

        $questionText = $this->answer_object->get_drag_and_drop_text();
        $gradesOfAnswers = $this->answer_object->get_drag_and_drop_answer_grade();
        $AnswersGradeArr= [];
        foreach ($gradesOfAnswers as $gr) {
            $AnswersGradeArr[] = $gr;
        }
        $AnswersGrade = implode(':', $AnswersGradeArr);
        $html_content .= "
            <tr>
                <td>" . standard_text_escape($questionText) . " <strong><small>($langScore: $AnswersGrade)</small></strong></td>
            </tr>";

        return $html_content;
    }

    public function AnswerQuestion($question_number, $exerciseResult = [], $options = []): string
    {
        global $langCalcelDroppableItem, $head_content;

        $questionId = $this->question_id;
        $question_text = $this->answer_object->get_drag_and_drop_text();
        $list_answers = $this->answer_object->get_drag_and_drop_answer_text();
        $question_text = replaceBracketsWithBlanks($question_text, $this->question_id);

        $html_content = "<div class='col-12 mb-4'><small class='Accent-200-cl'>(*)$langCalcelDroppableItem</small></div>";
        $html_content .= "<div class='col-12'>$question_text</div>";
        $html_content .= "<div class='col-12 d-flex justify-content-start align-items-center gap-4 flex-wrap mt-4' id='words_{$questionId}'>";
        foreach ($list_answers as $an) {
            $html_content .= "<div class='draggable' data-word='{$an}' data-pool-id='words_{$questionId}'>$an</div>";
        }
        $html_content .= "</div>";
        $html_content .= "<input type='hidden' name='choice[$questionId]' id='arrInput_{$questionId}'>";

        //drag_and_drop_process();
        load_js('tools.js');
        $head_content .= "<script>
                            document.addEventListener('DOMContentLoaded', function() {
                                drag_and_drop_process();
                            });
                          </script>";

        return $html_content;
    }
}
