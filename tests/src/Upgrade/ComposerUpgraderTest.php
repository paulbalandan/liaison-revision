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

namespace Liaison\Revision\Tests\Upgrade;

use CodeIgniter\Test\CIUnitTestCase;
use Liaison\Revision\Upgrade\ComposerUpgrader;

/**
 * @internal
 *
 * @covers \Liaison\Revision\Upgrade\ComposerUpgrader
 */
final class ComposerUpgraderTest extends CIUnitTestCase
{
    public function testComposerUpdateSuccess(): void
    {
        // Use getcwd to run composer update against our own path.
        $exitCode = (new ComposerUpgrader())->upgrade(getcwd(), ['dry-run']);
        self::assertSame(0, $exitCode);
    }

    public function testComposerUpdateFail(): void
    {
        $this->expectException('Liaison\Revision\Exception\RevisionException');
        (new ComposerUpgrader())->upgrade(getcwd() . '/inexistent/path', ['no-ansi', 'dry-run']);
    }
}
