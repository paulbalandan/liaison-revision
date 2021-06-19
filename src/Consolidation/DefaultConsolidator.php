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

namespace Liaison\Revision\Consolidation;

use Liaison\Revision\Config\Revision;
use Liaison\Revision\Files\FileManager;
use Symfony\Component\Filesystem\Filesystem;

/**
 * DefaultConsolidator.
 */
final class DefaultConsolidator implements ConsolidatorInterface
{
    /**
     * Path to workspace directory.
     *
     * @var string
     */
    private $workspace;

    /**
     * Instance of FileManager.
     *
     * @var \Liaison\Revision\Files\FileManager
     */
    private $fileManager;

    /**
     * Instance of ConfigurationResolver.
     *
     * @var \Liaison\Revision\Config\Revision
     */
    private $config;

    /**
     * Instance of Filesystem.
     *
     * @var \Symfony\Component\Filesystem\Filesystem
     */
    private $filesystem;

    /**
     * Constructor.
     */
    public function __construct(
        string $workspace,
        FileManager &$fileManager,
        ?Revision $config = null,
        ?Filesystem $filesystem = null
    ) {
        $this->workspace = $workspace;
        $this->fileManager = $fileManager;
        $this->config = $config ?? config('Revision');
        $this->filesystem = $filesystem ?? new Filesystem();
    }

    /**
     * {@inheritDoc}
     */
    public function mergeCreatedFiles()
    {
        foreach ($this->fileManager->createdFiles as $file) {
            $newCopy = $this->workspace . 'newSnapshot' . \DIRECTORY_SEPARATOR . $file;
            $project = $this->config->rootPath . $file;

            if (FileManager::areIdenticalFiles($project, $newCopy)) {
                continue;
            }

            if (is_file($project)) {
                $this->fileManager->conflicts['created'][] = $file;
            } else {
                $this->filesystem->copy($newCopy, $project, true);
                $this->fileManager->mergedFiles[] = $file;
            }
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function mergeModifiedFiles()
    {
        foreach ($this->fileManager->modifiedFiles as $file) {
            $newCopy = $this->workspace . 'newSnapshot' . \DIRECTORY_SEPARATOR . $file;
            $oldCopy = $this->workspace . 'oldSnapshot' . \DIRECTORY_SEPARATOR . $file;
            $project = $this->config->rootPath . $file;

            if (FileManager::areIdenticalFiles($project, $newCopy)) {
                continue;
            }

            if (! is_file($project) || FileManager::areIdenticalFiles($project, $oldCopy)) {
                $this->filesystem->copy($newCopy, $project, true);
                $this->fileManager->mergedFiles[] = $file;
            } elseif (is_file($project)) {
                $this->fileManager->conflicts['modified'][] = $file;
            }
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function mergeDeletedFiles()
    {
        foreach ($this->fileManager->deletedFiles as $file) {
            $oldCopy = $this->workspace . 'oldSnapshot' . \DIRECTORY_SEPARATOR . $file;
            $project = $this->config->rootPath . $file;

            if (FileManager::areIdenticalFiles($project, $oldCopy) || is_file($project)) {
                $this->fileManager->conflicts['deleted'][] = $file;
            }
        }

        return $this;
    }
}
