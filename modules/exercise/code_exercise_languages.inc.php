<?php

/*
 * Single source of truth for code exercise languages.
 * Used by statement_admin.inc.php (dropdown). Keys must match the languages in js/build/codemirror.js.
 * Programming language names are the same in all locales.
 *
 * Author: Marios Giannopoulos
 */

if (!isset($CODE_EXERCISE_LANGUAGES)) {

    $CODE_EXERCISE_LANGUAGES = [
        'text/x-c++src' => [
            'name' => 'C++',
        ],
        'text/x-csrc' => [
            'name' => 'C',
        ],
        'text/x-java' => [
            'name' => 'Java',
        ],
        'sql' => [
            'name' => 'SQL',
        ],
        'python' => [
            'name' => 'Python',
        ],
    ];
}
