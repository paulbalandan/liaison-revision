<?php

namespace Tests\Support\Pathfinders;

use Liaison\Revision\Paths\BasePathfinder;

class SimplePathfinder extends BasePathfinder
{
    protected $paths = [
        [
            'origin'      => SYSTEMPATH . '../spark',
            'destination' => '',
        ],
        [
            'origin'      => SYSTEMPATH . '../app',
            'destination' => 'app',
        ],
    ];
}
