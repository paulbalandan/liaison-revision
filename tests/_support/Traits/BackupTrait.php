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

/**
 * Handles the backup of original copy of mock directory.
 */
trait BackupTrait
{
    protected function backupMockProject()
    {
        $this->filesystem->mirror($this->config->rootPath, $this->backupDir);
    }

    protected function restoreMockProject()
    {
        $this->filesystem->remove($this->config->rootPath);
        $this->filesystem->mirror($this->backupDir, $this->config->rootPath);
        $this->filesystem->remove($this->backupDir);
    }
}
