<?php

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