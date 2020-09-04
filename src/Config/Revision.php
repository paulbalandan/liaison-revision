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
     * * app
     * * public
     * * or their subdirectories.
     *
     * IMPORTANT: The paths here should be absolute
     * and point to the **source** directory, not the
     * project-equivalent directory.
     *
     * @var string[]
     */
    public $ignoreDirs = [];

    /**
     * Specific files to ignore updating.
     *
     * IMPORTANT: The paths here should be absolute
     * and point to the **source** file, not the
     * project-equivalent file.
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
     * If the old and new vendor snapshot files are the same,
     * this option allows Revision to additionally check
     * against equivalent project file.
     *
     * @var bool
     */
    public $fallThroughToProject = true;

    /**
     * Consolidator to use.
     *
     * This must implement `Liaison\Revision\Consolidation\ConsolidatorInterface`.
     *
     * Available consolidators:
     * * `Liaison\Revision\Consolidator\DefaultConsolidator`
     *
     * @var string
     */
    public $consolidator = 'Liaison\Revision\Consolidation\DefaultConsolidator';

    /**
     * Upgrader to use.
     *
     * This must implement `Liaison\Revision\Upgrade\UpgraderInterface`.
     *
     * Available upgraders:
     * * `Liaison\Revision\Upgrade\ComposerUpgrader`
     *
     * @var string
     */
    public $upgrader = 'Liaison\Revision\Upgrade\ComposerUpgrader';

    /**
     * Pathfinder to use.
     *
     * This must be a subclass of `Liaison\Revision\Paths\BasePathfinder`.
     *
     * Available pathfinders:
     * * `Liaison\Revision\Paths\DefaultPathfinder`
     *
     * @var string
     */
    public $pathfinder = 'Liaison\Revision\Paths\DefaultPathfinder';

    /**
     * The diff output builder to be used by the
     * `SebastianBergmann\Diff\Differ` class in generating diffs.
     *
     * Available builders:
     * * `SebastianBergmann\Diff\Output\UnifiedDiffOutputBuilder`
     * * `SebastianBergmann\Diff\Output\StrictUnifiedDiffOutputBuilder`
     * * `SebastianBergmann\Diff\Output\DiffOnlyOutputBuilder`
     *
     * @var string
     */
    public $diffOutputBuilder = 'SebastianBergmann\Diff\Output\DiffOnlyOutputBuilder';

    /**
     * Log Handlers to use.
     *
     * These must extend `Liaison\Revision\Logs\BaseLogHandler`.
     *
     * Available log handlers:
     * * `Liaison\Revision\Logs\JsonLogHandler` (enabled by default)
     * * `Liaison\Revision\Logs\PlaintextLogHandler` (enabled by default)
     * * `Liaison\Revision\Logs\XmlLogHandler`
     *
     * @var string[]
     */
    public $logHandlers = [];
}
