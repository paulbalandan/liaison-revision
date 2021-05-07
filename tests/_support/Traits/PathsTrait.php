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

namespace Tests\Support\Traits;

use Liaison\Revision\Config\Revision;
use Symfony\Component\Filesystem\Filesystem;

/**
 * PathsTrait deals with the mock project paths.
 *
 * @property string                                   $backupDir
 * @property \Liaison\Revision\Config\Revision        $config
 * @property \Symfony\Component\Filesystem\Filesystem $filesystem
 */
trait PathsTrait
{
    protected function prepareMockPaths(): void
    {
        $this->filesystem = new Filesystem();

        $backupDir = __DIR__ . '/../../../backup';
        $this->filesystem->mkdir($backupDir);
        $this->backupDir = realpath($backupDir);

        $config = new Revision();
        $config->rootPath = __DIR__ . '/../../../mock';
        $config->writePath = __DIR__ . '/../../../mock/writable';
        $this->config = $config->normalizePaths();
    }

    protected function createCleanSlatePath(): void
    {
        $root = $this->config->rootPath;
        $paths = [
            $root . 'app',
            $root . 'writable',
        ];

        $this->filesystem->remove($paths);
        $this->filesystem->mkdir($paths);
    }

    protected function mockProjectStructure(): void
    {
        $this->filesystem->mirror(SYSTEMPATH . '../app', $this->config->rootPath . 'app');
        $this->filesystem->mirror(SYSTEMPATH . '../public', $this->config->rootPath . 'public');
        $this->filesystem->mirror(SYSTEMPATH . '../writable', $this->config->rootPath . 'writable');
        $this->filesystem->copy(SYSTEMPATH . '../env', $this->config->rootPath . 'env');
        $this->filesystem->copy(SYSTEMPATH . '../spark', $this->config->rootPath . 'spark');
    }

    protected function mockVendorDirectory(): void
    {
        $this->filesystem->mirror(
            VENDORPATH . 'codeigniter4/codeigniter4',
            $this->config->rootPath . 'vendor/codeigniter4/framework'
        );
    }
}
