<?php

/**
 * This file is part of Liaison Revision.
 *
 * (c) John Paul E. Balandan, CPA <paulbalandan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Liaison\Revision\Tests;

use CodeIgniter\Events\Events;
use CodeIgniter\Test\CIUnitTestCase;
use Liaison\Revision\Application;
use Liaison\Revision\Events\UpdateEvents;
use Liaison\Revision\Exception\RevisionException;
use Liaison\Revision\Upgrade\ComposerUpgrader;
use Symfony\Component\Filesystem\Exception\IOException;
use Tests\Support\Pathfinders\LiveTestPathfinder;
use Tests\Support\Traits\BackupTrait;
use Tests\Support\Traits\PathsTrait;

/**
 * @internal
 *
 * @large
 */
final class ApplicationTest extends CIUnitTestCase
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
     * The Revision workspace directory.
     *
     * @var string
     */
    protected $workspace = '';

    /**
     * @var \Liaison\Revision\Config\ConfigurationResolver
     */
    protected $config;

    /**
     * @var \Symfony\Component\Filesystem\Filesystem
     */
    protected $filesystem;

    /**
     * @var \Liaison\Revision\Upgrade\ComposerUpgrader
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

        $this->config->getConfig()->ignoreFiles = [
            $this->config->rootPath . 'vendor/codeigniter4/framework/composer.json',
        ];

        $this->upgrader = new ComposerUpgrader($this->config);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->restoreMockProject();
    }

    public function testDependenciesInstances()
    {
        $this->mockVendorDirectory();
        $this->instantiateApplication();

        $this->assertInstanceOf('Liaison\Revision\Config\ConfigurationResolver', $this->application->getConfiguration());
        $this->assertInstanceOf('Symfony\Component\Filesystem\Filesystem', $this->application->getFilesystem());
        $this->assertInstanceOf('Liaison\Revision\Files\FileManager', $this->application->getFileManager());
        $this->assertInstanceOf('Liaison\Revision\Logs\LogManager', $this->application->getLogManager());
        $this->assertInstanceOf('Liaison\Revision\Consolidation\ConsolidatorInterface', $this->application->getConsolidator());
        $this->assertInstanceOf('Liaison\Revision\Upgrade\UpgraderInterface', $this->application->getUpgrader());
        $this->assertInstanceOf('Liaison\Revision\Paths\PathfinderInterface', $this->application->getPathfinder());
        $this->assertInstanceOf('SebastianBergmann\Diff\Differ', $this->application->getDiffer());
    }

    /**
     * Live Test
     */
    public function testApplicationLifeCycleIntrospection()
    {
        // Install first the base version
        $this->upgrader->install($this->config->rootPath);
        $this->assertDirectoryExists($this->config->rootPath . 'vendor');

        // Create an instance of Application
        $workspace = $this->config->writePath . 'revision' . \DIRECTORY_SEPARATOR;
        $this->instantiateApplication($workspace, false);

        // Create the old snapshot copy
        $this->application->checkPreflightConditions();
        $this->assertDirectoryExists($workspace . 'oldSnapshot');

        // Update to latest version
        $this->updateComposerJson();
        $this->application->updateInternals();
        $this->application->analyzeModifications();
        $this->assertDirectoryExists($workspace . 'newSnapshot');

        // Consolidate the changes
        $exitcode = $this->application->consolidate();
        $this->application->analyzeMergesAndConflicts();
        $this->assertSame(EXIT_SUCCESS, $exitcode);

        // Terminate the process
        $this->assertSame(EXIT_SUCCESS, $this->application->terminate());
    }

    public function testApplicationFailsOnUpdate()
    {
        Events::on(UpdateEvents::PREUPGRADE, function (Application $app) {
            /** @var \Liaison\Revision\Upgrade\UpgraderInterface&\PHPUnit\Framework\MockObject\MockObject */
            $upgrader = $this->createMock('Liaison\Revision\Upgrade\ComposerUpgrader');
            $upgrader->method('upgrade')->willThrowException(new RevisionException(''));
            $app->setUpgrader($upgrader);
        });

        $this->mockVendorDirectory();
        $this->instantiateApplication($this->workspace);
        $this->assertSame(EXIT_ERROR, $this->application->execute());

        Events::removeAllListeners(UpdateEvents::PREUPGRADE);
    }

    public function testErroredInConsolidation()
    {
        Events::on(UpdateEvents::PRECONSOLIDATE, function (Application $app) {
            /** @var \Liaison\Revision\Consolidation\ConsolidatorInterface&\PHPUnit\Framework\MockObject\MockObject */
            $consolidator = $this->createMock('Liaison\Revision\Consolidation\DefaultConsolidator');
            $consolidator->method('mergeCreatedFiles')->willThrowException(new IOException(''));
            $app->setConsolidator($consolidator);
        });

        $this->mockVendorDirectory();
        $this->instantiateApplication($this->workspace);
        $this->assertSame(EXIT_ERROR, $this->application->execute());

        Events::removeAllListeners(UpdateEvents::PRECONSOLIDATE);
    }

    /**
     * @param string $event
     *
     * @dataProvider eventNamesProvider
     */
    public function testApplicationExitsOnErroredEvent(string $event)
    {
        Events::on($event, static function (Application $app) {
            return false;
        });

        $this->mockVendorDirectory();
        $this->instantiateApplication($this->workspace);
        $this->assertSame(EXIT_ERROR, $this->application->execute());

        Events::removeAllListeners($event);
    }

    public function eventNamesProvider(): array
    {
        return [
            [UpdateEvents::PREFLIGHT],
            [UpdateEvents::PREUPGRADE],
            [UpdateEvents::POSTUPGRADE],
            [UpdateEvents::PRECONSOLIDATE],
            [UpdateEvents::POSTCONSOLIDATE],
            [UpdateEvents::TERMINATE],
        ];
    }

    protected function instantiateApplication(?string $workspace = null, bool $mockUpgrader = true)
    {
        $this->application = new Application($workspace, $this->config);
        $this->application->setPathfinder(new LiveTestPathfinder($this->config, $this->filesystem));

        if ($mockUpgrader) {
            /** @var \Liaison\Revision\Upgrade\UpgraderInterface&\PHPUnit\Framework\MockObject\MockObject */
            $upgrader = $this->createMock('Liaison\Revision\Upgrade\ComposerUpgrader');
            $upgrader->method('upgrade')->willReturn(EXIT_SUCCESS);
            $this->application->setUpgrader($upgrader);
        }
    }

    protected function getComposerJsonContents()
    {
        if (file_exists($composer = $this->config->rootPath . 'composer.json')) {
            $json = json_decode(file_get_contents($composer), true);

            if (JSON_ERROR_NONE === json_last_error()) {
                return $json;
            }
        }

        return [];
    }

    protected function updateComposerJson()
    {
        if (empty($composer = $this->getComposerJsonContents())) {
            $this->fail('The composer.json file is either unreadable or does not exist.');
        }

        $composer['require']['codeigniter4/framework'] = '^4.0';

        $this->filesystem->dumpFile(
            $this->config->rootPath . 'composer.json',
            json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n"
        );
    }
}
