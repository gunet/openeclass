<?php

require_once 'PointsGame.php';
require_once 'Game.php';

function course_points_game_widget($uid, $course_id) {
    global $is_editor, $is_course_admin, $urlAppend, $course_code, $langPointsGames, $langPoints, $langStart, $langToNextLevel;

    if (!$uid || $is_editor || $is_course_admin || $_SESSION['status'] != USER_STUDENT) {
        return '';
    }

    // CHECK FOR COMPLETENESS - Ελέγχει και ενημερώνει τους πόντους του χρήστη
    Game::checkCompleteness($uid, $course_id);

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
        
        // Use current_points from getNextLevelInfo (this is total_points)
        $total_points = $info['current_points'];
        $percent = isset($info['progress_percentage']) ? round($info['progress_percentage']) : 0;
        
        // If user has reached a level, use that
        if (isset($info['current_level_title']) && !empty($info['current_level_title'])) {
            $current_level = $info['current_level_title'];
        } else {
            $current_level = $langStart;
        }

        if (!is_null($info['next_level_id'])) {
            $next_level = $langToNextLevel;
        } else {
            $next_level = '';
        }

        $games_data[] = [
            'title' => $game->title,
            'points' => $total_points,
            'level' => $current_level,
            'next_level' => $next_level,
            'percent' => $percent
        ];
    }

    $html = "
    <div class='card panelCard card-transparent border-0 mt-5 sticky-column-course-home'>
        <div class='card-header card-header-default px-0 py-0 border-0 d-flex justify-content-between align-items-center'>
            <h3 class='mb-0'>$langPointsGames</h3>
            <a class='TextRegular text-decoration-underline vsmall-text' href='{$urlAppend}modules/progress/index.php?course={$course_code}&tab=points'>Όλες...</a>
        </div>
        <div class='card-body card-body-default px-0 py-0 mt-3'>
            <div class='points-game-carousel'>
                <div class='carousel-nav-header'>";
    
    if (count($games_data) > 1) {
        $html .= "<button type='button' class='nav-btn prev-btn'><i class='fa-solid fa-chevron-left'></i></button>";
    }
    
    $first_game_name = isset($games_data[0]) ? htmlspecialchars($games_data[0]['title']) : '';
    $html .= "<span class='game-name'>{$first_game_name}</span>";
    
    if (count($games_data) > 1) {
        $html .= "<button type='button' class='nav-btn next-btn'><i class='fa-solid fa-chevron-right'></i></button>";
    }
    
    $html .= "</div><div class='carousel-slides'>";

    foreach ($games_data as $idx => $game) {
        $active_class = ($idx === 0) ? 'active' : '';
        $html .= "
            <div class='slide-item {$active_class}' data-name='" . htmlspecialchars($game['title']) . "'>
                <div class='game-box'>
                    <div class='badge-col'>
                        <div class='badge-circle'>
                            <i class='fa-solid fa-star'></i>
                        </div>
                    </div>
                    <div class='points-col'>
                        <div class='points-num'>{$game['points']}</div>
                        <div class='points-txt'>$langPoints</div>
                    </div>
                    <div class='progress-col'>
                        <div class='progress-label'>ολοκλήρωση</div>
                        <div class='progress-percent'>{$game['percent']}%</div>
                        <div class='progress-level'>$next_level</div>
                    </div>
                </div>
            </div>";
    }

    $html .= "</div></div></div></div>";
    
    
    // Add JavaScript
    $html .= "
    <script>
    $(document).ready(function() {
        let currentSlide = 0;
        const slides = $('.points-game-carousel .slide-item');
        const totalSlides = slides.length;
        const gameName = $('.points-game-carousel .game-name');
        
        function showSlide(index) {
            slides.removeClass('active');
            $(slides[index]).addClass('active');
            gameName.text($(slides[index]).data('name'));
            currentSlide = index;
        }
        
        $('.points-game-carousel .prev-btn').click(function() {
            let newIndex = (currentSlide - 1 + totalSlides) % totalSlides;
            showSlide(newIndex);
        });
        
        $('.points-game-carousel .next-btn').click(function() {
            let newIndex = (currentSlide + 1) % totalSlides;
            showSlide(newIndex);
        });
    });
    </script>
    ";

    
    return $html;
}