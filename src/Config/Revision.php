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
     * When using the safe overwrite option in updates, this
     * option sets the maximum retries the application can
     * make to create the backup file.
     *
     * This cannot be less than 1 or it will default to 10.
     *
     * @var int
     */
    public $retries = 10;

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
     * This must be a subclass of `Liaison\Revision\Paths\AbstractPathfinder`.
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
    public $diffOutputBuilder = 'SebastianBergmann\Diff\Output\UnifiedDiffOutputBuilder';

    /**
     * Settings to supply in the constructor
     * of the diff output builder of choice.
     *
     * @var array<string, mixed[]>
     *
     * @see http://github.com/sebastianbergmann/diff for the details.
     */
    public $diffOutputSettings = [
        'uniDiff' => [
            "--- Original\n+++ New\n", // string $header
            true, // bool $addLineNumbers
        ],
        'strictUniDiff' => [
            'collapseRanges' => true, // bool
            'commonLineThreshold' => 6, // int >= 0
            'contextLines' => 3, // int > 0
            'fromFile' => null, // string
            'fromFileDate' => null, // null|string
            'toFile' => null, // string
            'toFileDate' => null, // null/string
        ],
        'diffOnly' => [
            "--- Original\n+++ New\n", // string $header
        ],
    ];

    /**
     * Log Handlers to use.
     *
     * These must extend `Liaison\Revision\Logs\AbstractLogHandler`.
     *
     * Available log handlers:
     * * `Liaison\Revision\Logs\JsonLogHandler` (enabled by default)
     * * `Liaison\Revision\Logs\PlaintextLogHandler` (enabled by default)
     * * `Liaison\Revision\Logs\XmlLogHandler`
     *
     * @var string[]
     */
    public $logHandlers = [];

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->normalizePaths();
        parent::__construct();
    }

    /**
     * Resolves paths and remove relative links.
     *
     * @return $this
     */
    public function normalizePaths()
    {
        $this->rootPath = realpath(rtrim($this->rootPath, '\\/ ')) . \DIRECTORY_SEPARATOR;
        $this->writePath = realpath(rtrim($this->writePath, '\\/ ')) . \DIRECTORY_SEPARATOR;

        return $this;
    }
}
