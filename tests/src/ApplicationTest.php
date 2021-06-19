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

namespace Liaison\Revision\Tests;

use CodeIgniter\Events\Events;
use CodeIgniter\Test\CIUnitTestCase;
use Liaison\Revision\Application;
use Liaison\Revision\Events\UpdateEvents;
use Liaison\Revision\Exception\RevisionException;
use Liaison\Revision\Upgrade\ComposerUpgrader;
use Nexus\PHPUnit\Extension\Expeditable;
use Symfony\Component\Filesystem\Exception\IOException;
use Tests\Support\Pathfinders\LiveTestPathfinder;
use Tests\Support\Traits\BackupTrait;
use Tests\Support\Traits\PathsTrait;

/**
 * @internal
 *
 * @large
 *
 * @covers \Liaison\Revision\Application
 */
final class ApplicationTest extends CIUnitTestCase
{
    use BackupTrait;
    use Expeditable;
    use PathsTrait;

    /**
     * Backup dir for mock project.
     *
     * @var string
     */
    protected $backupDir = '';

    /**
     * The Revision workspace directory.
     *
     * @var string
     */
    protected $workspace = '';

    /**
     * @var \Liaison\Revision\Config\Revision
     */
    protected $config;

    /**
     * @var \Symfony\Component\Filesystem\Filesystem
     */
    protected $filesystem;

    /**
     * @var \Liaison\Revision\Upgrade\UpgraderInterface
     */
    protected $upgrader;

    /**
     * @var \Liaison\Revision\Application
     */
    protected $application;

    protected function setUp(): void
    {
        parent::setUp();

        $this->prepareMockPaths();
        $this->backupMockProject();
        $this->createCleanSlatePath();
        $this->mockProjectStructure();

        $this->workspace = $this->config->rootPath . 'revision' . \DIRECTORY_SEPARATOR;

        $this->config->ignoreFiles = [
            $this->config->rootPath . 'vendor/codeigniter4/framework/composer.json',
        ];

        $this->upgrader = new ComposerUpgrader($this->config);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->restoreMockProject();
    }

    public function testDependenciesInstances(): void
    {
        $this->mockVendorDirectory();
        $this->instantiateApplication();

        self::assertInstanceOf('Liaison\Revision\Config\Revision', $this->application->getConfiguration());
        self::assertInstanceOf('Symfony\Component\Filesystem\Filesystem', $this->application->getFilesystem());
        self::assertInstanceOf('Liaison\Revision\Files\FileManager', $this->application->getFileManager());
        self::assertInstanceOf('Liaison\Revision\Logs\LogManager', $this->application->getLogManager());
        self::assertInstanceOf('Liaison\Revision\Consolidation\ConsolidatorInterface', $this->application->getConsolidator());
        self::assertInstanceOf('Liaison\Revision\Upgrade\UpgraderInterface', $this->application->getUpgrader());
        self::assertInstanceOf('Liaison\Revision\Paths\PathfinderInterface', $this->application->getPathfinder());
        self::assertInstanceOf('SebastianBergmann\Diff\Differ', $this->application->getDiffer());
    }

    /**
     * Live Test.
     */
    public function testApplicationLifeCycleIntrospection(): void
    {
        // Install first the base version
        $this->upgrader->upgrade($this->config->rootPath);
        self::assertDirectoryExists($this->config->rootPath . 'vendor');

        // Create an instance of Application
        $workspace = $this->config->writePath . 'revision' . \DIRECTORY_SEPARATOR;
        $this->instantiateApplication($workspace, false);

        // Create the old snapshot copy
        $this->application->checkPreflightConditions();
        self::assertDirectoryExists($workspace . 'oldSnapshot');

        // Update to latest version
        $this->updateComposerJson();
        $this->application->updateInternals();
        $this->application->analyzeModifications();
        self::assertDirectoryExists($workspace . 'newSnapshot');

        // Consolidate the changes
        $exitcode = $this->application->consolidate();
        $this->application->analyzeMergesAndConflicts();
        self::assertSame(EXIT_SUCCESS, $exitcode);

        // Terminate the process
        self::assertSame(EXIT_SUCCESS, $this->application->terminate());
    }

    public function testApplicationFailsOnUpdate(): void
    {
        Events::on(UpdateEvents::PREUPGRADE, function (Application $app): void {
            /** @var \Liaison\Revision\Upgrade\UpgraderInterface&\PHPUnit\Framework\MockObject\MockObject */
            $upgrader = $this->createMock('Liaison\Revision\Upgrade\UpgraderInterface');
            $upgrader->method('upgrade')->willThrowException(new RevisionException(''));
            $app->setUpgrader($upgrader);
        });

        $this->mockVendorDirectory();
        $this->instantiateApplication($this->workspace);
        self::assertSame(EXIT_ERROR, $this->application->execute());

        Events::removeAllListeners(UpdateEvents::PREUPGRADE);
    }

    public function testErroredInConsolidation(): void
    {
        Events::on(UpdateEvents::PRECONSOLIDATE, function (Application $app): void {
            /** @var \Liaison\Revision\Consolidation\ConsolidatorInterface&\PHPUnit\Framework\MockObject\MockObject */
            $consolidator = $this->createMock('Liaison\Revision\Consolidation\ConsolidatorInterface');
            $consolidator->method('mergeCreatedFiles')->willThrowException(new IOException(''));
            $app->setConsolidator($consolidator);
        });

        $this->mockVendorDirectory();
        $this->instantiateApplication($this->workspace);
        self::assertSame(EXIT_ERROR, $this->application->execute());

        Events::removeAllListeners(UpdateEvents::PRECONSOLIDATE);
    }

    /**
     * @dataProvider eventNamesProvider
     */
    public function testApplicationExitsOnErroredEvent(string $event): void
    {
        Events::on($event, static function (Application $app): bool {
            return false;
        });

        $this->mockVendorDirectory();
        $this->instantiateApplication($this->workspace);
        self::assertSame(EXIT_ERROR, $this->application->execute());

        Events::removeAllListeners($event);
    }

    /**
     * @return array<int, array<string>>
     */
    public function eventNamesProvider(): array
    {
        return [
            [UpdateEvents::PREFLIGHT],
            [UpdateEvents::PREUPGRADE],
            [UpdateEvents::POSTUPGRADE],
            [UpdateEvents::PRECONSOLIDATE],
            [UpdateEvents::POSTCONSOLIDATE],
        ];
    }

    public function testApplicationIgnoresPreTerminateEventStatus(): void
    {
        Events::on(UpdateEvents::TERMINATE, static function (Application $app): bool {
            return false;
        });

        $this->mockVendorDirectory();
        $this->instantiateApplication($this->workspace);
        self::assertSame(EXIT_SUCCESS, $this->application->execute());

        Events::removeAllListeners(UpdateEvents::TERMINATE);
    }

    public function testGetRelativeTime(): void
    {
        $this->mockVendorDirectory();
        $this->instantiateApplication();

        self::assertStringContainsString('seconds', $this->application->getRelativeTime(37.5));
        self::assertStringContainsString('minutes', $this->application->getRelativeTime(300.0));
        self::assertStringContainsString('hours', $this->application->getRelativeTime(86400.0));
    }

    protected function instantiateApplication(?string $workspace = null, bool $mockUpgrader = true): void
    {
        $this->application = new Application($workspace, $this->config);
        $this->application->setPathfinder(new LiveTestPathfinder($this->config, $this->filesystem));

        if ($mockUpgrader) {
            /** @var \Liaison\Revision\Upgrade\UpgraderInterface&\PHPUnit\Framework\MockObject\MockObject */
            $upgrader = $this->createMock('Liaison\Revision\Upgrade\UpgraderInterface');
            $upgrader->method('upgrade')->willReturn(EXIT_SUCCESS);
            $this->application->setUpgrader($upgrader);
        }
    }

    /**
     * @return array<string, string|string[]>
     */
    protected function getComposerJsonContents(): array
    {
        if (is_file($composer = $this->config->rootPath . 'composer.json')) {
            $json = json_decode(file_get_contents($composer), true);

            if (JSON_ERROR_NONE === json_last_error()) {
                return $json;
            }
        }

        return [];
    }

    protected function updateComposerJson(): void
    {
        $composer = $this->getComposerJsonContents();

        if ([] === $composer) {
            self::fail('The composer.json file is either unreadable or does not exist.');
        }

        $composer['require']['codeigniter4/framework'] = '^4.0';

        $this->filesystem->dumpFile(
            $this->config->rootPath . 'composer.json',
            json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n",
        );
    }
}
