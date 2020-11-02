<?php

/**
 * This file is part of Liaison Revision.
 *
 * (c) 2020 John Paul E. Balandan, CPA <paulbalandan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Liaison\Revision\Tests\Files;

use CodeIgniter\Test\CIUnitTestCase;
use Liaison\Revision\Files\FileManager;

/**
 * @internal
 */
final class FileManagerTest extends CIUnitTestCase
{
    /**
     * @param bool   $expected
     * @param string $one
     * @param string $two
     *
     * @dataProvider providePathsToCompare
     */
    public function testAreIdenticalFiles(bool $expected, string $one, string $two): void
    {
        FileManager::$filesystem = null;
        $this->assertSame($expected, FileManager::areIdenticalFiles($one, $two));
    }

    /**
     * @return array<int, array<bool|string>>
     */
    public function providePathsToCompare(): iterable
    {
        return [
            [true, __DIR__ . '/../../../composer.json', __DIR__ . '/../../../composer.json'],
            [false, __DIR__ . '/../../../.editorconfig', __DIR__ . '/../../../.gitignore'],
        ];
    }
}
