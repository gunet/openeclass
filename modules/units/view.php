<?php
/**
 * Created by PhpStorm.
 * User: jexi
 * Date: 20/5/2019
 * Time: 2:38 μμ
 */

switch ($_REQUEST['res_type']) {
    case 'assignment':
        require_once '../work/index.php';
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
    default:
        break;
}