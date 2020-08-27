<?php

/**
 * This file is part of Liaison Revision.
 *
 * (c) John Paul E. Balandan, CPA <paulbalandan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Support\Traits;

use Liaison\Revision\Config\ConfigurationResolver;
use Liaison\Revision\Config\Revision;
use Symfony\Component\Filesystem\Filesystem;

/**
 * PathsTrait deals with the mock project paths.
 */
trait PathsTrait
{
    protected function prepareMockPaths()
    {
        $this->filesystem = new Filesystem();

        $backupDir = __DIR__ . '/../../../backup';
        $this->filesystem->mkdir($backupDir);
        $this->backupDir = realpath($backupDir);

        $config            = new Revision();
        $config->rootPath  = __DIR__ . '/../../../mock';
        $config->writePath = __DIR__ . '/../../../mock/writable';
        $this->config      = new ConfigurationResolver($config);
    }

    protected function createCleanSlatePath()
    {
        $root  = $this->config->rootPath;
        $paths = [
            $root . 'app',
            $root . 'writable',
        ];

        $this->filesystem->remove($paths);
        $this->filesystem->mkdir($paths);
    }
}
