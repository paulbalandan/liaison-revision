<?php

/**
 * This file is part of Liaison Revision.
 *
 * (c) John Paul E. Balandan, CPA <paulbalandan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Liaison\Revision\Logs;

/**
 * Interface for all log handlers.
 */
interface LogHandlerInterface
{
    public const EXIT_SUCCESS  = 0;
    public const EXIT_ERROR    = 1;
    public const EXIT_CONTINUE = 2;

    /**
     * Sets the filename of the log file without its extension.
     *
     * @param string $filename
     *
     * @return LogHandlerInterface
     */
    public function setFilename(string $filename): LogHandlerInterface;

    /**
     * Sets the file extension of the log file.
     *
     * @param string $ext
     *
     * @return LogHandlerInterface
     */
    public function setExtension(string $ext): LogHandlerInterface;

    /**
     * Handles the logging of messages.
     *
     * @param string $level
     * @param string $message
     *
     * @return int
     */
    public function handle(string $level, string $message): int;
}
