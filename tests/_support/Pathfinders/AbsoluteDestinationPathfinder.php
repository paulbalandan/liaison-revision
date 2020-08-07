<?php

namespace Tests\Support\Pathfinders;

use Liaison\Revision\Paths\BasePathfinder;

class AbsoluteDestinationPathfinder extends BasePathfinder
{
    protected $paths = [
        [
            'origin'      => SYSTEMPATH . '../spark',
            'destination' => ROOTPATH . 'spark',
        ],
    ];
}
