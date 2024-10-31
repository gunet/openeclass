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

namespace modules\tc\Zoom\Api;

use modules\tc\Zoom\Api\Repository as ZoomRepository;
use modules\tc\Zoom\User\ZoomUser;

class Service
{
    private $zoomRepository;
    /**
     * @var ZoomUser
     */
    private $zoomUser;

    public function __construct(ZoomRepository $zoomRepository, ZoomUser $zoomUser)
    {
        $this->zoomRepository = $zoomRepository;
        $this->zoomUser = $zoomUser;
    }

    public function create(string $agenda, string $topic, string $start_time, string $auto_recording)
    {
        return $this->zoomRepository->createMeeting($this->zoomUser, $agenda, $topic, $start_time, $auto_recording);
    }
}
