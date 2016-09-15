<?php

require_once 'include/QueryPath/QueryPath.php';
require_once 'include/init.php';
require_once 'exercise.class.php';
require_once 'question.class.php';
require_once 'answer.class.php';
require_once 'exercise.lib.php';

/**
 * Import a QTI file. */
function qti_import_file_form_submit($file, $course_id) {

    // Extract info from the file
    $qti_items = qti_extract_info($file);
    $msgs = array();

    // Loop through each question and import it
    foreach ($qti_items as $item) {

        $item = (object)$item;

        if(qti_create_node($item, $course_id)) {
            array_push($msgs, array(true, standard_text_escape($item->title)));
        } else {
            array_push($msgs, array(false, standard_text_escape($item->title)));
        }
    }
    return $msgs;
}

/**
 *
 * Convert item to node
 */
function qti_create_node($item) {

    $node = new stdClass();
    $node->title = $item->title;
    $node->content = $node->body = $item->content;
    $node->num_of_correct_answers = $node->body = $item->num_of_correct_answers;

    switch (strtolower($item->type)) {
        case 'explanation':
            $node->type = 'quiz_directions';
            break;
        case 'essay':
            $node->type = 'long_answer';
            $node->maximum_score = 1;
            break;
        default:
            $node->type = 'multichoice';
            $answers = $item->answers;

            // Add answers
            $node->answers = array();
            foreach ($answers as $answer) {
                $node->answers[] = array(
                    'answer' => $answer['text'],
                    'feedback' => $answer['feedback'],
                    'correct' => $answer['is_correct'],
                    'weight' => $answer['weight'],
                    'result_option' => '0', // No support for personality.
                    );
            }
            break;
    }

    //add question to db
    if($node->type == "multichoice") {
        add_question($node);
        return true;
    } else {
        return false;
    }
}

function add_question($node) {

    $objQuestion = new Question();
    $objQuestion->updateTitle(standard_text_escape($node->content));

    /**
    *Exercice type 1 refers to single response multiple choice question.
    *Exercice type 2 refers to multiple response multiple choice question.
    *QTILite allows only single response multiple choice questions.
    **/

    if($node->num_of_correct_answers > 1 ) {
        $objQuestion->updateType(2);
    } else {
        $objQuestion->updateType(1);
    }

    $objQuestion->save();

    $questionId = $objQuestion->selectId();

    $objAnswer = new Answer($questionId);
    $tmp_answer = array();

    if($node->answers) {
        foreach ($node->answers as $answer) {
            $objAnswer->createAnswer($answer['answer'], $answer['correct'], $answer['feedback'], $answer['weight'], 1);
        }
        $objAnswer->save();
    }
}

/**
 * Given a QTI XML file, extract questions.
 */
function qti_extract_info($file) {

    $items = array();
    foreach(qp($file, 'item') as $item) {

        //Get negative score
        $negative_score = $item->branch()->xpath('/questestinterop/item/resprocessing/respcondition/conditionvar/not/varequal/../../../setvar')->text();

        //Handle feedback
        $feedback_incorrect_linkrefid = $item->branch()->xpath('/questestinterop/item/resprocessing/respcondition/setvar[text()<=0]/../displayfeedback/@linkrefid')->text();

        $feedback_incorrect =  node_to_text($item->branch()->xpath('/questestinterop/item/itemfeedback[@ident="' . $feedback_incorrect_linkrefid . '"]/material'), true);

        $title = $item->attr('title');
        $type = $item->find('itemmetadata>qmd_itemtype')->text();

        $body = $item->end()->find('presentation>material');

        if ($body->attr('texttype') == 'text/html') {

            $bodytext = $body->text();

            if (strpos($bodytext, '<html') === FALSE) {
                $bodytext =  '<html>' . $bodytext . '</html>';
            }

            $doc = new DOMDocument();
            //supress query path warnings.
            @$doc->loadHTML($bodytext);
            $html = htmlqp($doc, 'body');
            //Handles emphasized text
            $contents = $html->get(0)->childNodes;
            // Extract HTML content
            $newdoc = qp();
            $i = 0;
            while ($node = $contents->item($i++)) {
                $newdoc->append($contents);
            }
            $out = strip_tags($newdoc->html()); // This leaves off XML declaration.
        }
        else {
            //$out = strip_tags($body->text());
            $out = node_to_text($body, false);
        }

        $new_item = array(
            'title' => $title,
            'type' => $type,
            'content' => $out,
            'answers' => array()
            );

        $answers = array();
        // Get all answers and loop through them.
        $answerstexts = $item->parent('item')->find('response_lid>render_choice>response_label>material>mattext');
        $num_of_correct_answers = 0;
        $answers = array();

        foreach ($answerstexts as $answertext) {

            $text = $answertext->text();
            $index = $answertext->parent('response_label')->attr('ident');
            $filter_weight = 'resprocessing>respcondition>conditionvar>varequal:contains(' . $index . ')';
            $weight = $answertext->parent('item')->find($filter_weight)->parent('respcondition')->find('setvar')->text();
            $index_feedback = $answertext->end()->parent('item')->find($filter_weight)->parent('respcondition')->find('displayfeedback')->attr('linkrefid');
            $filter_feedback = '//itemfeedback[@ident="' . $index_feedback . '"]';
            $feedback = "";
            $feedback = $answertext->end()->parent('item')->xpath($filter_feedback)->text();

            if($weight == '') {
                $feedback = $feedback_incorrect;
                $weight = $negative_score;
            }

            $is_correct = false;

            if($weight > 0) {
                $is_correct = true;
                $num_of_correct_answers++;
            }

            $answers[] = array(
                'text' => $text,
                'index' => $index,
                'is_correct' => $weight>0,
                'feedback' => $feedback,
                'weight' => $weight
                );

            //Store answers
            $new_item['answers'] = $answers;
        }
        $new_item['num_of_correct_answers'] = $num_of_correct_answers;

        // Store questions
        $items[] = $new_item;
    }
    return $items;
}


function node_to_text($body, $add_html_tags) {

    $text = "";

    $contents = $body->children();

    foreach ($contents as $node) {

        if($add_html_tags) {
            if($node->tag() == 'mattext') {
                $text .= $node->text();
            } else {
                $text .= '<strong>';
                $text .= $node->text();
                $text .= '</strong>';
            }
        } else {
            $text .= $node->text();
        }
    }
    return $text;
}

function exportIMSQTI($result) {

    $xml_qti = '<?xml version = "1.0" encoding = "UTF-8" standalone = "no"?>
    <!DOCTYPE questestinterop SYSTEM "fims_qtilitev1p2p1.dtd">
    <questestinterop></questestinterop>';

    $xml_qti = qp($xml_qti);

    $answers_xml = array();

    $xml = "";
    foreach ($result as $row) {

        $supported_question_types = array("1", "5");
        if (!in_array($row->type, $supported_question_types)) {
            continue;
        }

        $xml .= '
        <item title="' . $row->id . '" ident="question' . $row->id . '">
            <presentation>
                <material>
                    <mattext>'. $row->question .'</mattext>
                </material>
            </presentation>
            <response_lid rcardinality = "Single" rtiming = "No">
            <render_choice></render_choice>
            </response_lid>
        </item>
        ';
    }

    $xml_qti->branch()->find('questestinterop')->append($xml);

    foreach ($result as $row) {

        $objAnswerTmp = new Answer($row->id);

        $responses = "";
        $respconditions = "";
        $itemfeedbacks = "";
        for( $i=1 ; $i <= $objAnswerTmp->selectNbrAnswers() ; $i++ ) {
            $responses .= '
            <response_label ident="question' . $row->id . 'answer'. $i .'">
            <material>
                <mattext>'. $objAnswerTmp->answer[$i] .'</mattext>
            </material>
            </response_label>';

            $respconditions .=  '
            <respcondition>
                <conditionvar>
                    <varequal>question' . $row->id . 'answer'. $i .'</varequal>
                </conditionvar>
                <setvar action = "Set">'.$objAnswerTmp->weighting[$i].'</setvar>
                <displayfeedback feedbacktype = "Response" linkrefid = "question' . $row->id . 'feedback'. $i .'"/>
                </respcondition>';

                $itemfeedbacks .= '
                <itemfeedback ident = "question' . $row->id . 'feedback'. $i .'" view = "Candidate">
                    <material>
                        <mattext>'. $objAnswerTmp->comment[$i] .'</mattext>
                    </material>
                </itemfeedback>
                ';
            }

            $xml_qti->branch()->find('item[ident="question'. $row->id .'"]>response_lid>render_choice')->append($responses);
            $xml_qti->branch()->find('item[ident="question'. $row->id .'"]')->append('&#x9;<resprocessing>' . $respconditions . '</resprocessing>');
            $xml_qti->branch()->find('item[ident="question'. $row->id .'"]')->append($itemfeedbacks);
        }

        return $xml_qti->writeXML();
    }



