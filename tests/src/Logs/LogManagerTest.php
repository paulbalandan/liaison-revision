<?php

/**
 * This file is part of Liaison Revision.
 *
 * (c) John Paul E. Balandan, CPA <paulbalandan@gmail.com>
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
     * @var \Liaison\Revision\Config\ConfigurationResolver
     */
    protected $config;

    /**
     * @var \Symfony\Component\Filesystem\Filesystem
     */
    protected $filesystem;

    protected function setUp(): void
    {
        $this->prepareMockPaths();
        $this->backupMockProject();
    }

    protected function tearDown(): void
    {
        $this->restoreMockProject();
    }

    public function testLogManagerThrowsExceptionOnWrongLogHandlers()
    {
        $this->config->getConfig()->defaultLogHandlers = ['Liaison\Revision\Files\FileManager'];

        $this->expectException('Liaison\Revision\Exception\InvalidArgumentException');
        new LogManager($this->config);
    }

    public function testLogManagerManagesTheLogging()
    {
        $this->config->getConfig()->defaultLogHandlers = [
            'Liaison\Revision\Logs\JsonLogHandler',
            'Liaison\Revision\Logs\PlaintextLogHandler',
            'Tests\Support\Logs\FirstNullHandler',
            'Tests\Support\Logs\NullLogHandler',
        ];

        $log = new LogManager($this->config);
        $log->logMessage('Test message');
        $log->logMessage('Error message', 'error');
        $log->save();
        $this->assertTrue(true);
    }
}
