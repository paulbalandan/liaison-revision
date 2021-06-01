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

use Liaison\Revision\Config\Revision;
use Liaison\Revision\Logs\AbstractLogHandler;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;

class NullLogHandler extends AbstractLogHandler
{
    /**
     * Constructor.
     *
     * @param string                                        $directory
     * @param string                                        $filename
     * @param string                                        $extension
     * @param null|\Liaison\Revision\Config\Revision        $config
     * @param null|\Symfony\Component\Filesystem\Filesystem $filesystem
     */
    public function __construct(
        ?Revision $config = null,
        ?Filesystem $filesystem = null,
        string $directory = '',
        string $filename = '',
        string $extension = ''
    ) {
        $config = $config ?? config('Revision');
        $filesystem = $filesystem ?? new Filesystem();
        parent::__construct($config, $filesystem, $directory, $filename, $extension);
    }

    /**
     * {@inheritDoc}
     */
    public function initialize()
    {
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function handle(string $level, string $message): int
    {
        return self::EXIT_ERROR;
    }

    /**
     * {@inheritDoc}
     */
    public function save()
    {
        throw new IOException('NullLogHandler throwed this.');
    }
}
