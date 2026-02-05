<?php

require_once 'PointsGame.php';

function course_points_game_widget($uid, $course_id) {
    global $is_editor, $is_course_admin, $urlAppend, $course_code;

    if (!$uid || $is_editor || $is_course_admin || $_SESSION['status'] != USER_STUDENT) {
        return '';
    }

    $games = Database::get()->queryArray("
        SELECT id, title 
        FROM points_game 
        WHERE course_id = ?d AND active = 1
        ORDER BY title ASC
    ", $course_id);

    if (!$games || count($games) == 0) {
        return '';
    }

    $games_data = [];
    foreach ($games as $game) {
        $info = PointsGame::getNextLevelInfo($uid, $game->id);
        
        $points_record = Database::get()->querySingle("
            SELECT total_points 
            FROM user_points_game_points 
            WHERE user = ?d AND points_game = ?d
        ", $uid, $game->id);

        $total_points = $points_record ? $points_record->total_points : 0;
        $percent = $info['progress_percentage'] ?? 0;
        
        $level_number = 2;
        if ($info['current_level_id']) {
            $level_info = Database::get()->querySingle("
                SELECT friendly_name 
                FROM points_game_levels 
                WHERE id = ?d
            ", $info['current_level_id']);
            
            if ($level_info && preg_match('/(\d+)/', $level_info->friendly_name, $matches)) {
                $level_number = $matches[1];
            }
        }

        $games_data[] = [
            'title' => $game->title,
            'points' => $total_points,
            'level' => $level_number,
            'percent' => $percent
        ];
    }

    $html = "
    <div class='card panelCard card-transparent border-0 mt-5 sticky-column-course-home'>
        <div class='card-header card-header-default px-0 py-0 border-0 d-flex justify-content-between align-items-center'>
            <h3 class='mb-0'>Πόντοι Παιχνιδιών</h3>
            <a class='TextRegular text-decoration-underline vsmall-text' href='{$urlAppend}modules/progress/index.php?course={$course_code}'>Όλες...</a>
        </div>
        <div class='card-body card-body-default px-0 py-0 mt-3'>
            <div class='points-game-carousel'>
                <div class='carousel-nav-header'>";
    
    if (count($games_data) > 1) {
        $html .= "<button class='nav-btn prev-btn'><i class='fa-solid fa-chevron-left'></i></button>";
    }
    
    $html .= "<span class='game-name'></span>";
    
    if (count($games_data) > 1) {
        $html .= "<button class='nav-btn next-btn'><i class='fa-solid fa-chevron-right'></i></button>";
    }
    
    $html .= "</div><div class='carousel-slides'>";

    foreach ($games_data as $idx => $game) {
        $active = $idx === 0 ? 'active' : '';
        $html .= "
            <div class='slide-item {$active}' data-idx='{$idx}' data-name='" . htmlspecialchars($game['title']) . "'>
                <div class='game-box'>
                    <div class='badge-col'>
                        <div class='badge-circle'>
                            <i class='fa-solid fa-star'></i>
                        </div>
                    </div>
                    <div class='points-col'>
                        <div class='points-num'>{$game['points']}</div>
                        <div class='points-txt'>πόντοι</div>
                    </div>
                    <div class='progress-col'>
                        <div class='progress-label'>ολοκλήρωση</div>
                        <div class='progress-percent'>{$game['percent']}%</div>
                        <div class='progress-level'>του level {$game['level']}</div>
                    </div>
                </div>
            </div>";
    }

    $html .= "</div></div></div></div>";
    return $html;
}