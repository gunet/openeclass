<?php

require_once 'answer.class.php';

class DragAndDropMarkersAnswer extends \QuestionType
{

    public function __destruct() {
        unset($this->answer_object);
    }

    public function PreviewQuestion(): string
    {
        global $langAnswer, $langScore, $head_content, $webDir, $course_code, $langPoint, $langThisAnswerIsNotCorrect;

        $questionId = $this->question_id;

        $html_content = '';

        if (!isset($_GET['eurId'])) { // Display them in preview question

            $html_content = "<tr class='active'><td><strong>$langAnswer</strong></td></tr>";

            $markersWithAnswers = $this->answer_object->get_drag_and_drop_markers_with_answers();
            $markersWithGrades = $this->answer_object->get_drag_and_drop_markers_with_grades();
            $gradesOfAnswers = $this->answer_object->get_drag_and_drop_answer_grade();
            $AnswersGradeArr= [];
            foreach ($gradesOfAnswers as $gr) {
                $AnswersGradeArr[] = $gr;
            }
            $AnswersGrade = implode(':', $AnswersGradeArr);
        
            $html_content .= "<tr>
                            <td> 
                                    <strong><small class='text-nowrap'>($langScore: $AnswersGrade)</small></strong>";
                        foreach ($markersWithAnswers as $index => $val) {
                            $html_content .= "<div class='mt-2'>$langPoint [$index] = $val";
                            if (isset($markersWithGrades[$index]) && $markersWithGrades[$index] == 0) {
                                $html_content .= "&nbsp;&nbsp;<span class='Accent-200-cl'>($langThisAnswerIsNotCorrect)</span>";
                            }
                            $html_content .= "</div>";
                        }
            $html_content .= "</td>
                            </tr>";
        }

        // Create the blanks on the image and display them if the question type is DRAG AND DROP MARKERS.
        $dropZonesDir = "$webDir/courses/$course_code/image";
        $dropZonesFile = "$dropZonesDir/dropZones_$questionId.json";
        $arrDataMarkers = [];
        if (file_exists($dropZonesFile)) {
            $dataJsonFile = file_get_contents($dropZonesFile);
            $markersData = json_decode($dataJsonFile, true);
            // Loop through each item in the original array
            foreach ($markersData as $item => $value) {
                if (count($value) == 10) {
                    $arrDataMarkers[$value[0]['marker_id']] = [
                        'marker_answer' => $value[1]['marker_answer'],
                        'marker_shape' => $value[2]['shape_type'],
                        'marker_coordinates' => $value[3]['x'] . ',' . $value[4]['y'],
                        'marker_offsets' => $value[5]['endX'] . ',' . $value[6]['endY'],
                        'marker_grade' => $value[7]['marker_grade'],
                        'marker_radius' => $value[8]['marker_radius'],
                        'marker_answer_with_image' => $value[9]['marker_answer_with_image']
                    ];
                } elseif (count($value) == 6) { // polygon
                    $arrDataMarkers[$value[0]['marker_id']] = [
                                                                'marker_answer' => $value[1]['marker_answer'],
                                                                'marker_shape' => $value[2]['shape_type'],
                                                                'marker_coordinates' => $value[3]['points'],
                                                                'marker_grade' => $value[4]['marker_grade'],
                                                                'marker_answer_with_image' => $value[5]['marker_answer_with_image']
                                                            ];
                } elseif (count($value) == 5) { // without shape . So the defined answer is not correct
                    $arrDataMarkers[$value[0]['marker_id']] = [
                                                                'marker_answer' => $value[1]['marker_answer'],
                                                                'marker_shape' => null,
                                                                'marker_coordinates' => null,
                                                                'marker_grade' => 0,
                                                                'marker_answer_with_image' => $value[4]['marker_answer_with_image']
                                                            ];

                }
            }
        }

        $coordinatesXY = [];
        foreach ($arrDataMarkers as $index => $m) {
            $arr_m = explode(',', $m['marker_coordinates'] ?? '');
            if (count($arr_m) == 2) {
                $m['x'] = $arr_m[0];
                $m['y'] = $arr_m[1];
            }
            if ($m['marker_shape'] == 'circle' or $m['marker_shape'] == 'rectangle') {
                $arr_of = explode(',', $m['marker_offsets']);
                $m['endX'] = $arr_of[0];
                $m['endY'] = $arr_of[1];
            }
            if ($m['marker_shape'] == 'circle' && count($arr_m) == 2) {
                $coordinatesXY[] = ['marker_id' => $index, 'x' => $m['x'], 'y' => $m['y'], 'shape_type' => $m['marker_shape'], 'radius' => $m['marker_radius'], 'marker_answer_with_image' => $m['marker_answer_with_image']];
            } elseif ($m['marker_shape'] == 'rectangle' && count($arr_m) == 2) {
                $coordinatesXY[] = ['marker_id' => $index, 'x' => $m['x'], 'y' => $m['y'], 'shape_type' => $m['marker_shape'], 'endY' => $m['endY'], 'endX' => $m['endX'], 'marker_answer_with_image' => $m['marker_answer_with_image']];
            } elseif ($m['marker_shape'] == 'polygon') {
                $coordinatesXY[] = ['marker_id' => $index, 'points' => $m['marker_coordinates'], 'shape_type' => $m['marker_shape'], 'color' => 'rgba(255, 255, 255, 0.5)', 'marker_answer_with_image' => $m['marker_answer_with_image']];
            }
        }

        $DataMarkersToJson = json_encode($coordinatesXY) ?? '';
        $preview = $_GET['preview'] ?? 0;
        if (isset($_GET['eurId'])) { // Display the blanks of an image inside the user's results.
            $preview = 1;
        }

        // Show the blanks on the image
        if ($DataMarkersToJson) {
            $html_content .= "<input type='hidden' class='currentQuestion' value='{$questionId}'>
                                <input type='hidden' id='insertedMarkersAsJson-$questionId' value='{$DataMarkersToJson}'>";

            load_js('drag-and-drop-shapes');

            $head_content .= "<script>
                                document.addEventListener('DOMContentLoaded', function() {
                                    createMarkersBlanksOnImage($preview);
                                });
                                </script>";
        }

        return $html_content;
        
    }

    public function AnswerQuestion($question_number, $exerciseResult = [], $options = []): string
    {
        global $webDir, $course_code, $langCalcelDroppableItem, $head_content;

        $questionId = $this->question_id;
        $question_text = $this->answer_object->get_drag_and_drop_text();
        $list_answers = $this->answer_object->get_drag_and_drop_answer_text();

        $dropZonesDir = "$webDir/courses/$course_code/image";
        $dropZonesFile = "$dropZonesDir/dropZones_$questionId.json";
        $arrDataMarkers = [];
        if (file_exists($dropZonesFile)) {
            $dataJsonFile = file_get_contents($dropZonesFile);
            $markersData = json_decode($dataJsonFile, true);
            // Loop through each item in the original array
            foreach ($markersData as $item => $value) {
                if (count($value) == 10) { // circle or rectangle
                    $arrDataMarkers[$value[0]['marker_id']] = [
                        'marker_answer' => $value[1]['marker_answer'],
                        'marker_shape' => $value[2]['shape_type'],
                        'marker_coordinates' => $value[3]['x'] . ',' . $value[4]['y'],
                        'marker_offsets' => $value[5]['endX'] . ',' . $value[6]['endY'],
                        'marker_grade' => $value[7]['marker_grade'],
                        'marker_radius' => $value[8]['marker_radius'],
                        'marker_answer_with_image' => $value[9]['marker_answer_with_image']
                    ];
                } elseif (count($value) == 6) { // polygon
                    $arrDataMarkers[$value[0]['marker_id']] = [
                                                                'marker_answer' => $value[1]['marker_answer'],
                                                                'marker_shape' => $value[2]['shape_type'],
                                                                'marker_coordinates' => $value[3]['points'],
                                                                'marker_grade' => $value[4]['marker_grade'],
                                                                'marker_answer_with_image' => $value[5]['marker_answer_with_image']
                                                               ];
                } elseif (count($value) == 5) { // without shape . So the defined answer is not correct
                    $arrDataMarkers[$value[0]['marker_id']] = [
                                                                'marker_answer' => $value[1]['marker_answer'],
                                                                'marker_shape' => null,
                                                                'marker_coordinates' => null,
                                                                'marker_grade' => 0,
                                                                'marker_answer_with_image' => $value[4]['marker_answer_with_image']
                                                            ];

                }
            }
        }
        foreach ($arrDataMarkers as $index => $m) {
            $arr_m = explode(',', $m['marker_coordinates'] ?? '');
            if (count($arr_m) == 2) {
                $m['x'] = $arr_m[0];
                $m['y'] = $arr_m[1];
            }
            if ($m['marker_shape'] == 'circle' or $m['marker_shape'] == 'rectangle') {
                $arr_of = explode(',', $m['marker_offsets']);
                $m['endX'] = $arr_of[0];
                $m['endY'] = $arr_of[1];
            }
            if ($m['marker_shape'] == 'circle' && count($arr_m) == 2) {
                $coordinatesXY[] = ['marker_id' => $index, 'x' => $m['x'], 'y' => $m['y'], 'shape_type' => $m['marker_shape'], 'radius' => $m['marker_radius'], 'marker_answer_with_image' => $m['marker_answer_with_image']];
            } elseif ($m['marker_shape'] == 'rectangle' && count($arr_m) == 2) {
                $coordinatesXY[] = ['marker_id' => $index, 'x' => $m['x'], 'y' => $m['y'], 'shape_type' => $m['marker_shape'], 'endY' => $m['endY'], 'endX' => $m['endX'], 'marker_answer_with_image' => $m['marker_answer_with_image']];
            } elseif ($m['marker_shape'] == 'polygon') {
                $coordinatesXY[] = ['marker_id' => $index, 'points' => $m['marker_coordinates'], 'shape_type' => $m['marker_shape'], 'color' => 'rgba(255, 255, 255, 0.5)', 'marker_answer_with_image' => $m['marker_answer_with_image']];
            }
        }
        $DataMarkersToJson = json_encode($coordinatesXY) ?? '';
        $DataJsonFileVal = json_encode($markersData) ?? '';

        $html_content = "<div class='col-12 mb-4'><small class='Accent-200-cl'>(*)$langCalcelDroppableItem</small></div>";
        $html_content .= "<div class='col-12 d-flex justify-content-start align-items-center drag-and-drop-markers-container-words gap-4 flex-wrap mt-4' id='words_{$questionId}'>";
        foreach ($list_answers as $an) {
            foreach ($arrDataMarkers as $index => $value) {
                if (array_key_exists('marker_answer', $value) && array_key_exists('marker_answer_with_image', $value) && $value['marker_answer'] == $an) {
                   if ($value['marker_answer_with_image'] == 1) { // predefined answer will be shown as image
                        $mID = $index;
                        $html_content .= "<div class='draggable draggable-image' data-image-id='{$mID}' data-word='{$an}' data-pool-id='words_{$questionId}'>
                                            <img src='../../courses/$course_code/image/answer-$questionId-$mID' alt='{$an}'>
                                          </div>";
                    } else { // predefined answer will be shown as text
                        $html_content .= "<div class='draggable' data-word='{$an}' data-pool-id='words_{$questionId}'>$an</div>";
                    } 
                }
            }
        }
        $html_content .= "</div>";
        if (isset($_SESSION['userHasAnswered'][$questionId])) {
            $uHasAnswered = json_encode($_SESSION['userHasAnswered'][$questionId], JSON_PRETTY_PRINT);
            $html_content .= "<input type='hidden' id='userHasAnswered-$questionId' value='{$uHasAnswered}'>                      
                              <input type='hidden' class='CourseCodeNow' value='{$course_code}'>";
        }
        $html_content .= "<input type='hidden' name='choice[$questionId]' id='arrInput_{$questionId}'>";
        $html_content .= "<input type='hidden' id='insertedMarkersAsJson-$questionId' value='{$DataMarkersToJson}'>";
        $html_content .= "<input type='hidden' class='currentQuestion' value='{$questionId}'>";
        $html_content .= "<input type='hidden' id='DataJsonFile-$questionId' value='{$DataJsonFileVal}'>";
        $html_content .= "<input type='hidden' id='typeQuestion-$questionId' value='".DRAG_AND_DROP_MARKERS."'>";

        load_js('drag-and-drop-shapes');
        $head_content .= "<script>
                            document.addEventListener('DOMContentLoaded', function() {
                                createMarkersBlanksOnImage();
                                drag_and_drop_process();
                            });
                          </script>";

        if (isset($_SESSION['userHasAnswered'])) {
            $head_content .= "<script>
                            document.addEventListener('DOMContentLoaded', function() {
                                save_user_answers($questionId);
                            });
                          </script>";
        }

        return $html_content;
    }

    public function QuestionResult($choice, $eurid, $regrade, $extra_type = ''): string
    {
        global $questionScore;

        $html_content = '';

        $nbrAnswers = $this->answer_object->selectNbrAnswers();
        $answer_object_ids = range(1, $nbrAnswers);

        foreach ($answer_object_ids as $answerId) {
            $answer = $this->answer_object->get_drag_and_drop_text();
            $answerWeighting = $this->answer_object->get_drag_and_drop_answer_grade();
            // Change indexes to start from 0.
            $arrTmp = [];
            foreach ($answerWeighting as $index => $value) {
                if ($index > 0) {
                    $index = $index - 1;
                    $arrTmp[$index] = $value;
                }
            }
            $answerWeighting = $arrTmp;
            $arrResult = drag_and_drop_user_results_as_text($eurid, $this->question_id);
            $answer = $arrResult[0]['aboutUserAnswers'];
            $questionScore = $arrResult[0]['aboutUserGrade'];

            $html_content .= "<tr><td>$answer</td></tr>";
        }
        return $html_content;
    }
}
