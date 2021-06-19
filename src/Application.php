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

namespace Liaison\Revision;

use CodeIgniter\CLI\CLI;
use CodeIgniter\Debug\Timer;
use CodeIgniter\Events\Events;
use Liaison\Revision\Config\Revision;
use Liaison\Revision\Consolidation\ConsolidatorInterface;
use Liaison\Revision\Events\UpdateEvents;
use Liaison\Revision\Exception\RevisionException;
use Liaison\Revision\Files\FileManager;
use Liaison\Revision\Logs\LogManager;
use Liaison\Revision\Paths\PathfinderInterface;
use Liaison\Revision\Upgrade\UpgraderInterface;
use SebastianBergmann\Diff\Differ;
use SebastianBergmann\Diff\Output\DiffOnlyOutputBuilder;
use SebastianBergmann\Diff\Output\DiffOutputBuilderInterface;
use SebastianBergmann\Diff\Output\StrictUnifiedDiffOutputBuilder;
use SebastianBergmann\Diff\Output\UnifiedDiffOutputBuilder;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * The main entry point to Liaison Revision.
 */
class Application
{
    /**
     * Application name.
     *
     * @var string
     */
    public const NAME = 'Liaison Revision';

    /**
     * Application version.
     *
     * @var string
     */
    public const VERSION = '1.1.0';

    /**
     * Absolute path to the Revision workspace.
     *
     * @var string
     */
    public $workspace = '';

    /**
     * Array of paths to files to monitor. This
     * list has been filtered by the ignored files.
     *
     * @var string[][]
     */
    protected $files = [];

    /**
     * Instance of Revision configuration.
     *
     * @var \Liaison\Revision\Config\Revision
     */
    protected $config;

    /**
     * Instance of Filesystem.
     *
     * @var \Symfony\Component\Filesystem\Filesystem
     */
    protected $filesystem;

    /**
     * Instance of the current consolidator.
     *
     * @var \Liaison\Revision\Consolidation\ConsolidatorInterface
     */
    protected $consolidator;

    /**
     * Instance of the current pathfinder.
     *
     * @var \Liaison\Revision\Paths\PathfinderInterface
     */
    protected $pathfinder;

    /**
     * Instance of the current upgrader.
     *
     * @var \Liaison\Revision\Upgrade\UpgraderInterface
     */
    protected $upgrader;

    /**
     * Instance of the current differ.
     *
     * @var \SebastianBergmann\Diff\Differ
     */
    protected $differ;

    /**
     * Instance of FileManager.
     *
     * @var \Liaison\Revision\Files\FileManager
     */
    protected $fileManager;

    /**
     * Instance of LogManager.
     *
     * @var \Liaison\Revision\Logs\LogManager
     */
    protected $logManager;

    /**
     * Instance of Timer.
     *
     * @internal
     *
     * @var \CodeIgniter\Debug\Timer
     */
    private $timer;

    /**
     * Constructor.
     */
    public function __construct(?string $workspace = null, ?Revision $config = null)
    {
        $config = $config ?? config('Revision');
        $config->retries = $config->retries <= 0 ? 10 : $config->retries;

        $this->config = $config;
        $this->filesystem = new Filesystem();
        $this->fileManager = new FileManager();
        $this->logManager = new LogManager($this->config);
        $this->timer = new Timer();

        FileManager::$filesystem = &$this->filesystem;
        $this->initialize($workspace);
        Events::trigger(UpdateEvents::INITIALIZE, $this);

        $this->timer->start('revision');
        $this->logManager->logMessage(lang('Revision.appInitialized'));
        $this->logManager->logMessage(lang('Revision.startUpdateText'));
    }

    /**
     * Gets the current instance of Revision configuration.
     */
    public function getConfiguration(): Revision
    {
        return $this->config;
    }

    /**
     * Gets the current instance of Filesystem.
     */
    public function getFilesystem(): Filesystem
    {
        return $this->filesystem;
    }

    /**
     * Gets the current instance of FileManager.
     */
    public function getFileManager(): FileManager
    {
        return $this->fileManager;
    }

    /**
     * Gets the current instance of LogManager.
     */
    public function getLogManager(): LogManager
    {
        return $this->logManager;
    }

    /**
     * Sets the ConsolidatorInterface instance.
     *
     * @return $this
     */
    public function setConsolidator(ConsolidatorInterface $consolidator)
    {
        $this->consolidator = $consolidator;

        return $this;
    }

    /**
     * Gets the current instance of ConsolidatorInterface.
     */
    public function getConsolidator(): ConsolidatorInterface
    {
        return $this->consolidator;
    }

    /**
     * Sets the UpgraderInterface instance.
     *
     * @return $this
     */
    public function setUpgrader(UpgraderInterface $upgrader)
    {
        $this->upgrader = $upgrader;

        return $this;
    }

    /**
     * Gets the current instance of UpgraderInterface.
     */
    public function getUpgrader(): UpgraderInterface
    {
        return $this->upgrader;
    }

    /**
     * Sets the PathfinderInterface instance.
     *
     * @return $this
     */
    public function setPathfinder(PathfinderInterface $pathfinder)
    {
        $this->pathfinder = $pathfinder;

        return $this;
    }

    /**
     * Gets the current instance of PathfinderInterface.
     */
    public function getPathfinder(): PathfinderInterface
    {
        return $this->pathfinder;
    }

    /**
     * Sets the Differ instance.
     *
     * @return $this
     */
    public function setDiffer(DiffOutputBuilderInterface $diffOutputBuilder)
    {
        $this->differ = new Differ($diffOutputBuilder);

        return $this;
    }

    /**
     * Gets the current Differ instance.
     */
    public function getDiffer(): Differ
    {
        return $this->differ;
    }

    /**
     * Executes the application update.
     */
    public function execute(): int
    {
        if (! Events::trigger(UpdateEvents::PREFLIGHT, $this)) {
            return $this->terminate(lang('Revision.terminateExecutionFailure', [UpdateEvents::PREFLIGHT]), 'error');
        }

        $this->checkPreflightConditions();

        if (! Events::trigger(UpdateEvents::PREUPGRADE, $this)) {
            return $this->terminate(lang('Revision.terminateExecutionFailure', [UpdateEvents::PREUPGRADE]), 'error');
        }

        if (EXIT_ERROR === $this->updateInternals()) {
            return $this->terminate(lang('Revision.terminateExecutionFailure', ['Application::updateInternals']), 'error');
        }

        $this->analyzeModifications();

        if (! Events::trigger(UpdateEvents::POSTUPGRADE, $this)) {
            return $this->terminate(lang('Revision.terminateExecutionFailure', [UpdateEvents::POSTUPGRADE]), 'error');
        }

        if (! Events::trigger(UpdateEvents::PRECONSOLIDATE, $this)) {
            return $this->terminate(lang('Revision.terminateExecutionFailure', [UpdateEvents::PRECONSOLIDATE]), 'error');
        }

        if (EXIT_ERROR === $this->consolidate()) {
            return $this->terminate(lang('Revision.terminateExecutionFailure', ['Application::consolidate']), 'error');
        }

        $this->analyzeMergesAndConflicts();

        if (! Events::trigger(UpdateEvents::POSTCONSOLIDATE, $this)) {
            return $this->terminate(lang('Revision.terminateExecutionFailure', [UpdateEvents::POSTCONSOLIDATE]), 'error');
        }

        return $this->terminate();
    }

    /**
     * This ensures that paths are filtered,
     * and a snapshot of the current vendor files is created.
     *
     * @return void
     */
    public function checkPreflightConditions()
    {
        $paths = $this->pathfinder->getPaths();
        $ignore = $this->pathfinder->getIgnoredPaths();
        $oldSnapshot = $this->workspace . 'oldSnapshot' . \DIRECTORY_SEPARATOR;

        $this->filterFilesToCopy($paths, $ignore);
        $this->createOldVendorSnapshot($oldSnapshot);
    }

    /**
     * The main upgrade logic.
     */
    public function updateInternals(): int
    {
        $this->logManager->logMessage(lang('Revision.updateInternals'));

        try {
            return $this->upgrader->upgrade($this->config->rootPath);
        } catch (RevisionException $e) {
            $this->logManager->logMessage($e->getMessage(), 'error');

            return EXIT_ERROR;
        }
    }

    /**
     * After the update process is finished, this checks for any modifications
     * in the snapshot and accordingly sorts them through the FileManager.
     *
     * @return void
     */
    public function analyzeModifications()
    {
        $this->logManager->logMessage(lang('Revision.analyzeModifications'));

        foreach ($this->files as $file) {
            // Compare the previous snapshot with the new snapshot from update.
            $oldCopy = $this->workspace . 'oldSnapshot' . \DIRECTORY_SEPARATOR . $file['destination'];
            $project = $this->config->rootPath . $file['destination'];
            $doCopy = true;

            // If hashes are different, this can be new or modified.
            if (! FileManager::areIdenticalFiles($oldCopy, $file['origin'])
                || ($this->config->fallThroughToProject && ! FileManager::areIdenticalFiles($project, $file['origin']))
            ) {
                $newCopy = $this->workspace . 'newSnapshot' . \DIRECTORY_SEPARATOR . $file['destination'];

                try {
                    if (! is_file($oldCopy) || ! is_file($project)) {
                        $this->fileManager->createdFiles[] = $file['destination']; // @codeCoverageIgnore
                    } elseif (is_file($file['origin'])) {
                        $this->fileManager->modifiedFiles[] = $file['destination'];
                    } else {
                        // @codeCoverageIgnoreStart
                        $this->fileManager->deletedFiles[] = $file['destination'];
                        $doCopy = false;
                        // @codeCoverageIgnoreEnd
                    }

                    if ($doCopy) {
                        $this->filesystem->copy($file['origin'], $newCopy, true);
                    }

                    // @codeCoverageIgnoreStart
                } catch (IOExceptionInterface $e) {
                    $this->logManager->logMessage($e->getMessage(), 'error');
                    // @codeCoverageIgnoreEnd
                }
            }
        }

        // Log the update results
        $cc = \count($this->fileManager->createdFiles);
        $mc = \count($this->fileManager->modifiedFiles);
        $dc = \count($this->fileManager->deletedFiles);

        $cs = 1 === $cc ? lang('Revision.fileSingular') : lang('Revision.filePlural');
        $ms = 1 === $mc ? lang('Revision.fileSingular') : lang('Revision.filePlural');
        $ds = 1 === $dc ? lang('Revision.fileSingular') : lang('Revision.filePlural');

        $this->logManager->logMessage([
            lang('Revision.createdFilesAfterUpdate', [$cc, $cs]),
            lang('Revision.modifiedFilesAfterUpdate', [$mc, $ms]),
            lang('Revision.deletedFilesAfterUpdate', [$dc, $ds]),
        ]);
    }

    /**
     * The main consolidation logic.
     */
    public function consolidate(): int
    {
        $this->logManager->logMessage(lang('Revision.consolidate'));

        try {
            $this->consolidator
                ->mergeCreatedFiles()
                ->mergeModifiedFiles()
                ->mergeDeletedFiles()
            ;

            return EXIT_SUCCESS;
        } catch (IOExceptionInterface $e) {
            $this->logManager->logMessage($e->getMessage(), 'error');

            return EXIT_ERROR;
        }
    }

    /**
     * After the consolidation process, this will analyse the merges and
     * conflicts and logs them.
     *
     * @return void
     */
    public function analyzeMergesAndConflicts()
    {
        $this->logManager->logMessage(lang('Revision.analyzeMergesAndConflicts'));

        $mc = \count($this->fileManager->mergedFiles);
        $cc = array_reduce($this->fileManager->conflicts, static function ($carry, $item) {
            $carry += \count($item);

            return $carry;
        }, 0);

        $ms = 1 === $mc ? lang('Revision.fileSingular') : lang('Revision.filePlural');
        $cs = 1 === $cc ? lang('Revision.fileSingular') : lang('Revision.filePlural');

        $this->logManager->logMessage([
            lang('Revision.mergedFilesAfterConsolidation', [$mc, $ms]),
            lang('Revision.conflictingFilesAfterConsolidation', [$cc, $cs]),
        ]);
    }

    /**
     * Terminates the current application.
     *
     * @throws \Symfony\Component\Filesystem\Exception\IOException
     */
    public function terminate(?string $message = null, string $level = 'info'): int
    {
        Events::trigger(UpdateEvents::TERMINATE, $this);
        $errored = \in_array($level, ['error', 'critical', 'debug'], true);

        // Remove the current workspace.
        $this->filesystem->chmod($this->workspace, 0777, 0000, true);
        $this->filesystem->remove($this->workspace);

        // Stop the timer
        $this->timer->stop('revision');
        $elapsed = (float) $this->timer->getElapsedTime('revision', 3);
        $time = $this->getRelativeTime($elapsed);

        // Log termination message
        $message = $message ?? lang('Revision.terminateExecutionSuccess');
        $this->logManager->logMessage($message, $level);
        $this->logManager->logMessage(lang('Revision.stopUpdateText', [$time]));

        // @codeCoverageIgnoreStart
        if (\defined('SPARKED') && ENVIRONMENT !== 'testing' && is_cli()) {
            CLI::newLine();

            if (! $errored) {
                CLI::write($message, 'green');
            } else {
                CLI::error($message, 'light_gray', 'red');
            }

            CLI::write(lang('Revision.stopUpdateText', [$time]), 'green');
        }
        // @codeCoverageIgnoreEnd

        // Flush the logs.
        $this->logManager->save();

        return $errored ? EXIT_ERROR : EXIT_SUCCESS;
    }

    /**
     * Calculates the diff of `$file`.
     *
     * @param string $file Relative path to file
     *
     * @codeCoverageIgnore
     */
    public function calculateDiff(string $file): string
    {
        $old = $this->workspace . 'oldSnapshot' . \DIRECTORY_SEPARATOR . $file;
        $new = $this->workspace . 'newSnapshot' . \DIRECTORY_SEPARATOR . $file;
        $own = $this->config->rootPath . $file;

        $oldContents = is_file($old) ? file_get_contents($old) : '';
        $newContents = is_file($new) ? file_get_contents($new) : '';
        $ownContents = is_file($own) ? file_get_contents($own) : '';

        $diff = $this->differ->diff($oldContents, $newContents);

        if (\count(explode("\n", $diff)) <= 3 && $this->config->fallThroughToProject) {
            return $this->differ->diff($ownContents, $newContents);
        }

        return $diff;
    }

    /**
     * Formats the seconds to its relative time.
     */
    public function getRelativeTime(float $seconds): string
    {
        if ($seconds < MINUTE) {
            return lang('Revision.seconds', [$seconds]);
        }

        if ($seconds < HOUR) {
            return lang('Revision.minutes', [number_format($seconds / MINUTE, 3)]);
        }

        return lang('Revision.hours', [number_format($seconds / HOUR, 3)]);
    }

    /**
     * Initializes the application.
     *
     * @return void
     */
    protected function initialize(?string $workspace)
    {
        if ((string) $workspace !== '') {
            $workspace = rtrim($workspace, '\\/ ');
        } else {
            $workspace = $this->config->writePath . 'revision/' . date('Y-m-d-His');
        }

        $this->filesystem->mkdir($workspace);
        $this->workspace = realpath($workspace) . \DIRECTORY_SEPARATOR;

        $consolidator = $this->config->consolidator;
        $pathfinder = $this->config->pathfinder;
        $upgrader = $this->config->upgrader;

        $this
            ->setConsolidator(new $consolidator($this->workspace, $this->fileManager, $this->config, $this->filesystem))
            ->setDiffer($this->getDiffOutputBuilder())
            ->setPathfinder(new $pathfinder($this->config, $this->filesystem))
            ->setUpgrader(new $upgrader($this->config))
        ;
    }

    /**
     * Takes in the paths from pathfinder and filters out those
     * files not included in the ignore list.
     *
     * @param string[][] $paths
     * @param string[]   $ignore
     *
     * @return void
     */
    protected function filterFilesToCopy(array $paths, array $ignore)
    {
        $this->logManager->logMessage(lang('Revision.filterFilesToCopy'));

        foreach ($paths as $path) {
            if (\in_array($path['origin'], $ignore, true)) {
                continue;
            }

            $this->files[] = $path;
        }
    }

    /**
     * Creates a local copy of the current vendor files as specified
     * by the pathfinder.
     *
     * @throws \Liaison\Revision\Exception\RevisionException
     *
     * @return void
     */
    protected function createOldVendorSnapshot(string $destination)
    {
        if ([] === $this->files) {
            throw new RevisionException('Cannot build snapshot. Files array is empty.'); // @codeCoverageIgnore
        }

        $this->logManager->logMessage(lang('Revision.createOldVendorSnap'));

        foreach ($this->files as $path) {
            try {
                $this->filesystem->copy($path['origin'], $destination . $path['destination'], true);
                // @codeCoverageIgnoreStart
            } catch (IOExceptionInterface $e) {
                $this->logManager->logMessage($e->getMessage(), 'error');
                // @codeCoverageIgnoreEnd
            }
        }
    }

    /**
     * Creates the right instance of the DiffOutputBuilderInterface.
     *
     * @throws \Liaison\Revision\Exception\RevisionException
     *
     * @codeCoverageIgnore
     */
    private function getDiffOutputBuilder(): DiffOutputBuilderInterface
    {
        $builder = $this->config->diffOutputBuilder;
        $setting = $this->config->diffOutputSettings;

        if (UnifiedDiffOutputBuilder::class === $builder) {
            return new $builder(...$setting['uniDiff']);
        }

        if (StrictUnifiedDiffOutputBuilder::class === $builder) {
            return new $builder($setting['strictUniDiff']);
        }

        if (DiffOnlyOutputBuilder::class === $builder) {
            return new $builder(...$setting['diffOnly']);
        }

        throw new RevisionException("{$builder} is not a configured \$diffOutputBuilder.");
    }
}
