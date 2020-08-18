<?php

/**
 * This file is part of Liaison Revision.
 *
 * (c) John Paul E. Balandan, CPA <paulbalandan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Liaison\Revision\Files;

use Symfony\Component\Filesystem\Filesystem;

/**
 * @internal
 */
final class FileManager
{
    /**
     * Array of paths to created files.
     *
     * @var array
     */
    public $createdFiles = [];

    /**
     * Array of paths to deleted files.
     *
     * @var array
     */
    public $deletedFiles = [];

    /**
     * Array of paths to modified files.
     *
     * @var array
     */
    public $modifiedFiles = [];

    /**
     * Array of paths to successfully merged files.
     *
     * @var array
     */
    public $mergedFiles = [];

    /**
     * Array of paths to files that caused conflicts during consolidation.
     *
     * @var array
     */
    public $conflicts = [
        'created' => [],
        'merged'  => [],
        'deleted' => [],
    ];
    /**
     * Instance of Filesystem
     *
     * @var \Symfony\Component\Filesystem\Filesystem
     */
    private static $filesystem;

    /**
     * Asserts that two files are identical in contents.
     *
     * @param string $pathOne
     * @param string $pathTwo
     *
     * @return bool
     */
    public static function areIdenticalFiles(string $pathOne, string $pathTwo): bool
    {
        if (null === self::$filesystem) {
            self::$filesystem = new Filesystem();
        }

        return self::$filesystem->exists([$pathOne, $pathTwo])
            && sha1_file($pathOne) === sha1_file($pathTwo);
    }
}
