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

namespace Liaison\Revision\Paths;

use Liaison\Revision\Config\Revision;
use Liaison\Revision\Exception\InvalidArgumentException;
use Symfony\Component\Filesystem\Filesystem;

/**
 * AbstractPathfinder.
 */
abstract class AbstractPathfinder implements PathfinderInterface
{
    /**
     * Array of paths defined by pathfinders. Still for parsing.
     *
     * @var string[][]
     */
    protected $paths = [];

    /**
     * Instance of Revision configuration.
     *
     * @var \Liaison\Revision\Config\Revision
     */
    protected $config;

    /**
     * Instance of FileSystem.
     *
     * @var \Symfony\Component\Filesystem\Filesystem
     */
    protected $filesystem;

    /**
     * Array of parsed and verified paths for update and transfer.
     *
     * @var string[][]
     */
    private $parsedPaths = [];

    /**
     * Array of verified paths to ignore during local merge.
     *
     * @var string[]
     */
    private $ignoredPaths = [];

    /**
     * Constructor.
     */
    public function __construct(?Revision $config = null, ?Filesystem $filesystem = null)
    {
        $this->config = $config ?? config('Revision');
        $this->filesystem = $filesystem ?? new Filesystem();

        helper('filesystem');
        $this->verifyPaths();
        $this->verifyIgnoredPaths();
    }

    /**
     * {@inheritDoc}
     */
    public function getPaths(): array
    {
        return $this->parsedPaths;
    }

    /**
     * {@inheritDoc}
     */
    public function getIgnoredPaths(): array
    {
        return $this->ignoredPaths;
    }

    /**
     * Verifies paths provided by pathfinders
     * and compiles the parseable files.
     *
     * @throws \Liaison\Revision\Exception\InvalidArgumentException
     *
     * @return void
     */
    private function verifyPaths()
    {
        $tempPath = [];

        foreach ($this->paths as $path) {
            // Make sure all destination paths are relative
            if ($this->filesystem->isAbsolutePath($path['destination'])) {
                throw new InvalidArgumentException(lang('Revision.invalidAbsolutePathFound', [$path['destination']]));
            }

            if ('' !== $path['destination']) {
                $path['destination'] = rtrim($path['destination'], '\\/ ') . \DIRECTORY_SEPARATOR;
            }

            if (is_dir($path['origin'])) {
                $path['origin'] = realpath(rtrim($path['origin'], '\\/ ')) . \DIRECTORY_SEPARATOR;

                foreach (get_filenames($path['origin'], true, true) as $origin) {
                    if (is_file($origin)) {
                        $destination = str_replace($path['origin'], $path['destination'], $origin);
                        $destination = str_replace('\\', '/', $destination);
                        $tempPath[] = compact('origin', 'destination');
                    }
                }
            } elseif (is_file($path['origin'])) {
                $origin = realpath($path['origin']);
                $destination = str_replace('\\', '/', $path['destination'] . basename($path['origin']));
                $tempPath[] = compact('origin', 'destination');
            } else {
                throw new InvalidArgumentException(lang('Revision.invalidOriginPathFound', [$path['origin']]));
            }
        }

        $this->parsedPaths = $tempPath;
    }

    /**
     * Verifies the ignored directories and files defined in
     * the config file and compiles the valid files.
     *
     * @throws \Liaison\Revision\Exception\InvalidArgumentException
     *
     * @return void
     */
    private function verifyIgnoredPaths()
    {
        $ignoredPaths = [];
        $dirs = $this->config->ignoreDirs;
        $files = $this->config->ignoreFiles;

        foreach ($dirs as $dir) {
            if (! $this->filesystem->isAbsolutePath($dir)) {
                throw new InvalidArgumentException(lang('Revision.invalidRelativePathFound', [$dir]));
            }

            if (! is_dir($dir)) {
                throw new InvalidArgumentException(lang('Revision.invalidPathNotDirectory', [$dir]));
            }

            foreach (get_filenames($dir, true, true) as $file) {
                if (is_file($file)) {
                    $ignoredPaths[] = $file;
                }
            }
        }

        foreach ($files as $file) {
            if (! $this->filesystem->isAbsolutePath($file)) {
                throw new InvalidArgumentException(lang('Revision.invalidRelativePathFound', [$file]));
            }

            if (! is_file($file)) {
                throw new InvalidArgumentException(lang('Revision.invalidPathNotFile', [$file]));
            }

            $ignoredPaths = array_merge($ignoredPaths, [realpath($file)]);
        }

        $ignoredPaths = array_filter($ignoredPaths);
        sort($ignoredPaths);
        $this->ignoredPaths = $ignoredPaths;
    }
}
