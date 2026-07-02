<?php

require_once 'PointsGame.php';
require_once 'Game.php';

function course_points_game_widget($uid, $course_id) {
    global $is_editor, $is_course_admin, $urlAppend, $course_code, $langPointsGames, $langPoints, $langStart, $langForNextLevel, $langReadMore, $langCompletion, $urlServer;

    if (!$uid || $is_editor || $is_course_admin || $_SESSION['status'] != USER_STUDENT) {
        return '';
    }

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

        $total_points = $info['current_points'];
        $percent = isset($info['progress_percentage']) ? round($info['progress_percentage']) : 0;

        if (isset($info['current_level_title']) && !empty($info['current_level_title'])) {
            $current_level = $info['current_level_title'];
            $level_num = isset($info['current_level_num']) ? $info['current_level_num'] : '';
        } else {
            $current_level = $langStart;
            $level_num = '';
        }

        if (!is_null($info['next_level_id'])) {
            $next_level = $info['next_level_title'];
        } else {
            $next_level = '';
        }

        // Resolve the icon: use current level's icon, or fall back to the first level's icon
        $icon_src = null;
        $icon_level_id = $info['current_level_id'] ?? null;
        if (!$icon_level_id) {
            // not yet on any level — use the first level's icon
            $first_lvl = Database::get()->querySingle(
                "SELECT id FROM points_game_levels WHERE points_game = ?d ORDER BY required_points ASC LIMIT 1", $game->id);
            if ($first_lvl) {
                $icon_level_id = $first_lvl->id;
            }
        }
        if ($icon_level_id) {
            $lvl_row = Database::get()->querySingle(
                "SELECT bi.filename FROM points_game_levels pgl JOIN badge_icon bi ON bi.id = pgl.icon WHERE pgl.id = ?d", $icon_level_id);
            if ($lvl_row) {
                $icon_src = $urlServer . BADGE_TEMPLATE_PATH . $lvl_row->filename;
            }
        }

        $games_data[] = [
            'title'      => $game->title,
            'points'     => $total_points,
            'level'      => $current_level,
            'level_num'  => $level_num,
            'next_level' => $next_level,
            'percent'    => $percent,
            'icon_src'   => $icon_src
        ];
    }

    $html = "
    <div class='card panelCard card-transparent border-0 mt-5 sticky-column-course-home'>
        <div class='card-header card-header-default px-0 py-0 border-0 d-flex justify-content-between align-items-center'>
            <h2 class='text-heading-h3 mb-0'>$langPointsGames</h2>
            <a class='TextRegular text-decoration-underline vsmall-text' href='{$urlAppend}modules/progress/index.php?course={$course_code}&tab=points'>$langReadMore...</a>
        </div>
        <div class='card-body card-body-default px-0 py-0 mt-3'>
            <div class='pg-widget-wrapper'>
                <div class='pg-calendar-style-header'>";

    if (count($games_data) > 1) {
        $html .= "<button type='button' class='pg-arrow prev-btn'><i class='fa-solid fa-chevron-left'></i></button>";
    }

    $first_title = htmlspecialchars($games_data[0]['title']);
    $html .= "<span class='pg-title-text'>{$first_title}</span>";

    if (count($games_data) > 1) {
        $html .= "<button type='button' class='pg-arrow next-btn'><i class='fa-solid fa-chevron-right'></i></button>";
    }

    $html .= "</div><div class='pg-main-content'>";

    foreach ($games_data as $idx => $game) {
        $active_class = ($idx === 0) ? 'active' : '';
        $footer_text = !empty($game['next_level'])
            ? "{$game['percent']}% {$langForNextLevel} ({$game['next_level']})"
            : "{$game['percent']}% $langCompletion";

        $star_html = $game['icon_src']
            ? "<img src='" . htmlspecialchars($game['icon_src']) . "' style='width:50px;height:50px;object-fit:contain;border-radius:10px;' alt=''>"
            : "<div class='pg-star-gradient-icon'></div>" .
              (($game['level_num'] === '' || $game['level_num'] === null || $game['level_num'] == 0)
                  ? "<i class='fa-solid fa-flag-checkered pg-level-tag pg-flag-icon'></i>"
                  : "<span class='pg-level-tag'>" . htmlspecialchars($game['level_num']) . "</span>");

        $html .= "
            <div class='pg-slide {$active_class}' data-name='" . htmlspecialchars($game['title']) . "'>
                <div class='pg-inner-card'>
                    <div class='pg-row'>
                        <div class='pg-star-container'>
                            $star_html
                        </div>
                        <div class='pg-stats'>
                            <div class='pg-points-wrap'>
                                <span class='pg-val'>" . number_format($game['points']) . "</span>
                                <span class='pg-lbl'>$langPoints</span>
                            </div>
                            <div class='pg-bar-bg'>
                                <div class='pg-bar-fill' style='width:{$game['percent']}%'></div>
                            </div>
                        </div>
                    </div>
                    <div class='pg-line'></div>
                    <div class='pg-footer'>{$footer_text}</div>
                </div>
            </div>";
    }

    $html .= "</div></div></div></div>";

    $html .= "
    <style>
    .pg-widget-wrapper {
        background: white; border-radius: 8px; overflow: hidden;
        border: 1px solid #e1e8ed; box-shadow: 0 4px 12px rgba(0,0,0,0.05); width: 100%;
    }
    .pg-calendar-style-header {
        background: #2c3e50; padding: 12px 15px; display: flex;
        align-items: center; justify-content: center; gap: 25px; color: white;
    }
    .pg-arrow { background: none; border: none; color: white; cursor: pointer; padding: 5px; z-index: 10; }
    .pg-title-text { font-size: 15px; font-weight: 500; text-align: center; flex: 1; min-width: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

    .pg-main-content { padding: 15px; }
    .pg-slide { display: none; }
    .pg-slide.active { display: block; animation: pgFade 0.2s ease-in; }
    @keyframes pgFade { from { opacity: 0; } to { opacity: 1; } }

    .pg-inner-card { border: 1px solid #f0f0f0; border-radius: 8px; padding: 15px; }
    .pg-row { display: flex; align-items: center; gap: 15px; }

    .pg-star-container { position: relative; width: 50px; height: 50px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; }
    .pg-flag-icon { font-size: 11px; background: linear-gradient(135deg, #3498db, #9b59b6); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; margin-top: 4px; }
    .pg-star-gradient-icon {
        width: 100%; height: 100%;
        background: linear-gradient(135deg, #3498db, #9b59b6);
        -webkit-mask-image: url('data:image/svg+xml;utf8,<svg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 24 24\"><path d=\"M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z\" fill=\"none\" stroke=\"black\" stroke-width=\"1.5\"/></svg>');
        mask-image: url('data:image/svg+xml;utf8,<svg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 24 24\"><path d=\"M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z\" fill=\"none\" stroke=\"black\" stroke-width=\"1.5\"/></svg>');
        -webkit-mask-repeat: no-repeat; mask-repeat: no-repeat;
        -webkit-mask-size: contain; mask-size: contain;
        -webkit-mask-position: center; mask-position: center;
    }
    .pg-level-tag { position: absolute; font-size: 10px; font-weight: 700; color: #2c3e50; text-align: center; width: 30px; line-height: 1; word-wrap: break-word; }

    .pg-stats { flex: 1; min-width: 0; }
    .pg-points-wrap { display: flex; align-items: baseline; gap: 5px; margin-bottom: 5px; }
    .pg-val { font-size: 24px; font-weight: 700; color: #2c3e50; }
    .pg-lbl { font-size: 16px; color: #5d6d7e; }

    .pg-bar-bg { height: 6px; background: #ebedef; border-radius: 10px; width: 100%; }
    .pg-bar-fill { height: 100%; background: #3b82f6; border-radius: 10px; transition: width 0.3s; }

    .pg-line { height: 1px; background: #f1f1f1; margin: 12px 0; }
    .pg-footer { font-size: 12px; color: #95a5a6; }
    </style>

    <script>
    $(document).ready(function() {
        let current = 0;
        const slides = $('.pg-slide');
        const title = $('.pg-title-text');
        if(slides.length <= 1) return;
        function showSlide(idx) {
            slides.hide().removeClass('active');
            const active = $(slides[idx]).show().addClass('active');
            title.text(active.data('name'));
            current = idx;
        }
        $('.prev-btn').click(function() { showSlide((current - 1 + slides.length) % slides.length); });
        $('.next-btn').click(function() { showSlide((current + 1) % slides.length); });
    });
    </script>";

    return $html;
}
