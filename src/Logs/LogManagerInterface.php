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

namespace Liaison\Revision\Logs;

/**
 * Interface for unified management of logging events.
 */
interface LogManagerInterface
{
    /**
     * Passes the message to the handlers for proper handling.
     *
     * @param string|string[] $messages
     *
     * @return void
     */
    public function logMessage($messages, string $level = 'info');

    /**
     * Makes the handlers save their logs.
     *
     * @return void
     */
    public function save();
}
