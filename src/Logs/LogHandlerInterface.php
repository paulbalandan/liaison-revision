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
 * Interface for all log handlers.
 */
interface LogHandlerInterface
{
    /**
     * Exit code when things go fine as expected.
     */
    public const EXIT_SUCCESS = 0;

    /**
     * Exit code when things don't go as planned
     * and handler wishes to exit early.
     */
    public const EXIT_ERROR = 1;

    /**
     * Sets the path to the specific log handler save directory.
     *
     * @return LogHandlerInterface
     */
    public function setDirectory(string $directory);

    /**
     * Sets the filename of the log file without its extension.
     *
     * @return LogHandlerInterface
     */
    public function setFilename(string $filename);

    /**
     * Sets the file extension of the log file.
     *
     * @return LogHandlerInterface
     */
    public function setExtension(string $ext);

    /**
     * LogHandler-specific initialization.
     *
     * @return LogHandlerInterface
     */
    public function initialize();

    /**
     * Handles the logging of messages.
     */
    public function handle(string $level, string $message): int;

    /**
     * Saves the log file to respective directory.
     *
     * @throws \Symfony\Component\Filesystem\Exception\IOException
     *
     * @return void
     */
    public function save();
}
