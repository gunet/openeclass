<?php

/**
 * @brief list available blogs
 */
function list_blogs() {
    global $id, $course_id, $tool_content, $urlServer,
           $langAddModulesButton, $langChoice, $langBlogEmpty,
           $langBlogPostTitle, $course_code, $langBlogPosts, $langSelect;

    $result = Database::get()->queryArray("SELECT * FROM blog_post WHERE course_id = ?d ORDER BY time DESC", $course_id);
    $bloginfo = array();
    foreach ($result as $row) {
        $bloginfo[] = array(
            'id' => $row->id,
            'name' => $row->title,
            'content' => $row->content);
    }
    if (count($bloginfo) == 0) {
        $tool_content .= "<div class='col-sm-12'><div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>$langBlogEmpty</span></div></div>";
    } else {
        $tool_content .= "<form action='insert.php?course=$course_code' method='post'>" .
            "<input type='hidden' name='id' value='$id'>" .
            "<div class='table-responsive'><table class='table-default'>" .
            "<thead><tr class='list-header'>" .
            "<th>$langChoice</th>" .
            "<th>$langBlogPosts</th>" .
            "<th>$langBlogPostTitle</th>" .
            "</tr></thead>";

        foreach ($bloginfo as $entry) {
            $tool_content .= "<tr>";
            $tool_content .= "<td><label class='label-container' aria-label='$langSelect'><input type='checkbox' name='blog[]' value='$entry[id]'><span class='checkmark'></span></label></td>";
            $tool_content .= "<td><a href='{$urlServer}modules/blog/index.php?course=$course_code&action=showPost&pId=$entry[id]'>" . q($entry['name']) . "</a></td>";
            $tool_content .= "<td>" . $entry['content'] . "</td>";
            $tool_content .= "</tr>";
        }
        $tool_content .= "</table></div>";
        $tool_content .= "<div class='d-flex justify-content-start mt-4'>";
        $tool_content .= "<input class='btn submitAdminBtn' type='submit' name='submit_blog' value='$langAddModulesButton'></div></form>";
    }
}
