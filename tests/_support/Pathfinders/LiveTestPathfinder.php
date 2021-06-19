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

namespace Tests\Support\Pathfinders;

use Liaison\Revision\Config\Revision;
use Liaison\Revision\Paths\AbstractPathfinder;
use Symfony\Component\Filesystem\Filesystem;

class LiveTestPathfinder extends AbstractPathfinder
{
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

    /**
     * Constructor.
     */
    public function __construct(Revision $config, ?Filesystem $filesystem = null)
    {
        $paths = [];
        $systemPath = $config->rootPath . 'vendor/codeigniter4/framework/system/';

        foreach ($this->paths as $path) {
            $path['origin'] = str_replace(SYSTEMPATH, $systemPath, $path['origin']);
            $paths[] = $path;
        }

        // Add something to ignore later
        $paths[] = [
            'origin' => $systemPath . '../composer.json',
            'destination' => '',
        ];

        $this->paths = $paths;
        unset($paths);

        parent::__construct($config, $filesystem);
    }
}
