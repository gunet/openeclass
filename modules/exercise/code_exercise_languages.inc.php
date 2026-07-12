<?php

/*
 * Single source of truth for code exercise languages.
 * Used by statement_admin.inc.php (dropdown) and exercise_submit.php (load mode scripts).
 * Programming language names are the same in all locales.
 *
 * Author: Marios Giannopoulos
 */

if (!isset($CODE_EXERCISE_LANGUAGES)) {

    $CODE_EXERCISE_LANGUAGES = [
        'javascript' => [
            'mode' => 'javascript/javascript.js',
            'name' => 'JavaScript',
        ],
        'python' => [
            'mode' => 'python/python.js',
            'name' => 'Python',
        ],
        'php' => [
            'mode' => 'php/php.js',
            'name' => 'PHP',
            'extra' => ['clike/clike.js'],
        ],
        'text/x-c++src' => [
            'mode' => 'clike/clike.js',
            'name' => 'C++',
        ],
        'text/x-java' => [
            'mode' => 'clike/clike.js',
            'name' => 'Java',
        ],
        'sql' => [
            'mode' => 'sql/sql.js',
            'name' => 'SQL',
        ],
        'text/html' => [
            'mode' => 'htmlmixed/htmlmixed.js',
            'name' => 'HTML',
        ],
        'css' => [
            'mode' => 'css/css.js',
            'name' => 'CSS',
        ],
    ];
}
