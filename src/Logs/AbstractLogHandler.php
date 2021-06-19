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

use Liaison\Revision\Config\Revision;
use Symfony\Component\Filesystem\Filesystem;

/**
 * AbstractLogHandler.
 */
abstract class AbstractLogHandler implements LogHandlerInterface
{
    /**
     * Instance of Revision configuration.
     *
     * @var \Liaison\Revision\Config\Revision
     */
    protected $config;

    /**
     * Instance of Filesystem.
     *
     * @var \Symfony\Component\Filesystem\Filesystem
     */
    protected $filesystem;

    /**
     * @var string
     */
    protected $filename = '';

    /**
     * @var string
     */
    protected $extension = '';

    /**
     * @var string
     */
    protected $directory = '';

    /**
     * Constructor.
     */
    public function __construct(
        Revision $config,
        Filesystem $filesystem,
        string $directory,
        string $filename,
        string $extension
    ) {
        $this->config = $config;
        $this->filesystem = $filesystem;

        $this
            ->setDirectory($directory)
            ->setFilename($filename)
            ->setExtension($extension)
            ->initialize()
        ;
    }

    /**
     * {@inheritDoc}
     */
    public function setDirectory(string $directory)
    {
        $this->directory = $this->config->writePath . 'revision/logs/' . $directory;
        $this->filesystem->mkdir($this->directory);
        $this->directory = realpath($this->directory) . \DIRECTORY_SEPARATOR;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setFilename(string $filename)
    {
        $this->filename = $filename . date('Y-m-d_His_') . 'UTC' . date('O');

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setExtension(string $ext)
    {
        $this->extension = $ext;

        return $this;
    }
}
