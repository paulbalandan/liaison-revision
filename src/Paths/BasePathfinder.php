<?php

/**
 * This file is part of Liaison Revision.
 *
 * (c) John Paul E. Balandan, CPA <paulbalandan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Liaison\Revision\Paths;

use Liaison\Revision\Config\ConfigurationResolver;
use Liaison\Revision\Exception\InvalidArgumentException;
use Symfony\Component\Filesystem\Filesystem;

/**
 * BasePathfinder
 */
abstract class BasePathfinder implements PathfinderInterface
{
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
     * Array of paths defined by pathfinders. Still for parsing.
     *
     * @var string[][]
     */
    protected $paths = [];

    /**
     * Instance of ConfigurationResolver.
     *
     * @var \Liaison\Revision\Config\ConfigurationResolver
     */
    protected $config;

    /**
     * Instance of FileSystem.
     *
     * @var \Symfony\Component\Filesystem\Filesystem
     */
    protected $fs;

    /**
     * Constructor.
     *
     * @param \Liaison\Revision\Config\ConfigurationResolver|null $config
     * @param \Symfony\Component\Filesystem\Filesystem|null       $fs
     */
    public function __construct(?ConfigurationResolver $config = null, ?Filesystem $fs = null)
    {
        $this->config = $config ?? new ConfigurationResolver();
        $this->fs     = $fs     ?? new Filesystem();

        helper('filesystem');
        $this->verifyPaths();
        $this->verifyIgnoredPaths();
    }

    /**
     * Verifies paths provided by pathfinders
     * and compiles the parseable files.
     *
     * @throws \Liaison\Revision\Exception\InvalidArgumentException
     * @return void
     */
    private function verifyPaths()
    {
        $tempPath = [];

        foreach ($this->paths as $path) {
            // Make sure all destination paths are relative
            if ($this->fs->isAbsolutePath($path['destination'])) {
                throw new InvalidArgumentException(lang('Revision.invalidAbsolutePathFound', [$path['destination']]));
            }

            $path['destination'] = empty($path['destination'])
                ? ''
                : rtrim($path['destination'], '\\/ ') . DIRECTORY_SEPARATOR;

            if (is_dir($path['origin'])) {
                $path['origin'] = realpath(rtrim($path['origin'], '\\/ ')) . DIRECTORY_SEPARATOR;

                foreach (get_filenames($path['origin'], true, true) as $origin) {
                    if (is_file($origin)) {
                        $destination = str_replace($path['origin'], $path['destination'], $origin);
                        $destination = str_replace(['\\', '/'], '/', $destination);

                        $tempPath[] = [
                            'origin'      => $origin,
                            'destination' => $destination,
                        ];
                    }
                }
            } elseif (is_file($path['origin'])) {
                $tempPath[] = [
                    'origin'      => realpath($path['origin']),
                    'destination' => str_replace(['\\', '/'], '/', $path['destination'] . basename($path['origin'])),
                ];
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
     * @return void
     */
    private function verifyIgnoredPaths()
    {
        $ignoredPaths = [];
        $rootPath     = rtrim($this->config->rootPath, '\\/ ') . DIRECTORY_SEPARATOR;
        $dirs         = (array) $this->config->ignoredDirs;
        $files        = (array) $this->config->ignoredFiles;

        foreach ($dirs as $dir) {
            if ($this->fs->isAbsolutePath($dir)) {
                throw new InvalidArgumentException(lang('Revision.invalidAbsolutePathFound', [$dir]));
            }

            $tempDir = $rootPath . trim($dir, '\\/ ');
            if (!is_dir($tempDir)) {
                throw new InvalidArgumentException(lang('Revision.invalidPathNotDirectory', [$dir]));
            }

            foreach (get_filenames($tempDir, true, true) as $file) {
                if (is_file($file)) {
                    $ignoredPaths[] = $file;
                }
            }
        }

        foreach ($files as $file) {
            if ($this->fs->isAbsolutePath($file)) {
                throw new InvalidArgumentException(lang('Revision.invalidAbsolutePathFound', [$file]));
            }

            $tempFile = $rootPath . trim($file, '\\/ ');
            if (!is_file($tempFile)) {
                throw new InvalidArgumentException(lang('Revision.invalidPathNotFile', [$file]));
            }

            $ignoredPaths = array_merge($ignoredPaths, [realpath($tempFile)]);
        }

        sort($ignoredPaths);
        $this->ignoredPaths = $ignoredPaths;
    }

    /**
     * {@inheritDoc}
     */
    public function getPaths(): array
    {
        return $this->parsedPaths;
    }

    /**
     * Retrieves the array of paths to be ignored during local merge.
     *
     * @return string[]
     */
    public function getIgnoredPaths(): array
    {
        return $this->ignoredPaths;
    }
}
