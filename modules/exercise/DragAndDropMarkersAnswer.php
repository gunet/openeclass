<?php

require_once 'answer.class.php';

class DragAndDropMarkersAnswer extends \QuestionType
{

    public function __destruct() {
        unset($this->answer_object);
    }

    public function PreviewQuestion(): string
    {
        // to be implemented
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
                if (count($value) == 9) { // circle or rectangle
                    $arrDataMarkers[$value[0]['marker_id']] = [
                        'marker_answer' => $value[1]['marker_answer'],
                        'marker_shape' => $value[2]['shape_type'],
                        'marker_coordinates' => $value[3]['x'] . ',' . $value[4]['y'],
                        'marker_offsets' => $value[5]['endX'] . ',' . $value[6]['endY'],
                        'marker_grade' => $value[7]['marker_grade'],
                        'marker_radius' => $value[8]['marker_radius']
                    ];
                } elseif (count($value) == 5) { // polygon
                    $arrDataMarkers[$value[0]['marker_id']] = [
                                                                'marker_answer' => $value[1]['marker_answer'],
                                                                'marker_shape' => $value[2]['shape_type'],
                                                                'marker_coordinates' => $value[3]['points'],
                                                                'marker_grade' => $value[4]['marker_grade']
                                                               ];
                }
            }
        }
        foreach ($arrDataMarkers as $index => $m) {
            $arr_m = explode(',', $m['marker_coordinates']);
            $m['x'] = $arr_m[0];
            $m['y'] = $arr_m[1];
            if ($m['marker_shape'] == 'circle' or $m['marker_shape'] == 'rectangle') {
                $arr_of = explode(',', $m['marker_offsets']);
                $m['endX'] = $arr_of[0];
                $m['endY'] = $arr_of[1];
            }
            if ($m['marker_shape'] == 'circle') {
                $coordinatesXY[] = ['marker_id' => $index, 'x' => $m['x'], 'y' => $m['y'], 'shape_type' => $m['marker_shape'], 'radius' => $m['marker_radius']];
            } elseif ($m['marker_shape'] == 'rectangle') {
                $coordinatesXY[] = ['marker_id' => $index, 'x' => $m['x'], 'y' => $m['y'], 'shape_type' => $m['marker_shape'], 'endY' => $m['endY'], 'endX' => $m['endX']];
            } elseif ($m['marker_shape'] == 'polygon') {
                $coordinatesXY[] = ['marker_id' => $index, 'points' => $m['marker_coordinates'], 'shape_type' => $m['marker_shape'], 'color' => 'rgba(255, 255, 255, 0.5)'];
            }
        }
        $DataMarkersToJson = json_encode($coordinatesXY) ?? '';

        $html_content = "<div class='col-12 mb-4'><small class='Accent-200-cl'>(*)$langCalcelDroppableItem</small></div>";
        $html_content .= "<div class='col-12 d-flex justify-content-start align-items-center drag-and-drop-markers-container-words gap-4 flex-wrap mt-4' id='words_{$questionId}'>";
        foreach ($list_answers as $an) {
            $html_content .= "<div class='draggable' data-word='{$an}' data-pool-id='words_{$questionId}'>$an</div>";
        }
        $html_content .= "</div>";
        $html_content .= "<input type='hidden' name='choice[$questionId]' id='arrInput_{$questionId}'>";
        $html_content .= "<input type='hidden' id='insertedMarkersAsJson-$questionId' value='{$DataMarkersToJson}'>";
        $html_content .= "<input type='hidden' class='currentQuestion' value='{$questionId}'>";

        load_js('drag-and-drop-shapes');
        $head_content .= "<script>
                            document.addEventListener('DOMContentLoaded', function() {
                                createMarkersBlanksOnImage();
                                drag_and_drop_process();
                            });
                          </script>";

        //createMarkersBlanksOnImage($questionId);
        //drag_and_drop_process();

        return $html_content;
    }
}
