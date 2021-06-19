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
     * @var string[]
     */
    public $createdFiles = [];

    /**
     * Array of paths to deleted files.
     *
     * @var string[]
     */
    public $deletedFiles = [];

    /**
     * Array of paths to modified files.
     *
     * @var string[]
     */
    public $modifiedFiles = [];

    /**
     * Array of paths to successfully merged files.
     *
     * @var string[]
     */
    public $mergedFiles = [];

    /**
     * Array of paths to files that caused conflicts during consolidation.
     *
     * @var array<string, string[]>
     */
    public $conflicts = [
        'created' => [],
        'modified' => [],
        'deleted' => [],
    ];

    /**
     * Instance of Filesystem.
     *
     * @var null|\Symfony\Component\Filesystem\Filesystem
     */
    public static $filesystem;

    /**
     * Asserts that two files are identical in contents.
     */
    public static function areIdenticalFiles(string $pathOne, string $pathTwo): bool
    {
        if (null === self::$filesystem) {
            self::$filesystem = new Filesystem();
        }

        return self::$filesystem->exists([$pathOne, $pathTwo])
            && hash_equals(sha1_file($pathOne), sha1_file($pathTwo));
    }
}
