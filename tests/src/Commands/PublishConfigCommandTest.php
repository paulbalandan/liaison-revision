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

namespace Liaison\Revision\Tests\Commands;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Filters\CITestStreamFilter;

/**
 * @internal
 *
 * @covers \Liaison\Revision\Commands\PublishConfigCommand
 */
final class PublishConfigCommandTest extends CIUnitTestCase
{
    /**
     * @var resource
     */
    private $streamFilter;

    protected function setUp(): void
    {
        parent::setUp();

        CITestStreamFilter::$buffer = '';

        $this->streamFilter = stream_filter_append(STDOUT, 'CITestStreamFilter');
        $this->streamFilter = stream_filter_append(STDERR, 'CITestStreamFilter');
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        stream_filter_remove($this->streamFilter);

        $result = str_replace(["\033[0;32m", "\033[0m", "\n"], '', CITestStreamFilter::$buffer);
        $file = trim(substr($result, 14));
        $file = str_replace('APPPATH' . \DIRECTORY_SEPARATOR, APPPATH, $file);
        is_file($file) && unlink($file);
    }

    public function testPublishConfigCommandWorks(): void
    {
        command('revision:config');
        self::assertStringContainsString('File created:', CITestStreamFilter::$buffer);
    }

    public function testSupplyingOptionsIsUseless(): void
    {
        command('revision:config Update --namespace App');
        self::assertStringContainsString('File created:', CITestStreamFilter::$buffer);
        self::assertStringContainsString('Config' . \DIRECTORY_SEPARATOR . 'Revision.php', CITestStreamFilter::$buffer);
    }
}
