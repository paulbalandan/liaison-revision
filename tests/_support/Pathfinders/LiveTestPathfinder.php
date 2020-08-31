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

use Liaison\Revision\Config\ConfigurationResolver;
use Liaison\Revision\Paths\DefaultPathfinder;
use Symfony\Component\Filesystem\Filesystem;

class LiveTestPathfinder extends DefaultPathfinder
{
    public function __construct(ConfigurationResolver $config, ?Filesystem $filesystem = null)
    {
        $paths      = [];
        $systemPath = $config->rootPath . 'vendor/codeigniter4/framework/system/';

        foreach ($this->paths as $path) {
            $path['origin'] = str_replace(SYSTEMPATH, $systemPath, $path['origin']);
            $paths[]        = $path;
        }

        // Add something to ignore later
        $paths[] = [
            'origin'      => $systemPath . '../composer.json',
            'destination' => '',
        ];

        $this->paths = $paths;
        unset($paths);

        parent::__construct($config, $filesystem);
    }
}
