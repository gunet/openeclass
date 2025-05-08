<?php

/*
 *  ========================================================================
 *  * Open eClass
 *  * E-learning and Course Management System
 *  * ========================================================================
 *  * Copyright 2003-2024, Greek Universities Network - GUnet
 *  *
 *  * Open eClass is an open platform distributed in the hope that it will
 *  * be useful (without any warranty), under the terms of the GNU (General
 *  * Public License) as published by the Free Software Foundation.
 *  * The full license can be read in "/info/license/license_gpl.txt".
 *  *
 *  * Contact address: GUnet Asynchronous eLearning Group
 *  *                  e-mail: info@openeclass.org
 *  * ========================================================================
 *
 */

class eClassTag {

    private $id;
    private $name;

    public function __construct($name = '') {
        $this->name = canonicalize_whitespace($name);
    }
    public function getID() {
        return $this->id;
    }
    public function setName($name) {
        $this->name = $name;
    }
    public function getName() {
        return $this->name;
    }
    public static function tagInput($id = null) {
        global $langTags, $head_content, $course_code;

        // initialize the tags
        if ($id) {
            require_once 'modules/tags/moduleElement.class.php';
            $moduleTag = new ModuleElement($id);

            $tags_init = $moduleTag->getTags();
            $answer = implode(',', array_map(function ($tag) {
                return '{id:"' . js_escape($tag) . '", text:"' . js_escape($tag) . '", selected: true}';
            }, $tags_init));
        } else {
            $answer = '';
        }
        $head_content .= "
            <script>
                $(function () {
                    $('#tags').select2({
                            data: [".$answer."],
                            minimumInputLength: 2,
                            tags: true,
                            tokenSeparators: [','],
                            width: '100%',
                            selectOnClose: true,
                            createSearchChoice: function(term, data) {
                              if ($(data).filter(function() {
                                return this.text.localeCompare(term) === 0;
                              }).length === 0) {
                                return {
                                  id: term,
                                  text: term
                                };
                              }
                            },
                            ajax: {
                                url: '../tags/feed.php',
                                dataType: 'json',
                                data: function(term, page) {
                                    return {
                                        course: '" . js_escape($course_code) . "',
                                        q: term
                                    };
                                },
                                processResults: function(data, page) {
                                    return {results: data};
                                }
                            }
                    });
                });
            </script>";
        $input_field = "
                <div class='form-group mt-4'>
                    <label for='tags' class='col-12 control-label-notes mb-2'>$langTags</label>
                    <div class='col-12'>
                        <select id='tags' class='form-select' name='tags[]' multiple>
                        </select>
                    </div>
                </div>
        ";
        return $input_field;
    }
    public function findOrCreate() {
       if ($this->name){
            if ($tag = $this->exists()) {
                $this->id = $tag->id;
                return $this->id;
            } else {
                $this->id = Database::get()->query("INSERT INTO `tag` (`name`) VALUES (?s)", $this->name)->lastInsertID;
                return $this->id;
            }
       } else {
           return FALSE;
       }
    }
    private function exists() {
        $tag = Database::get()->querySingle('SELECT * FROM tag WHERE name = ?s', $this->name);
        return $tag;
    }
}
