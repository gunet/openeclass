<?php

namespace modules\tc\Zoom;

use modules\tc\Zoom\Repository as ZoomRepository;

class Service
{
    private $zoomRepository;

    public function __construct(ZoomRepository $zoomRepository)
    {
        $this->zoomRepository = $zoomRepository;
    }

    public function call()
    {
        $this->zoomRepository->getUsers();
    }
}