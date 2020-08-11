<?php

/**
 * This file is part of Liaison Revision.
 *
 * (c) John Paul E. Balandan, CPA <paulbalandan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
