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

namespace Liaison\Revision\Tests\Consolidation;

use CodeIgniter\Test\CIUnitTestCase;
use Liaison\Revision\Consolidation\DefaultConsolidator;
use Liaison\Revision\Files\FileManager;
use Tests\Support\Traits\BackupTrait;
use Tests\Support\Traits\PathsTrait;

/**
 * @internal
 *
 * @covers \Liaison\Revision\Consolidation\DefaultConsolidator
 */
final class DefaultConsolidatorTest extends CIUnitTestCase
{
    use BackupTrait;
    use PathsTrait;

    /**
     * Backup dir for mock project.
     *
     * @var string
     */
    protected $backupDir = '';

    /**
     * @var \Liaison\Revision\Config\Revision
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
        parent::setUp();
        $this->prepareMockPaths();

        $this->fileManager = new FileManager();
        $workspace = $this->config->writePath . 'revision' . \DIRECTORY_SEPARATOR;
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

        $this->backupMockProject();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->restoreMockProject();
    }

    public function testMergeOfCreatedFiles(): void
    {
        $this->consolidator->mergeCreatedFiles();

        self::assertNotContains('app/identicalFile.txt', $this->fileManager->mergedFiles);
        self::assertNotContains('app/identicalFile.txt', $this->fileManager->conflicts['created']);
        self::assertContains('app/mergeCreated.txt', $this->fileManager->mergedFiles);
        self::assertNotContains('app/mergeCreated.txt', $this->fileManager->conflicts['created']);
        self::assertContains('app/conflictCreated.txt', $this->fileManager->conflicts['created']);
        self::assertNotContains('app/conflictCreated.txt', $this->fileManager->mergedFiles);
    }

    public function testMergeOfModifiedFiles(): void
    {
        $this->consolidator->mergeModifiedFiles();

        self::assertNotContains('app/identicalFile.txt', $this->fileManager->mergedFiles);
        self::assertNotContains('app/identicalFile.txt', $this->fileManager->conflicts['modified']);
        self::assertContains('app/missingForModified.txt', $this->fileManager->mergedFiles);
        self::assertNotContains('app/missingForModified.txt', $this->fileManager->conflicts['modified']);
        self::assertContains('app/sameOld.txt', $this->fileManager->mergedFiles);
        self::assertNotContains('app/sameOld.txt', $this->fileManager->conflicts['modified']);
        self::assertNotContains('app/conflictModified.txt', $this->fileManager->mergedFiles);
        self::assertContains('app/conflictModified.txt', $this->fileManager->conflicts['modified']);
    }

    public function testMergeOfDeletedFiles(): void
    {
        $this->consolidator->mergeDeletedFiles();

        self::assertContains('app/sameOld.txt', $this->fileManager->conflicts['deleted']);
        self::assertContains('app/oldForDelete.txt', $this->fileManager->conflicts['deleted']);
    }
}
