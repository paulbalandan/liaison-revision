<?php

/**
 * This file is part of Liaison Revision.
 *
 * (c) John Paul E. Balandan, CPA <paulbalandan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Liaison\Revision;

use Liaison\Revision\Config\ConfigurationResolver;
use Liaison\Revision\Consolidation\ConsolidatorInterface;
use Liaison\Revision\Exception\RevisionException;
use Liaison\Revision\Files\FileManager;
use Liaison\Revision\Logs\LogManager;
use Liaison\Revision\Paths\PathfinderInterface;
use Liaison\Revision\Upgrade\UpgraderInterface;
use SebastianBergmann\Diff\Differ;
use SebastianBergmann\Diff\Output\DiffOutputBuilderInterface;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * The main entry point to Liaison Revision.
 */
class Application
{
    public const VERSION = '1.0.0';

    /**
     * Absolute path to the Revision workspace.
     *
     * @var string
     */
    protected $workspace = '';

    /**
     * Array of paths to files to monitor. This
     * list has been filtered by the ignored files.
     *
     * @var string[][]
     */
    protected $files = [];

    /**
     * Instance of ConfigurationResolver
     *
     * @var \Liaison\Revision\Config\ConfigurationResolver
     */
    protected $config;

    /**
     * Instance of Filesystem
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
     * Instance of FileManager
     *
     * @var \Liaison\Revision\Files\FileManager
     */
    protected $fileManager;

    /**
     * Instance of LogManager
     *
     * @var \Liaison\Revision\Logs\LogManager
     */
    protected $logManager;

    /**
     * Constructor.
     *
     * @param null|string                                         $workspace
     * @param null|\Liaison\Revision\Config\ConfigurationResolver $config
     * @param null|\Symfony\Component\Filesystem\Filesystem       $filesystem
     */
    public function __construct(
        ?string $workspace = null,
        ?ConfigurationResolver $config = null,
        ?Filesystem $filesystem = null
    ) {
        $this->config      = $config     ?? new ConfigurationResolver();
        $this->filesystem  = $filesystem ?? new Filesystem();
        $this->fileManager = new FileManager();
        $this->logManager  = new LogManager($this->config);
        $this->initialize($workspace);
    }

    /**
     * Sets the ConsolidatorInterface instance.
     *
     * @param \Liaison\Revision\Consolidation\ConsolidatorInterface $consolidator
     *
     * @return $this
     */
    public function setConsolidator(ConsolidatorInterface $consolidator)
    {
        $this->consolidator = $consolidator;

        return $this;
    }

    /**
     * Sets the UpgraderInterface instance.
     *
     * @param \Liaison\Revision\Upgrade\UpgraderInterface $upgrader
     *
     * @return $this
     */
    public function setUpgrader(UpgraderInterface $upgrader)
    {
        $this->upgrader = $upgrader;

        return $this;
    }

    /**
     * Sets the PathfinderInterface instance.
     *
     * @param \Liaison\Revision\Paths\PathfinderInterface $pathfinder
     *
     * @return $this
     */
    public function setPathfinder(PathfinderInterface $pathfinder)
    {
        $this->pathfinder = $pathfinder;

        return $this;
    }

    /**
     * Sets the Differ instance
     *
     * @param \SebastianBergmann\Diff\Output\DiffOutputBuilderInterface $diffOutputBuilder
     *
     * @return $this
     */
    public function setDiffer(DiffOutputBuilderInterface $diffOutputBuilder)
    {
        $this->differ = new Differ($diffOutputBuilder);

        return $this;
    }

    /**
     * This ensures that paths are filtered,
     * and a snapshot of the current vendor files is created.
     */
    public function checkPreflightConditions()
    {
        $paths       = $this->pathfinder->getPaths();
        $ignore      = $this->pathfinder->getIgnoredPaths();
        $oldSnapshot = $this->workspace . 'oldSnapshot' . \DIRECTORY_SEPARATOR;

        $this->filterFilesToCopy($paths, $ignore);
        $this->createOldVendorSnapshot($oldSnapshot, $this->files);
    }

    /**
     * The main upgrade logic.
     *
     * @return int
     */
    public function updateInternals(): int
    {
        try {
            return $this->upgrader->upgrade($this->config->rootPath);
        } catch (RevisionException $e) {
            $this->logManager->logMessage([$e->getMessage()], 'error');

            return EXIT_ERROR;
        }
    }

    /**
     * After the update process is finished, this checks for any modifications
     * in the snapshot and accordingly sorts them through the FileManager.
     */
    public function analyseModifications()
    {
        $unchanged = [];

        foreach ($this->files as $file) {
            // Compare the previous snapshot with the new snapshot from update.
            $oldCopy = $this->workspace . 'oldSnapshot' . \DIRECTORY_SEPARATOR . $file['destination'];
            $project = $this->config->rootPath . $file['destination'];

            // If hashes are different, this can be new or modified.
            if (!FileManager::areIdenticalFiles($oldCopy, $file['origin']) || ($this->config->fallThroughToPath && !FileManager::areIdenticalFiles($project, $file['origin']))) {
                $newCopy = $this->workspace . 'newSnapshot' . \DIRECTORY_SEPARATOR . $file['destination'];

                try {
                    $this->filesystem->copy($file['origin'], $newCopy, true);

                    if (!is_file($oldCopy) || !is_file($project)) {
                        $this->fileManager->createdFiles[] = $file['destination'];
                    } else {
                        $this->fileManager->modifiedFiles[] = $file['destination'];
                    }
                } catch (IOExceptionInterface $e) {
                    $this->logManager->logMessage([$e->getMessage()], 'error');
                }
            } elseif (is_file($oldCopy)) {
                $unchanged[] = $file['destination'];
            }
        }

        // Remove the unchanged files from snapshot copy
        $this->fileManager->snapshotFiles = array_diff($this->fileManager->snapshotFiles, $unchanged);
        // Get the deleted files, if any
        $this->fileManager->deletedFiles = array_diff($this->fileManager->snapshotFiles, $this->fileManager->modifiedFiles);

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
     *
     * @return int
     */
    public function consolidate(): int
    {
        try {
            $this->consolidator->mergeCreatedFiles()->mergeModifiedFiles()->mergeDeletedFiles();

            return EXIT_SUCCESS;
        } catch (IOExceptionInterface $e) {
            $this->logManager->logMessage([$e->getMessage()], 'error');

            return EXIT_ERROR;
        }
    }

    /**
     * After the consolidation process, this will analyse the merges and
     * conflicts and logs them.
     */
    public function analyseMergesAndConflicts()
    {
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
     * Initializes the application.
     *
     * @param null|string $workspace
     */
    protected function initialize(?string $workspace)
    {
        if ($workspace) {
            $workspace = rtrim($workspace, '\\/ ');
        } else {
            $workspace = $this->config->writePath . 'revision/' . date('Y-m-d-His');
        }

        $this->filesystem->mkdir($workspace);
        $this->workspace = realpath($workspace) . \DIRECTORY_SEPARATOR;

        $consolidator      = $this->config->consolidator;
        $diffOutputBuilder = $this->config->diffOutputBuilder;
        $pathfinder        = $this->config->pathfinder;
        $upgrader          = $this->config->upgrader;

        $this
            ->setConsolidator(new $consolidator($this->workspace, $this->fileManager, $this->config, $this->filesystem))
            ->setDiffer(new $diffOutputBuilder())
            ->setPathfinder(new $pathfinder())
            ->setUpgrader(new $upgrader())
        ;
    }

    /**
     * Takes in the paths from pathfinder and filters out those
     * files not included in the ignore list.
     *
     * @param array $paths
     * @param array $ignore
     */
    protected function filterFilesToCopy(array $paths, array $ignore)
    {
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
     * @param string $destination
     * @param array  $paths
     */
    protected function createOldVendorSnapshot(string $destination, array $paths)
    {
        foreach ($paths as $path) {
            try {
                $this->filesystem->copy($path['origin'], $destination . $path['destination'], true);
                $this->fileManager->snapshotFiles[] = $path['destination'];
            } catch (IOExceptionInterface $e) {
                $this->logManager->logMessage([$e->getMessage()], 'error');
            }
        }
    }
}
