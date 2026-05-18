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

require_once 'BasicEvent.php';

class LearningPathLessonStatusEvent extends BasicEvent {

    const ACTIVITY = 'learning path lesson status';
    const UPDPROGRESS = 'learning-path-accessed';

    public function __construct() {
        parent::__construct();

        $this->on(self::UPDPROGRESS, function($data) {
            // Boolean: 1 if every visible LP module has best lesson_status in {PASSED, COMPLETED}, else 0.
            $row = Database::get()->querySingle(
                "SELECT
                    SUM(succeeded) AS done,
                    COUNT(*) AS total
                 FROM (
                    SELECT MAX(COALESCE(UMP.lesson_status IN ('PASSED','COMPLETED'), 0)) AS succeeded
                      FROM lp_rel_learnPath_module LPM
                      JOIN lp_module M ON M.module_id = LPM.module_id
                 LEFT JOIN lp_user_module_progress UMP
                        ON UMP.learnPath_module_id = LPM.learnPath_module_id
                       AND UMP.user_id = ?d
                     WHERE LPM.learnPath_id = ?d
                       AND LPM.visible = 1
                       AND M.contentType != ?s
                  GROUP BY LPM.learnPath_module_id
                 ) sub",
                $data->uid, $data->resource, CTLABEL_);
            $threshold = ($row && intval($row->total) > 0 && intval($row->done) === intval($row->total)) ? 1 : 0;

            $this->setEventData($data);
            $this->context['threshold'] = $threshold;
            $this->emit(parent::PREPARERULES);
        });
    }

}
