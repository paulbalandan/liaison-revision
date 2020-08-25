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

use Liaison\Revision\Config\ConfigurationResolver;
use Symfony\Component\Filesystem\Filesystem;

/**
 * BaseLogHandler.
 */
abstract class BaseLogHandler implements LogHandlerInterface
{
    /**
     * Instance of ConfigurationResolver.
     *
     * @var \Liaison\Revision\Config\ConfigurationResolver
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
     *
     * @param \Liaison\Revision\Config\ConfigurationResolver $config
     * @param \Symfony\Component\Filesystem\Filesystem       $filesystem
     * @param string                                         $directory
     * @param string                                         $filename
     * @param string                                         $extension
     */
    public function __construct(
        ConfigurationResolver $config,
        Filesystem $filesystem,
        string $directory,
        string $filename,
        string $extension
    ) {
        $this->config     = $config;
        $this->filesystem = $filesystem;

        $this
            ->setDirectory($directory)
            ->setFilename($filename)
            ->setExtension($extension)
            ->initialize()
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function setDirectory(string $directory)
    {
        $this->directory = $this->config->writePath . 'revision/logs/' . $directory;
        $this->filesystem->mkdir($this->directory);
        $this->directory = realpath($this->directory) . \DIRECTORY_SEPARATOR;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setFilename(string $filename)
    {
        $this->filename = $filename . date('Y-m-d_His_') . 'UTC' . date('O');

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setExtension(string $ext)
    {
        $this->extension = $ext;

        return $this;
    }
}
