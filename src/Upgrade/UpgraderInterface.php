<?php

/**
 * This file is part of Liaison Revision.
 *
 * (c) John Paul E. Balandan, CPA <paulbalandan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Liaison\Revision\Upgrade;

/**
 * UpgraderInterface.
 */
interface UpgraderInterface
{
    /**
     * Execute the upgrade process.
     *
     * @param string   $rootPath
     * @param string[] $options
     *
     * @throws \Liaison\Revision\Exception\RevisionException
     *
     * @return int
     */
    public function upgrade(string $rootPath, array $options = []): int;
}
