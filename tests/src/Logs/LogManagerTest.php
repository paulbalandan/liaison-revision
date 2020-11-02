<?php

/**
 * This file is part of Liaison Revision.
 *
 * (c) 2020 John Paul E. Balandan, CPA <paulbalandan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Liaison\Revision\Tests\Logs;

use CodeIgniter\Test\CIUnitTestCase;
use Liaison\Revision\Logs\LogManager;
use Tests\Support\Traits\BackupTrait;
use Tests\Support\Traits\PathsTrait;

/**
 * @internal
 */
final class LogManagerTest extends CIUnitTestCase
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

    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareMockPaths();
        $this->backupMockProject();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->restoreMockProject();
    }

    public function testLogManagerThrowsExceptionOnWrongLogHandlers()
    {
        $this->config->logHandlers = ['Liaison\Revision\Files\FileManager'];

        $this->expectException('Liaison\Revision\Exception\InvalidArgumentException');
        new LogManager($this->config);
    }

    public function testLogManagerManagesTheLogging()
    {
        $this->config->logHandlers = [
            'Liaison\Revision\Logs\XmlLogHandler',
            'Tests\Support\Logs\FirstNullHandler',
            'Tests\Support\Logs\NullLogHandler',
        ];

        $log = new LogManager($this->config);
        $log->logMessage('Test message');
        $log->logMessage('Error message', 'error');
        $log->save();

        $this->assertDirectoryExists($this->config->writePath . 'revision/logs');
    }
}
