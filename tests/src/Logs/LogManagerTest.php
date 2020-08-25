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
use Liaison\Revision\Config\ConfigurationResolver;
use Liaison\Revision\Config\Revision;
use Liaison\Revision\Logs\LogManager;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @internal
 */
final class LogManagerTest extends CIUnitTestCase
{
    /**
     * @var \Liaison\Revision\Config\ConfigurationResolver
     */
    protected $config;

    /**
     * @var \Symfony\Component\Filesystem\Filesystem
     */
    protected $filesystem;

    protected $backupDir;

    protected function setUp(): void
    {
        $config            = new Revision();
        $config->writePath = __DIR__ . '/../../../mock/writable';
        $config->rootPath  = __DIR__ . '/../../../mock';
        $this->config      = new ConfigurationResolver($config);
        $this->filesystem  = new Filesystem();

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
