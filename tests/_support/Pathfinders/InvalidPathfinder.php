<?php

namespace Tests\Support\Pathfinders;

use Liaison\Revision\Paths\BasePathfinder;

class InvalidPathfinder extends BasePathfinder
{
    protected $paths = [
        [
            'origin'      => SYSTEMPATH . '../foo/bar',
            'destination' => 'foo/bar',
        ],
    ];
}
