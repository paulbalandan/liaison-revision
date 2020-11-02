<?php

/**
 * This file is part of Liaison Revision.
 *
 * (c) 2020 John Paul E. Balandan, CPA <paulbalandan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Support\Traits;

/**
 * BackupTrait handles the backup of original copy of mock directory.
 *
 * @property \Symfony\Component\Filesystem\Filesystem $filesystem
 */
trait BackupTrait
{
    protected function backupMockProject()
    {
        $this->filesystem->mirror($this->config->rootPath, $this->backupDir);
    }

    protected function restoreMockProject()
    {
        $this->filesystem->chmod($this->config->rootPath, 0777, 000, true);
        $this->filesystem->remove($this->config->rootPath);
        $this->filesystem->mirror($this->backupDir, $this->config->rootPath);
        $this->filesystem->remove($this->backupDir);
    }
}
