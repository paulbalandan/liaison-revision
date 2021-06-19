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

namespace Liaison\Revision\Upgrade;

/**
 * UpgraderInterface.
 */
interface UpgraderInterface
{
    /**
     * Execute the upgrade process.
     *
     * @param string[] $options
     *
     * @throws \Liaison\Revision\Exception\RevisionException
     */
    public function upgrade(string $rootPath, array $options = []): int;
}
