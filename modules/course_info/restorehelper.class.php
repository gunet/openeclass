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

    const STYLE_2X = 2;
    const STYLE_3X = 3;
    const STYLE_3X_MIN = '2.99';

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
        if ($eclassVersion >= self::STYLE_3X_MIN) {
            return self::STYLE_3X;
        } else {
            return self::STYLE_2X;
        }
    }

    private function populateFiles() {
        $this->files = array();
        $this->files[self::STYLE_2X]['course'] = 'cours';
        $this->files[self::STYLE_2X]['course_user'] = 'cours_user';
        $this->files[self::STYLE_2X]['announcement'] = 'annonces';
    }

    private function populateFields() {
        $this->fields = array();
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
    }
    
    private function populateValues() {
        $this->values[self::STYLE_2X]['announcement']['visibility'] = function($value) {
            if ($value === 'v') {
                return 1;
            } else {
                return 0;
            }
        };
    }

}
