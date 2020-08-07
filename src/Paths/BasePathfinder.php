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
     * @var array
     */
    private $parsedPaths = [];

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
    }

    /**
     * Verifies paths provided by pathfinders
     * and compiles the parseable files.
     *
     * @throws \Liaison\Revision\Exception\InvalidArgumentException
     * @return void
     */
    protected function verifyPaths()
    {
        $tempPath = [];

        foreach ($this->paths as $path) {
            // Make sure all destination paths are relative
            if ($this->fs->isAbsolutePath($path['destination'])) {
                throw new InvalidArgumentException(lang('Revision.invalidDestPathFound', [$path['destination']]));
            }

            $path['destination'] = empty($path['destination'])
                ? ''
                : rtrim($path['destination'], '\\/ ') . DIRECTORY_SEPARATOR;

            if (is_dir($path['origin'])) {
                $path['origin'] = rtrim($path['origin'], '\\/ ');
                $path['origin'] = realpath($path['origin']) . DIRECTORY_SEPARATOR;

                foreach (get_filenames($path['origin'], null, true) as $file) {
                    $tempPath[] = [
                        'origin'      => $path['origin'] . $file,
                        'destination' => str_replace(['\\', '/'], '/', $path['destination'] . $file),
                    ];
                }
            } elseif (is_file($path['origin'])) {
                $origin     = realpath($path['origin']);
                $tempPath[] = [
                    'origin'      => $origin,
                    'destination' => str_replace(['\\', '/'], '/', $path['destination'] . basename($origin)),
                ];
            } else {
                throw new InvalidArgumentException(lang('Revision.invalidOriginPathFound', [$path['origin']]));
            }
        }

        $this->parsedPaths = $tempPath;
    }

    /**
     * {@inheritDoc}
     */
    public function getPaths(): array
    {
        return $this->parsedPaths;
    }
}
