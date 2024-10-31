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

require_once 'H5PFramework.php';
require_once 'EditorAjax.php';
require_once 'EditorStorage.php';

class H5PFactory {

    /** @var H5PFramework The Open eClass H5PFramework implementation */
    protected $framework;

    /** @var H5PCore The Open eClass H5PCore implementation */
    protected $core;

    /** @var EditorStorage The Open eClass H5peditorStorage implementation */
    protected $editorStorage;

    /** @var H5peditor */
    protected $editor;

    /** @var EditorAjax The Open eClass H5PEditorAjaxInterface implementation */
    protected $editorAjax;

    /** @var H5PContentValidator The Open eClass H5PContentValidator implementation */
    protected $contentValidator;

    /**
     * Returns an instance of the H5PFramework class.
     *
     * @return H5PFramework
     */
    public function getFramework(): H5PFramework {
        if (null === $this->framework) {
            $this->framework = new H5PFramework();
        }

        return $this->framework;
    }

    /**
     * Returns an instance of the H5PCore class.
     *
     * @return H5PCore
     */
    public function getCore(): H5PCore {
        global $webDir, $urlServer;

        if (null === $this->core) {
            $h5pPath = $webDir . '/courses/h5p';
            $url = $urlServer . 'courses/h5p';
            $this->core = new H5PCore($this->getFramework(), $h5pPath, $url, 'en', FALSE);
        }

        return $this->core;
    }

    /**
     * Returns an instance of the H5Peditor class.
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

    /**
     * Returns an instance of the EditorAjax class.
     *
     * @return EditorAjax
     */
    public function getH5PEditorAjax(): EditorAjax {
        if (empty($this->editorAjax)) {
            $this->editorAjax = new EditorAjax();
        }

        return $this->editorAjax;
    }

    /**
     * Returns an instance of the H5PContentValidator class.
     *
     * @return H5PContentValidator
     */
    public function getContentValidator(): H5PContentValidator {
        if (empty($this->contentValidator)) {
            $this->contentValidator = new H5PContentValidator($this->getFramework(), $this->getCore());
        }

        return $this->contentValidator;
    }
}
