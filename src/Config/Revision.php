<?php

/**
 * This file is part of Liaison Revision.
 *
 * (c) John Paul E. Balandan, CPA <paulbalandan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Liaison\Revision\Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Revision configuration.
 *
 * @package Liaison\Revision
 */
class Revision extends BaseConfig
{
    /**
     * Path to project's root.
     *
     * @var string
     */
    public $rootPath = ROOTPATH;

    /**
     * Directories to ignore updating.
     * These can include the top directories:
     *
     * * app
     * * public
     *
     * or their subdirectories.
     *
     * NOTE: Must be relative to `$rootPath`.
     *
     * @var string[]
     */
    public $ignoreDirs = [];

    /**
     * Specific files to ignore updating. These
     * must be relative to `$rootPath`.
     *
     * @var string[]
     */
    public $ignoreFiles = [];

    /**
     * Allows Revision to write its logs folder
     * and other temp files to `.gitignore`.
     *
     * @var bool
     */
    public $allowGitignoreEntry = true;
}
