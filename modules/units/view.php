<?php
/**
 * Created by PhpStorm.
 * User: jexi
 * Date: 20/5/2019
 * Time: 2:38 μμ
 */

$res_type = isset($_REQUEST['res_type'])? $_REQUEST['res_type']: null;
$unit = isset($_REQUEST['unit'])? intval($_REQUEST['unit']): null;

switch ($res_type) {
    case 'assignment':
        require_once '../work/index.php';
        exit;
    case 'assignment_grading':
        require_once '../work/grade_edit_review.php';
        exit;
    case 'exercise':
        require_once '../exercise/exercise_submit.php';
        exit;
    case 'exercise_results':
        require_once '../exercise/exercise_result.php';
        exit;
    case 'videolink':
        require_once "../video/playlink.php";
        exit;
    case 'video':
        require_once "../../include/lib/modalboxhelper.class.php";
        ModalBoxHelper::loadModalBox(true);
        require_once "../video/play.php";
        exit;
    case 'chat':
        require_once "../chat/chat.php";
        exit;
    case 'chat_actions':
        require_once "../chat/messageList.php";
        exit;
    case 'questionnaire':
        require_once "../questionnaire/pollparticipate.php";
        exit;
    case 'questionnaire_results':
        require_once "../questionnaire/pollresults.php";
        exit;
    case 'forum':
        require_once "../forum/viewforum.php";
        exit;
    case 'forum_topic':
        require_once "../forum/viewtopic.php";
        exit;
    case 'forum_new_topic':
        require_once "../forum/newtopic.php";
        exit;
    case 'forum_topic_reply':
        require_once "../forum/reply.php";
        exit;
    case 'h5p':
        require_once "../h5p/view.php";
        exit;
    case 'h5p_show':
        require_once "../h5p/show.php";
        exit;
    case 'lp':
        require_once "../learnPath/viewer.php";
        exit;
    case 'lp_results':
        require_once "../learnPath/learningPath.php";
        exit;
    default:
        break;
}
