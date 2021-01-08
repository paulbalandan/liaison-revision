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

namespace Tests\Support\Logs;

use Liaison\Revision\Logs\LogHandlerInterface;

class FirstNullHandler extends NullLogHandler
{
    /** @inheritDoc */
    public function handle(string $level, string $message): int
    {
        return LogHandlerInterface::EXIT_SUCCESS;
    }
}
