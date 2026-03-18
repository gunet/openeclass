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
            'title'      => $game->title,
            'points'     => $total_points,
            'level'      => $current_level,
            'next_level' => $next_level,
            'percent'    => $percent
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
        $next_str = !empty($game['next_level']) ? "{$game['percent']}% {$game['next_level']}" : "{$game['percent']}% ολοκλήρωση";

        $html .= "
            <div class='slide-item {$active_class}' data-name='" . htmlspecialchars($game['title']) . "'>
                <div class='game-box'>
                    <div class='game-top-row'>
                        <div class='level-star-wrap'>
                            <i class='fa-solid fa-star level-star-bg'></i>
                            <span class='level-star-text'>" . htmlspecialchars($game['level']) . "</span>
                        </div>
                        <div class='gi-group'>
                            <span class='gi-value'>" . number_format($game['points']) . "</span>
                            <span class='gi-label'>$langPoints</span>
                        </div>
                    </div>
                    <div class='game-prog-track'>
                        <div class='game-prog-fill' style='width:{$game['percent']}%'></div>
                    </div>
                    <div class='game-next-str'>{$next_str}</div>
                </div>
            </div>";
    }

    $html .= "</div></div></div></div>";
    
    
    // Add JavaScript
    $html .= "
    <style>
    .points-game-carousel {
        border: 1px solid #e0e0e0;
        border-radius: 6px;
        overflow: hidden;
        background: white;
        width: 100%;
        box-sizing: border-box;
    }
    
    .carousel-nav-header {
        background: #4a5568;
        padding: 12px 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 25px;
    }
    
    .carousel-nav-header .nav-btn {
        background: none;
        border: none;
        color: white;
        font-size: 16px;
        cursor: pointer;
        padding: 0;
    }
    
    .carousel-nav-header .game-name {
        color: white;
        font-size: 15px;
        font-weight: 500;
    }
    
    .carousel-slides {
        padding: 15px;
        display: block;
        width: 100%;
        box-sizing: border-box;
    }
    
    .carousel-slides .slide-item {
        display: none !important;
    }
    
    .carousel-slides .slide-item.active {
        display: block !important;
        animation: fadeEffect 0.4s;
        width: 100%;
    }
    
    @keyframes fadeEffect {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    
    .game-box {
        display: flex;
        flex-direction: column;
        gap: 8px;
        padding: 15px;
        border: 1px solid #e0e0e0;
        border-radius: 6px;
        background: #fafafa;
        max-width: 100%;
        margin: 0 auto;
        box-sizing: border-box;
    }

    .game-top-row {
        display: flex;
        align-items: center;
        gap: 22px;
        flex-wrap: wrap;
    }

    .badge-col {
        flex: 0 0 auto;
    }

    .level-star-wrap {
        position: relative;
        width: 52px;
        height: 52px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .level-star-bg {
        font-size: 50px;
        color: #FFD700;
        position: absolute;
        line-height: 1;
    }

    .level-star-text {
        position: relative;
        z-index: 1;
        font-size: 12px;
        font-weight: 700;
        color: #333;
        text-align: center;
        line-height: 1;
        max-width: 36px;
        word-break: break-word;
    }

    .gi-group {
        display: flex;
        align-items: baseline;
        gap: 5px;
    }

    .gi-label {
        font-size: 16px;
        color: #666;
        font-weight: 500;
    }

    .gi-value {
        font-size: 22px;
        font-weight: 700;
        color: #333;
    }

    .gi-sep {
        display: inline-block;
        width: 12px;
    }

    .game-next-str {
        font-size: 12px;
        color: #555;
        text-align: left;
        align-self: flex-start;
    }

    .game-prog-track {
        height: 6px;
        background: #e0e0e0;
        border-radius: 10px;
        overflow: hidden;
        width: 100%;
    }

    .game-prog-fill {
        height: 100%;
        background: #5b7ad6;
        border-radius: 10px;
        transition: width 0.3s ease;
    }
    </style>

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