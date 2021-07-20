<?php
/*
 * ========================================================================
 * Open eClass 3.11 - E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2021  Greek Universities Network - GUnet
 * A full copyright notice can be read in "/info/copyright.txt".
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
 *
 * For a full list of contributors, see "credits.txt".
 */

require_once 'modules/h5p/H5Pclass.php';
require_once 'EditorAjax.php';
require_once 'EditorStorage.php';

class H5PFactory {

    /** @var H5PCore The Open eClass H5PCore implementation */
    protected $core;

    /** @var EditorStorage The Open eClass H5peditorStorage implementation */
    protected $editorStorage;

    /** @var H5peditor */
    protected $editor;

    /** @var EditorAjax The Open eClass H5PEditorAjaxInterface implementation */
    protected $editorAjax;

    /**
     * Returns an instance of the H5PCore class.
     *
     * @return H5PCore
     */
    public function getCore(): H5PCore {
        global $webDir, $course_code;

        if (null === $this->core) {
            $h5pFramework = new H5Pclass();
            $h5pPath = $webDir . '/courses/h5p';
            $upload_dir = $webDir . '/courses/temp/h5p/' . $course_code;
            $this->core = new H5PCore($h5pFramework, $h5pPath, $upload_dir, 'en', FALSE);
        }

        return $this->core;
    }

    /**
     * Returns an instance of H5Peditor class.
     *
     * @return H5peditor
     */
    public function getH5PEditor(): H5peditor {
        if (null === $this->editor) {
            if (empty($this->editorStorage)) {
                $this->editorStorage = new EditorStorage();
            }

            if (empty($this->editorAjax)) {
                $this->editorAjax = new EditorAjax();
            }

            if (empty($this->editor)) {
                $this->editor = new H5peditor($this->getCore(), $this->editorStorage, $this->editorAjax);
            }
        }

        return $this->editor;
    }
}
