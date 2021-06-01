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
 */
final class GitignoreCommandTest extends CIUnitTestCase
{
    /**
     * @var resource
     */
    private $streamFilter;

    /**
     * @var string
     */
    private $original = ROOTPATH . '.gitignore';

    /**
     * @var string
     */
    private $backup = ROOTPATH . 'backup.gitignore';

    protected function setUp(): void
    {
        parent::setUp();

        CITestStreamFilter::$buffer = '';

        $this->streamFilter = stream_filter_append(STDOUT, 'CITestStreamFilter');
        $this->streamFilter = stream_filter_append(STDERR, 'CITestStreamFilter');

        if (! is_file($this->original)) {
            copy(__DIR__ . '/../../../.gitignore', $this->original);
            copy($this->original, $this->backup);
        }
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        stream_filter_remove($this->streamFilter);

        if (is_file($this->original)) {
            unlink($this->original);
        }

        if (is_file($this->backup)) {
            unlink($this->backup);
        }
    }

    public function testBothOptionsProvidedThrowsExceptions(): void
    {
        try {
            command('revision:gitignore --allow-entry --disallow-entry');
        } catch (\Throwable $e) {
            ob_end_clean();
            self::assertInstanceOf('Liaison\Revision\Exception\LogicException', $e);
        }
    }

    public function testDeniedWriteToGitignore(): void
    {
        command('revision:gitignore --disallow-entry');
        self::assertStringContainsString('not allowed to write', CITestStreamFilter::$buffer);
    }

    public function testWriteNewGitignoreIfMissing(): void
    {
        unlink($this->original);
        command('revision:gitignore --write-if-missing');
        self::assertStringContainsString('Successfully created', CITestStreamFilter::$buffer);
    }

    public function testCommandWarnsOnAlreadyWrittenGitignore(): void
    {
        command('revision:gitignore');
        CITestStreamFilter::$buffer = '';

        command('revision:gitignore');
        self::assertStringContainsString('There was already an entry', CITestStreamFilter::$buffer);
    }

    public function testWriteFailsOnUnwritableFile(): void
    {
        chmod($this->original, 0444);
        command('revision:gitignore');
        self::assertStringContainsString('Failed creating entry', CITestStreamFilter::$buffer);
        chmod($this->original, 0644);
    }
}
