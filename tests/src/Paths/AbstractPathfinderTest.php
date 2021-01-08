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

namespace Liaison\Revision\Tests\Paths;

use CodeIgniter\Test\CIUnitTestCase;
use Tests\Support\Configurations\SimpleConfig;
use Tests\Support\Pathfinders\AbsoluteDestinationPathfinder;
use Tests\Support\Pathfinders\InvalidPathfinder;
use Tests\Support\Pathfinders\SimplePathfinder;

/**
 * @internal
 */
final class AbstractPathfinderTest extends CIUnitTestCase
{
    public function testNormalGetPaths(): void
    {
        $finder = new SimplePathfinder();
        $subset1 = [
            'origin'      => realpath(SYSTEMPATH . '../spark'),
            'destination' => 'spark',
        ];
        $subset2 = [
            'origin'      => realpath(SYSTEMPATH . '../app/Config/App.php'),
            'destination' => 'app/Config/App.php',
        ];
        self::assertContains($subset1, $finder->getPaths());
        self::assertContains($subset2, $finder->getPaths());
    }

    public function testAbsoluteDestinationPathThrowsException(): void
    {
        $this->expectException('Liaison\Revision\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('"' . ROOTPATH . 'spark" must be a relative path.');
        (new AbsoluteDestinationPathfinder())->getPaths();
    }

    public function testInvalidPathsGiven(): void
    {
        $this->expectException('Liaison\Revision\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('"' . SYSTEMPATH . '../foo/bar" is not a valid origin file or directory.');
        (new InvalidPathfinder())->getPaths();
    }

    public function testEmptyIgnoredPaths(): void
    {
        self::assertEmpty((new SimplePathfinder())->getIgnoredPaths());
    }

    public function testArrayIgnoredPaths(): void
    {
        $finder = new SimplePathfinder(new SimpleConfig());

        self::assertIsArray($finder->getIgnoredPaths());
        self::assertContains(realpath(ROOTPATH . 'app/.htaccess'), $finder->getIgnoredPaths());
        self::assertContains(realpath(APPPATH . 'Config/Constants.php'), $finder->getIgnoredPaths());
    }

    /**
     * @param string $invalid
     * @param string $type
     * @param string $message
     *
     * @dataProvider invalidPathsProvider
     */
    public function testInvalidIgnoredPaths(string $invalid, string $type = 'file', string $message = ''): void
    {
        $config = new SimpleConfig();

        if ('dir' === $type) {
            $config->ignoreDirs[] = $invalid;
        } else {
            $config->ignoreFiles[] = $invalid;
        }

        $this->expectException('Liaison\Revision\Exception\InvalidArgumentException');
        $this->expectExceptionMessage($message);
        (new SimplePathfinder($config))->getIgnoredPaths();
    }

    /** @return array<int, array<string>> */
    public function invalidPathsProvider(): iterable
    {
        return [
            ['app', 'dir', '"app" must be an absolute path.'],
            [APPPATH . 'foo/bar', 'dir', '"' . APPPATH . 'foo/bar" is not a valid directory.'],
            ['.gitignore', 'file', '".gitignore" must be an absolute path.'],
            [APPPATH . '.env', 'file', '"' . APPPATH . '.env" is not a valid file.'],
        ];
    }
}
