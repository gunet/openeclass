<?php

/*
 * Single source of truth for code exercise languages.
 * Keys must match the languages in js/build/codemirror.js.
 * Programming language names are the same in all locales.
 *
 * Author: Marios Giannopoulos
 */

define('CODE_EXERCISE_DEFAULT_LANGUAGE', 'text/x-c++src');

/**
 * @return array language key => ['name' => display name]
 */
function code_exercise_languages(): array {
    return [
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

/**
 * @param string|null $options exercise_question.options (JSON)
 * @return string|null the question's language, or null if it isn't a code exercise
 */
function code_exercise_language(?string $options): ?string {
    $opts = json_decode($options ?? '', true);
    if (($opts['code_exercise'] ?? false) !== true) {
        return null;
    }
    $language = $opts['code_language'] ?? '';
    return isset(code_exercise_languages()[$language]) ? $language : CODE_EXERCISE_DEFAULT_LANGUAGE;
}

/**
 * Renders a submitted code answer as escaped text, highlighted read-only by CodeMirror.
 * Code must never go through purify(): HTMLPurifier would strip things like <stdio.h>.
 */
function code_exercise_answer(?string $text, string $language): string {
    global $head_content, $urlAppend;
    static $script_loaded = false;

    if (!$script_loaded) {
        $script_loaded = true;
        $head_content .= "
        <script type='module'>
        const { viewCode } = await import('{$urlAppend}js/bundle/codemirror/codemirror.js');
        document.querySelectorAll('pre.code-exercise-answer').forEach(function(pre) {
            viewCode(pre, { language: pre.getAttribute('data-language') });
        });
        </script>";
    }
    return "<pre class='code-exercise-answer' data-language='" . q($language) . "'><code>" . q($text ?? '') . "</code></pre>";
}
