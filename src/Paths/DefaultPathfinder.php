<?php

declare(strict_types=1);

/**
 * This file is part of Liaison Revision.
 *
 * (c) 2020 John Paul E. Balandan, CPA <paulbalandan@gmail.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Liaison\Revision\Paths;

/**
 * DefaultPathfinder.
 *
 * Contains the basic paths to update.
 */
final class DefaultPathfinder extends AbstractPathfinder
{
    /**
     * Parseable paths.
     *
     * @var string[][]
     */
    protected $paths = [
        [
            'origin' => SYSTEMPATH . '../app',
            'destination' => 'app',
        ],
        [
            'origin' => SYSTEMPATH . '../public',
            'destination' => 'public',
        ],
        [
            'origin' => SYSTEMPATH . '../writable',
            'destination' => 'writable',
        ],
        [
            'origin' => SYSTEMPATH . '../spark',
            'destination' => '',
        ],
        [
            'origin' => SYSTEMPATH . '../env',
            'destination' => '',
        ],
    ];
}
