<?php

/* ========================================================================
 * Open eClass 
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2014  Greek Universities Network - GUnet
 * A full copyright notice can be read in "/info/copyright.txt".
 * For a full list of contributors, see "credits.txt".
 *
 * Open eClass is an open platform distributed in the hope that it will
 * be useful (without any warranty), under the terms of the GNU (General
 * Public License) as published by the Free Software Foundation.
 * The full license can be read in "/info/license/license_gpl.txt".
 *
 * Contact address: GUnet Asynchronous eLearning Group,
 *                  Network Operations Center, University of Athens,
 *                  Panepistimiopolis Ilissia, 15784, Athens, Greece
 *                  e-mail: info@openeclass.org
 * ======================================================================== 
 */

class RestoreHelper {

    const STYLE_2X = '2.0';
    const STYLE_3X = '3.0';
    const STYLE_3X_MIN = '2.99';
    const FIELD_DROP = 901;

    private $eclassVersion;
    private $backupVersion;
    private $files;
    private $fields;
    private $values;

    public function __construct($eclassVersion) {
        $this->eclassVersion = $eclassVersion;
        $this->backupVersion = self::resolveBackupVersion($eclassVersion);
        $this->populateFiles();
        $this->populateFields();
        $this->populateValues();
    }

    public function getFile($obj) {
        if (isset($this->files[$this->eclassVersion][$obj])) {
            return $this->files[$this->eclassVersion][$obj];
        } else if (isset($this->files[$this->backupVersion][$obj])) {
            return $this->files[$this->backupVersion][$obj];
        } else {
            return $obj;
        }
    }

    public function getField($obj, $field) {
        if (isset($this->fields[$this->eclassVersion][$obj][$field])) {
            return $this->fields[$this->eclassVersion][$obj][$field];
        } else if (isset($this->fields[$this->backupVersion][$obj][$field])) {
            return $this->fields[$this->backupVersion][$obj][$field];
        } else {
            return $field;
        }
    }
    
    public function getValue($obj, $field, $value) {
        if (isset($this->values[$this->eclassVersion][$obj][$field]) && is_callable($this->values[$this->eclassVersion][$obj][$field])) {
            return $this->values[$this->eclassVersion][$obj][$field]($value);
        } else if (isset($this->values[$this->backupVersion][$obj][$field]) && is_callable($this->values[$this->backupVersion][$obj][$field])) {
            return $this->values[$this->backupVersion][$obj][$field]($value);
        } else {
            return $value;
        }
    }
    
    public function getBackupVersion() {
        return $this->backupVersion;
    }

    public static function resolveBackupVersion($eclassVersion) {
        if (version_compare($eclassVersion, self::STYLE_3X_MIN, '>=')) {
            return self::STYLE_3X;
        } else {
            return self::STYLE_2X;
        }
    }

    private function populateFiles() {
        $this->files = array();
        // syntax is: new name = old name
        $this->files[self::STYLE_2X]['course'] = 'cours';
        $this->files[self::STYLE_2X]['course_user'] = 'cours_user';
        $this->files[self::STYLE_2X]['announcement'] = 'annonces';
        $this->files[self::STYLE_2X]['forum_category'] = 'catagories';
        $this->files[self::STYLE_2X]['forum'] = 'forums';
        $this->files[self::STYLE_2X]['forum_topic'] = 'topics';
        $this->files[self::STYLE_2X]['forum_post'] = 'posts';
        $this->files[self::STYLE_2X]['videolink'] = 'videolinks';
        $this->files[self::STYLE_2X]['assignment'] = 'assignments';
        $this->files[self::STYLE_2X]['exercise'] = 'exercices';
        $this->files[self::STYLE_2X]['exercise_user_record']  = 'exercise_user_record';
        $this->files[self::STYLE_2X]['exercise_question'] = 'questions';
        $this->files[self::STYLE_2X]['exercise_answer'] = 'reponses';
        $this->files[self::STYLE_2X]['exercise_with_questions'] = 'exercice_question';
    }

    private function populateFields() {
        $this->fields = array();
        // syntax is: [new table][old field] = [new table][new field]
        // notice that some fields need also reverse lookup:
        // [new table][new field] = [new table][old field]
        // those are the fields used for return_mapping and map, possibly for delete option as well
        $this->fields[self::STYLE_2X]['course']['keywords'] = 'course_keywords';
        $this->fields[self::STYLE_2X]['course']['glossary_expand'] = 'expand_glossary';
        $this->fields[self::STYLE_2X]['course_user']['status'] = 'statut';
        $this->fields[self::STYLE_2X]['user']['id'] = 'user_id';
        $this->fields[self::STYLE_2X]['user']['givenname'] = 'prenom';
        $this->fields[self::STYLE_2X]['user']['surname'] = 'nom';
        $this->fields[self::STYLE_2X]['announcement']['contenu'] = 'content';
        $this->fields[self::STYLE_2X]['announcement']['temps'] = 'date';
        $this->fields[self::STYLE_2X]['announcement']['cours_id'] = 'course_id';
        $this->fields[self::STYLE_2X]['announcement']['ordre'] = 'order';
        $this->fields[self::STYLE_2X]['announcement']['visibility'] = 'visible';
        $this->fields[self::STYLE_2X]['forum_category']['cat_id'] = 'id';
        $this->fields[self::STYLE_2X]['forum_category']['id'] = 'cat_id';
        $this->fields[self::STYLE_2X]['forum']['forum_id'] = 'id';
        $this->fields[self::STYLE_2X]['forum']['id'] = 'forum_id';
        $this->fields[self::STYLE_2X]['forum']['forum_name'] = 'name';
        $this->fields[self::STYLE_2X]['forum']['forum_desc'] = 'desc';
        $this->fields[self::STYLE_2X]['forum']['forum_access'] = self::FIELD_DROP;
        $this->fields[self::STYLE_2X]['forum']['forum_moderator'] = self::FIELD_DROP;
        $this->fields[self::STYLE_2X]['forum']['forum_topics'] = 'num_topics';
        $this->fields[self::STYLE_2X]['forum']['forum_posts'] = 'num_posts';
        $this->fields[self::STYLE_2X]['forum']['forum_last_post_id'] = 'last_post_id';
        $this->fields[self::STYLE_2X]['forum']['forum_type'] = self::FIELD_DROP;
        $this->fields[self::STYLE_2X]['forum_topic']['topic_id'] = 'id';
        $this->fields[self::STYLE_2X]['forum_topic']['id'] = 'topic_id';
        $this->fields[self::STYLE_2X]['forum_topic']['topic_title'] = 'title';
        $this->fields[self::STYLE_2X]['forum_topic']['topic_poster'] = 'poster_id';
        $this->fields[self::STYLE_2X]['forum_topic']['poster_id'] = 'topic_poster';
        $this->fields[self::STYLE_2X]['forum_topic']['topic_views'] = 'num_views';
        $this->fields[self::STYLE_2X]['forum_topic']['topic_replies'] = 'num_replies';
        $this->fields[self::STYLE_2X]['forum_topic']['topic_last_post_id'] = 'last_post_id';
        $this->fields[self::STYLE_2X]['forum_topic']['topic_status'] = self::FIELD_DROP;
        $this->fields[self::STYLE_2X]['forum_topic']['topic_notify'] = self::FIELD_DROP;
        $this->fields[self::STYLE_2X]['forum_topic']['nom'] = self::FIELD_DROP;
        $this->fields[self::STYLE_2X]['forum_topic']['prenom'] = self::FIELD_DROP;
        $this->fields[self::STYLE_2X]['forum_post']['post_id'] = 'id';
        $this->fields[self::STYLE_2X]['forum_post']['id'] = 'post_id';
        $this->fields[self::STYLE_2X]['forum_post']['forum_id'] = self::FIELD_DROP;
        $this->fields[self::STYLE_2X]['forum_post']['nom'] = self::FIELD_DROP;
        $this->fields[self::STYLE_2X]['forum_post']['prenom'] = self::FIELD_DROP;
        $this->fields[self::STYLE_2X]['document']['visibility'] = 'visible';
        $this->fields[self::STYLE_2X]['video']['titre'] = 'title';
        $this->fields[self::STYLE_2X]['videolink']['titre'] = 'title';
        $this->fields[self::STYLE_2X]['dropbox_file']['uploaderId'] = 'uploader_id';
        $this->fields[self::STYLE_2X]['dropbox_file']['uploader_id'] = 'uploaderId';
        $this->fields[self::STYLE_2X]['dropbox_file']['author'] = self::FIELD_DROP;
        $this->fields[self::STYLE_2X]['dropbox_file']['uploadDate'] = 'upload_date';
        $this->fields[self::STYLE_2X]['dropbox_file']['lastUploadDate'] = 'last_upload_date';
        $this->fields[self::STYLE_2X]['lp_learnPath']['visibility'] = 'visible';
        $this->fields[self::STYLE_2X]['lp_rel_learnPath_module']['visibility'] = 'visible';
        $this->fields[self::STYLE_2X]['agenda']['titre'] = 'title';
        $this->fields[self::STYLE_2X]['agenda']['contenu'] = 'content';
        $this->fields[self::STYLE_2X]['agenda']['day'] = 'start';
        $this->fields[self::STYLE_2X]['agenda']['hour'] = self::FIELD_DROP;
        $this->fields[self::STYLE_2X]['agenda']['lasting'] = 'duration';
        $this->fields[self::STYLE_2X]['agenda']['visibility'] = 'visible';
        $this->fields[self::STYLE_2X]['exercise']['titre'] = 'title';
        $this->fields[self::STYLE_2X]['exercise']['StartDate'] = 'start_date';
        $this->fields[self::STYLE_2X]['exercise']['EndDate'] = 'end_date';
        $this->fields[self::STYLE_2X]['exercise']['TimeConstrain'] = 'time_constraint';
        $this->fields[self::STYLE_2X]['exercise']['AttemptsAllowed'] = 'attempts_allowed';
        $this->fields[self::STYLE_2X]['exercise_user_record']['RecordStartDate'] = 'record_start_date';
        $this->fields[self::STYLE_2X]['exercise_user_record']['RecordEndDate'] = 'record_end_date';
        $this->fields[self::STYLE_2X]['exercise_user_record']['TotalScore'] = 'total_score';
        $this->fields[self::STYLE_2X]['exercise_user_record']['TotalWeighting'] = 'total_weighting';
        $this->fields[self::STYLE_2X]['exercise_question']['ponderation'] = 'weight';
        $this->fields[self::STYLE_2X]['exercise_answer']['reponse'] = 'answer';
        $this->fields[self::STYLE_2X]['exercise_answer']['ponderation'] = 'weight';
        $this->fields[self::STYLE_2X]['exercise_with_questions']['exercice_id'] = 'exercise_id';
        $this->fields[self::STYLE_2X]['exercise_with_questions']['exercise_id'] = 'exercice_id';
        $this->fields[self::STYLE_2X]['course_units']['visibility'] = 'visible';
        $this->fields[self::STYLE_2X]['unit_resources']['visibility'] = 'visible';
    }
    
    private function populateValues() {
        $visibility = function($value) {
            if ($value === 'v') {
                return 1;
            } else {
                return 0;
            }
        };
        $lp_visibility = function($value) {
            if ($value === 'SHOW') {
                return 1;
            } else {
                return 0;
            }
        };
        $to_int = function($value) {
            return intval($value);
        };
        // syntax is: [new table][old field]
        $this->values[self::STYLE_2X]['announcement']['visibility'] = $visibility;
        $this->values[self::STYLE_2X]['document']['visibility'] = $visibility;
        $this->values[self::STYLE_2X]['lp_learnPath']['visibility'] = $lp_visibility;
        $this->values[self::STYLE_2X]['lp_rel_learnPath_module']['visibility'] = $lp_visibility;
        $this->values[self::STYLE_2X]['agenda']['visibility'] = $visibility;
        $this->values[self::STYLE_2X]['course_units']['visibility'] = $visibility;
        $this->values[self::STYLE_2X]['unit_resources']['visibility'] = $visibility;
        $this->values[self::STYLE_2X]['forum_category']['cat_order'] = $to_int;
    }

}
