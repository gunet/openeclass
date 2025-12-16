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
        global $langScore, $langAnswer, $langOrdering;

        $html_content = "<tr class='active'><td><strong>$langAnswer</strong></td></tr>";

        $predefinedOrderingAnswers = $this->answer_object->get_ordering_answers();
        $predefinedOrderingGrades = $this->answer_object->get_ordering_answer_grade();

        $Answers = implode('->', $predefinedOrderingAnswers);
        $AnswersGrade = implode(':', $predefinedOrderingGrades);
        $html_content .= "<tr>
                           <td>
                                <strong><small class='text-nowrap'>($langScore: $AnswersGrade)</small></strong>
                                <div><strong>$langOrdering </strong><span class='ps-2'>$Answers</span></div>";
        $html_content .= "</td>
                         </tr>";

        return $html_content;
    }

    public function AnswerQuestion($question_number, $exerciseResult = [], $options = []): string
    {
        global $head_content, $course_code, $langClearChoice, $langReorder, $urlServer, $uid;

        $html_content = "";

        $questionId = $this->answer_object->getQuestionId();
        $nbrAnswers = $this->answer_object->selectNbrAnswers();

        $head_content .= "
            <script src='{$urlServer}/js/sortable/Sortable.min.js'></script>
            <script type='text/javascript'>

                function initialPositions(QID) {

                    let dataObject = [];

                    // Loop through all items to get their current indexes
                    const items = document.querySelectorAll('#CardList_'+QID+' > .draggable-item');
                    
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
                    $('#orderingResponses_'+QID).val(jsonString);
                    const arrSubsetKeys = JSON.parse($('#subsetKeys_'+QID).val());
                    $('#orderingWithSubsetKeys_'+QID).val(jsonString+'::'+'['+arrSubsetKeys+']');

                    calculatePositions(QID);
                    updateButtonOpacity(QID);

                }
                

                function calculatePositions(QID) {
                    var box = document.getElementById('CardList_'+QID);
                    Sortable.create(box, {
                        animation: 350,
                        handle: '.fa-arrows',
                        onEnd: function(evt) {
                            initialPositions(QID);
                        }
                    });
                }
                
                function updateButtonOpacity(QID) {
                    const container = $('#CardList_'+QID);
                    const items = container.children('.draggable-item');

                    // Reset all button opacities and listeners first
                    container.find('.move-up').css('opacity', 1);
                    container.find('.move-down').css('opacity', 1);
                    container.find('.move-up').removeClass('pe-none');
                    container.find('.move-down').removeClass('pe-none');

                    // Set opacity for the first item's Up button
                    const firstItem = items.first();
                    firstItem.find('.move-up').css('opacity', 0.3);
                    firstItem.find('.move-up').addClass('pe-none');

                    // Set opacity for the last item's Down button
                    const lastItem = items.last();
                    lastItem.find('.move-down').css('opacity', 0.3);
                    lastItem.find('.move-down').addClass('pe-none');
                }

                $(document).ready(function() {
                    initialPositions($questionId);

                    $('#CardList_{$questionId}').on('click', '.move-up, .move-down', function() {
                        
                        const button = $(this);
                        const item = button.closest('#CardList_{$questionId} > .draggable-item');

                        if (button.hasClass('move-up')) {
                            const prev = item.prev('#CardList_{$questionId} > .draggable-item');
                            if (prev.length) {
                                item.insertBefore(prev);
                                initialPositions($questionId);
                            }
                        } else if (button.hasClass('move-down')) {
                            const next = item.next('#CardList_{$questionId} > .draggable-item');
                            if (next.length) {
                                next.insertBefore(item);
                                initialPositions($questionId);
                            }
                        }
                    });

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

        $arrow1 = '';
        $arrow2 = '';
        $displayItems = '';
        if (isset($layoutItems) && $layoutItems == 'Horizontal') {
            $displayItems = 'd-flex justify-content-start align-items-center gap-3 flex-wrap';
            $arrow1 = 'fa-solid fa-arrow-left';
            $arrow2 = 'fa-solid fa-arrow-right';
        } elseif (isset($layoutItems) && $layoutItems == 'Vertical') {
            $displayItems = 'd-flex flex-column gap-3';
            $arrow1 = 'fa-solid fa-up-long';
            $arrow2 = 'fa-solid fa-down-long';
        }

        $randomKeys = array_keys($ordering_answer);
        if (!isset($exerciseResult[$questionId])) {

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
            if (is_string($exerciseResult[$questionId])) { // comes from navigation (prev-next buttons)
                $tmpArr = explode('::', $exerciseResult[$questionId]);
                $arr = json_decode($tmpArr[0], true);
                $randomKeys = json_decode($tmpArr[1], true);
            } elseif (is_array($exerciseResult[$questionId])) { // comes from temporary
                $arr = $exerciseResult[$questionId];
                // RandomKeys will get its subset from option question
                $jsonQuestion = Database::get()->querySingle("SELECT options FROM exercise_question WHERE id = ?d", $questionId)->options;
                $arrJson = json_decode($jsonQuestion, true);
                $arrayRes = json_decode($arrJson['userSubset_' . $uid], true);
                $result = implode(',', $arrayRes);
                $randomKeys = explode(',', $result);
            }

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
            $sortingBtns = "<div class='sorting-controls d-flex gap-2'>
                                <button type='button' class='btn submitAdminBtn move-up' data-question-id='{$questionId}' onclick='updateListenerOrderingBtn({$question_number}, {$questionId})'><i class='$arrow1'></i></button>
                                <button type='button' class='btn submitAdminBtn move-down' data-question-id='{$questionId}' onclick='updateListenerOrderingBtn({$question_number}, {$questionId})'><i class='$arrow2'></i></button>
                            </div>";
            if (!in_array($i, $randomKeys)) {
                $class = 'light-transparent';
                $icon = '';
                $sortingBtns = '';
            }
            $html_content .= "  <div class='draggable-item $class border-card p-3' data-value='{$value}' data-position='{$i}'>
                                    <div class='d-flex justify-content-between align-items-center gap-3'>
                                        <p class='text-nowrap'>$value</p>
                                        <div class='d-flex align-items-center gap-4'>
                                            $sortingBtns
                                            <span class='reorder-btn'>
                                                <span class='fa $icon' data-bs-toggle='tooltip' data-bs-placement='top' title='' data-icon='{$questionId}' onmousedown='updateListenerOrderingIcon({$question_number}, {$questionId})' style='cursor: grab;'></span>
                                            </span>
                                        </div>
                                    </div>
                                </div>";

        }
        $html_content .= "  </div>";
        $html_content .= "<input type='hidden' id='orderingResponses_{$questionId}'>";
        $html_content .= "<input type='hidden' id='subsetKeys_{$questionId}' name='subsetKeys[$questionId]' value='{$jsonRandomKeys}'>";
        $html_content .= "<input type='hidden' id='orderingWithSubsetKeys_{$questionId}' name='choice[$questionId]'>";

        return $html_content;
    }

    public function QuestionResult($choice, $eurid, $regrade, $extra_type = ''): string
    {

        global $questionScore, $langYourOwnAnswerIs, $langCorrectOrdering;

        $html_content = '';

        $questionId = $this->answer_object->getQuestionId();
        $ordering_answers = $this->answer_object->get_ordering_answers();
        $answersByUser = $this->answer_object->get_ordering_answers_by_user($questionId, $eurid);

        $arr = $arrAnId = $arrGrade = [];
        $loop = 1;
        foreach ($answersByUser as $an) {
            $arr[$loop] = $an->answer;
            $arrAnId[$loop] = $an->answer_id;
            $arrGrade[$loop] = $an->weight;
            $loop++;
        }

        if (count($ordering_answers) == count($arr) && count($arr) == count($arrGrade)) {
            for ($i = 1; $i <= count($ordering_answers); $i++) {
                if ($ordering_answers[$i] != $arr[$i]) {
                    $arr[$i] = "<span class='text-danger TextBold'><s>" . $arr[$i] . "</s></span>";
                    if ($arrGrade[$i] < 0) { // Negative grade
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

        $html_content .= "<tr><td><strong>$langCorrectOrdering: </strong> $correct</td></tr>";
        $html_content .= "<tr><td><strong>$langYourOwnAnswerIs: </strong> $str</td></tr>";

        return $html_content;

    }

}
