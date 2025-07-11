<?php

require_once 'question.class.php';
require_once 'answer.class.php';

class OrderingAnswer extends \QuestionType
{

    public function __destruct() {
        unset($this->answer_object);
    }

    public function PreviewQuestion(): string
    {
        global $langScore, $langAnswer, $langComment, $langOrdering;

        $html_content = "<tr class='active'><td><strong>$langAnswer</strong></td></tr>";

        $predefinedOrderingAnswers = $this->answer_object->get_ordering_answers();
        $predefinedOrderingGrades = $this->answer_object->get_ordering_answer_grade();

        $Answers = implode('->', $predefinedOrderingAnswers);
        $AnswersGrade = implode(':', $predefinedOrderingGrades);
        $html_content .= "<tr>
                           <td>
                                <strong><small class='text-nowrap'>($langScore: $AnswersGrade)</small></strong>
                                <div><strong>$langOrdering:</strong><span class='ps-2'>$Answers</span></div>";
        $html_content .= "</td>
                         </tr>";

        return $html_content;
    }

    public function AnswerQuestion($question_number, $exerciseResult = [], $options = []): string
    {
        global $head_content, $course_code, $langClearChoice, $langReorder, $urlServer;

        $html_content = "";

        $questionId = $this->answer_object->getQuestionId();
        $nbrAnswers = $this->answer_object->selectNbrAnswers();

        $head_content .= "
            <script src='{$urlServer}/js/sortable/Sortable.min.js'></script>
            <script type='text/javascript'>
            
                let dataObject = [];

                function initialPositions() {
                    // Loop through all items to get their current indexes
                    const items = document.querySelectorAll('#CardList_{$questionId} > .draggable-item');
                    const indexes = Array.from(items).map((item, index) => {
                        return {
                            element: item,
                            currentIndex: index+1,
                            dataValue: item.getAttribute('data-value')
                        };
                    });
                    for (var i = 0; i < indexes.length; i++) {
                        var currentIndex = indexes[i].currentIndex;
                        var currentValue = indexes[i].dataValue;
                        dataObject[currentIndex] = currentValue;
                    }

                    const jsonString = JSON.stringify(dataObject);
                    $('#orderingResponses_{$questionId}').val(jsonString);
                }

                function calculatePositions() {
                    var box = document.getElementById('CardList_{$questionId}');
                    Sortable.create(box, {
                        animation: 350,
                        handle: '.fa-arrows',
                        onEnd: function(evt) {
                            initialPositions();
                        }
                    });
                }

                $(document).ready(function() {
                    initialPositions();
                    calculatePositions();
                });
            </script>
        
        ";
        
        $objQuestion = new Question();
        $objQuestion->read($questionId);
        
        $ordering_answer = $this->answer_object->get_ordering_answers();
        $ordering_answer_grade = $this->answer_object->get_ordering_answer_grade();
        $optionsQ = $objQuestion->selectOptions();
        $arrOptions = json_decode($optionsQ, true);
        $countOrderingAnswers = count($ordering_answer);

        $layoutItems = (isset($arrOptions['layoutItems']) ? $arrOptions['layoutItems'] : '');
        $itemsSelectionType = (isset($arrOptions['itemsSelectionType']) ? $arrOptions['itemsSelectionType'] : '');
        $sizeOfSubset = (isset($arrOptions['sizeOfSubset']) ? $arrOptions['sizeOfSubset'] : '');

        $displayItems = '';
        if (isset($layoutItems) && $layoutItems == 'Horizontal') {
            $displayItems = 'd-flex justify-content-start align-items-center gap-3 flex-wrap';
        } elseif (isset($layoutItems) && $layoutItems == 'Vertical') {
            $displayItems = 'd-flex flex-column gap-3';
        }

        $randomKeys = array_keys($ordering_answer);
        if (!isset($_SESSION['OrderingTemporarySave'][$questionId]) && !isset($_SESSION['OrderingSubsetKeys'][$questionId])) {

            if (isset($itemsSelectionType) && $itemsSelectionType > 1 && isset($sizeOfSubset) && $sizeOfSubset >= 2) {
                $minKey = min($randomKeys);
                $maxKey = max($randomKeys);
                $range = array_combine(range($minKey, $maxKey), range($minKey, $maxKey));
                if ($itemsSelectionType == 2) {
                    $randomKeys = array_rand($range, $sizeOfSubset);
                } elseif ($itemsSelectionType == 3) {
                    $maxStart = $maxKey - $sizeOfSubset + 1;
                    $start = rand($minKey, $maxStart);
                    $randomKeys = range($start, $start + $sizeOfSubset - 1);
                }
            }

            $fullRange = range(1, $countOrderingAnswers);
            if (isset($itemsSelectionType) && $itemsSelectionType == 1) {
                shuffle($fullRange); // Shuffle all items
            } elseif (isset($itemsSelectionType) && $itemsSelectionType == 2) {
                shuffle($randomKeys);
                $sizeRandomKeys = count($randomKeys);
                for ($i = 0; $i < $sizeRandomKeys; $i++) {
                    if ($i < $sizeRandomKeys - 1) {
                        $fullRangeKey = array_search($randomKeys[$i], $fullRange);
                        $fullRangeNextKey = array_search($randomKeys[$i+1], $fullRange);
                        $tmpValOfKey = $fullRange[$fullRangeKey];
                        $fullRange[$fullRangeKey] = $fullRange[$fullRangeNextKey];
                        $fullRange[$fullRangeNextKey] = $tmpValOfKey;
                    }
                }
            } elseif (isset($itemsSelectionType) && $itemsSelectionType == 3) {
                $positions = [];
                foreach ($fullRange as $index => $value) {
                    if (in_array($value, $randomKeys)) {
                        $positions[] = $index; // store index positions
                    }
                }
                // Shuffle the subset
                shuffle($randomKeys);
                foreach ($positions as $i => $pos) {
                    $fullRange[$pos] = $randomKeys[$i];
                }
            }

        } else { // FullRange will change after temporary save or prev-next navigation

            $fullRange = [];
            $arr = $_SESSION['OrderingTemporarySave'][$questionId];
            $randomKeys = $_SESSION['OrderingSubsetKeys'][$questionId];
            foreach ($arr as $v) {
                if (!empty($v)) {
                    $fullRange[] = array_search($v, $ordering_answer);
                }
            }

        }

        $jsonRandomKeys = json_encode($randomKeys);
        $html_content .= "  <div class='{$displayItems}' id='CardList_{$questionId}'>";
        foreach ($fullRange as $i) {
            $class = '';
            $icon = 'fa-arrows';
            $value = $ordering_answer[$i];
            $style = '';
            $bgColor = '';
            if (!in_array($i, $randomKeys)) {
                $class = 'bg-light';
                $icon = '';
            }
            $html_content .= "  <div class='draggable-item $class border-card p-3' data-value='{$value}' data-position='{$i}'>
                                    <div class='d-flex justify-content-between align-items-center gap-3'>
                                        <p class='text-nowrap'>$value</p>
                                        <span class='reorder-btn'>
                                            <span class='fa $icon' data-bs-toggle='tooltip' data-bs-placement='top' title='' style='cursor: grab;'></span>
                                        </span>
                                    </div>
                                </div>";
            
        }
        $html_content .= "  </div>";
        $html_content .= "<input type='hidden' id='orderingResponses_{$questionId}' name='choice[$questionId]'>";
        $html_content .= "<input type='hidden' name='subsetKeys[$questionId]' value='{$jsonRandomKeys}'>";
        
        return $html_content;
    }

    public function QuestionResult($choice, $eurid, $regrade, $extra_type = ''): string
    {

        global $langSelect, $langCorrectS, $langIncorrectS, $questionScore, $langYourOwnAnswerIs, $langCorrectOrdering;

        $html_content = '';

        $questionId = $this->answer_object->getQuestionId();
        $nbrAnswers = $this->answer_object->selectNbrAnswers();
        $ordering_answers = $this->answer_object->get_ordering_answers();
        $answersByUser = $this->answer_object->get_ordering_answers_by_user($questionId, $eurid);

        $arr = [];
        $arrGrade = [];
        $loop = 1;
        foreach ($answersByUser as $an) {
            $arr[$loop] = $an->answer;
            $arrGrade[$loop] = $an->weight;
            $loop++;
        }

        if (count($ordering_answers) == count($arr) && count($arr) == count($arrGrade)) {
            for ($i = 1; $i <= count($ordering_answers); $i++) {
                if ($ordering_answers[$i] != $arr[$i]) {
                    $arr[$i] = "<span class='text-danger TextBold'><s>" . $arr[$i] . "</s></span>";
                    if ($arrGrade[$i] < 0) {
                        $questionScore -= $arrGrade[$i];
                        $grade = $arrGrade[$i];
                    }
                } else {
                    $arr[$i] = "<span class='text-success TextBold'>" . $arr[$i] . "</span>";
                    $questionScore += $arrGrade[$i];
                    $grade = $arrGrade[$i];
                }
                if ($regrade) {
                    Database::get()->query('UPDATE exercise_answer_record
                            SET weight = ?f
                            WHERE eurid = ?d AND question_id = ?d AND answer_id = ?d',
                            $grade, $eurid, $questionId, $arrAnId[$i]);
                }
            }
        }

        $correct = implode(' -> ', $ordering_answers);
        $str = implode(' -> ', $arr);

        $html_content .= "<tr><td><strong>$langCorrectOrdering</strong> $correct</td></tr>";
        $html_content .= "<tr><td><strong>$langYourOwnAnswerIs</strong> $str</td></tr>";

        return $html_content;

    }

}
