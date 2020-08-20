<?php

/**
 * This file is part of Liaison Revision.
 *
 * (c) John Paul E. Balandan, CPA <paulbalandan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Liaison\Revision\Tests\Consolidation;

use CodeIgniter\Test\CIUnitTestCase;
use Liaison\Revision\Config\ConfigurationResolver;
use Liaison\Revision\Config\Revision;
use Liaison\Revision\Consolidation\DefaultConsolidator;
use Liaison\Revision\Files\FileManager;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @internal
 */
final class DefaultConsolidatorTest extends CIUnitTestCase
{
    /**
     * Backup dir for mock project.
     *
     * @var string
     */
    protected $backupDir = '';

    /**
     * @var \Liaison\Revision\Config\ConfigurationResolver
     */
    protected $config;

    /**
     * @var \Symfony\Component\Filesystem\Filesystem
     */
    protected $filesystem;

    /**
     * @var \Liaison\Revision\Files\FileManager
     */
    protected $fileManager;

    /**
     * @var \Liaison\Revision\Consolidation\DefaultConsolidator
     */
    protected $consolidator;

    protected function setUp(): void
    {
        $config             = new Revision();
        $config->rootPath   = __DIR__ . '/../../../mock';
        $config->writePath  = __DIR__ . '/../../../mock/writable';
        $this->config       = new ConfigurationResolver($config);
        $this->filesystem   = new Filesystem();
        $this->fileManager  = new FileManager();
        $workspace          = $this->config->writePath . 'revision' . \DIRECTORY_SEPARATOR;
        $this->consolidator = new DefaultConsolidator($workspace, $this->fileManager, $this->config, $this->filesystem);

        $this->fileManager->createdFiles = [
            'app/identicalFile.txt',
            'app/conflictCreated.txt',
            'app/mergeCreated.txt',
        ];
        $this->fileManager->modifiedFiles = [
            'app/identicalFile.txt',
            'app/missingForModified.txt',
            'app/conflictModified.txt',
            'app/sameOld.txt',
        ];
        $this->fileManager->deletedFiles = [
            'app/oldForDelete.txt',
            'app/sameOld.txt',
        ];

        $this->backupDir = __DIR__ . '/../../../backup';
        $this->filesystem->mirror($this->config->rootPath, $this->backupDir);
        $this->backupDir = realpath($this->backupDir);
    }

    protected function tearDown(): void
    {
        $this->filesystem->remove($this->config->rootPath);
        $this->filesystem->mirror($this->backupDir, $this->config->rootPath);
        $this->filesystem->remove($this->backupDir);
    }

    public function testMergeOfCreatedFiles()
    {
        $this->consolidator->mergeCreatedFiles();

        $this->assertNotContains('app/identicalFile.txt', $this->fileManager->mergedFiles);
        $this->assertNotContains('app/identicalFile.txt', $this->fileManager->conflicts['created']);
        $this->assertContains('app/mergeCreated.txt', $this->fileManager->mergedFiles);
        $this->assertNotContains('app/mergeCreated.txt', $this->fileManager->conflicts['created']);
        $this->assertContains('app/conflictCreated.txt', $this->fileManager->conflicts['created']);
        $this->assertNotContains('app/conflictCreated.txt', $this->fileManager->mergedFiles);
    }

    public function testMergeOfModifiedFiles()
    {
        $this->consolidator->mergeModifiedFiles();

        $this->assertNotContains('app/identicalFile.txt', $this->fileManager->mergedFiles);
        $this->assertNotContains('app/identicalFile.txt', $this->fileManager->conflicts['modified']);
        $this->assertContains('app/missingForModified.txt', $this->fileManager->mergedFiles);
        $this->assertNotContains('app/missingForModified.txt', $this->fileManager->conflicts['modified']);
        $this->assertContains('app/sameOld.txt', $this->fileManager->mergedFiles);
        $this->assertNotContains('app/sameOld.txt', $this->fileManager->conflicts['modified']);
        $this->assertNotContains('app/conflictModified.txt', $this->fileManager->mergedFiles);
        $this->assertContains('app/conflictModified.txt', $this->fileManager->conflicts['modified']);
    }

    public function testMergeOfDeletedFiles()
    {
        $this->consolidator->mergeDeletedFiles();

        $this->assertContains('app/sameOld.txt', $this->fileManager->conflicts['deleted']);
        $this->assertContains('app/oldForDelete.txt', $this->fileManager->conflicts['deleted']);
    }
}
