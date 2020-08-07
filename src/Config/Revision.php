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
     * Path to project's writable folder.
     *
     * @var string
     */
    public $writePath = WRITEPATH;

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
     * Allows Revision to write an entry for its
     * logs folder and other temp files to `.gitignore`.
     *
     * @var bool
     */
    public $allowGitIgnoreEntry = true;

    /**
     * Consolidator to use.
     *
     * @var string
     */
    public $consolidator = '';

    /**
     * Upgrader to use.
     *
     * This must implement `\Liaison\Revision\Upgrade\UpgraderInterface`.
     *
     * Available upgraders:
     * * `\Liaison\Revision\Upgrade\ComposerUpgrader`
     *
     * @var string
     */
    public $upgrader = \Liaison\Revision\Upgrade\ComposerUpgrader::class;

    /**
     * Pathfinder to use.
     *
     * This must be a subclass of `\Liaison\Revision\Paths\BasePathfinder`.
     *
     * Available pathfinders:
     * * `\Liaison\Revision\Paths\DefaultPathfinder`
     *
     * You can create your own pathfinder by extending
     * `BasePathfinder`, providing your own `$paths` array,
     * and indicating the class name here.
     *
     * @var string
     */
    public $pathfinder = \Liaison\Revision\Paths\DefaultPathfinder::class;

    /**
     * Default LogHandlers to use.
     *
     * These must implement `Liaison\Revision\Logs\LogHandlerInterface`.
     *
     * @var string[]
     */
    public $defaultLogHandlers = [
        \Liaison\Revision\Logs\JsonLogHandler::class,
        \Liaison\Revision\Logs\PlaintextLogHandler::class,
    ];
}
