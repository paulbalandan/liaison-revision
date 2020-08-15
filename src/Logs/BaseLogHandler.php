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

    protected $filename  = '';
    protected $extension = '';
    protected $directory = '';

    /**
     * Constructor.
     *
     * @param string                                              $directory
     * @param string                                              $filename
     * @param string                                              $extension
     * @param null|\Liaison\Revision\Config\ConfigurationResolver $config
     * @param null|\Symfony\Component\Filesystem\Filesystem       $filesystem
     */
    public function __construct(
        string $directory,
        string $filename,
        string $extension,
        ?ConfigurationResolver $config = null,
        ?Filesystem $filesystem = null
    ) {
        $this->config     = $config     ?? new ConfigurationResolver();
        $this->filesystem = $filesystem ?? new Filesystem();
        $this->setDirectory($directory);
        $this->setFilename($filename);
        $this->setExtension($extension);
    }

    /**
     * {@inheritdoc}
     */
    public function setDirectory(string $directory)
    {
        $this->directory = rtrim($this->config->writePath, '\\/ ') . '/revision/logs/' . $directory;
        $this->filesystem->mkdir($this->directory);

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

    /**
     * Stringify values for logging.
     *
     * @param mixed $value
     *
     * @return string
     */
    protected function stringify($value): string
    {
        switch (true) {
            case \is_object($value):
                return 'Object(' . \get_class($value) . ')';
            case null === $value:
                return 'null';
            case \is_bool($value):
                return $value ? 'true' : 'false';
            case \is_array($value):
                return \count($value) ? '[...]' : '[]';
            default:
                return (string) $value;
        }
    }
}
