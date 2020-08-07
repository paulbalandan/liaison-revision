<?php

namespace Liaison\Revision\Tests\Paths;

use CodeIgniter\Test\CIUnitTestCase;
use Tests\Support\Pathfinders\AbsoluteDestinationPathfinder;
use Tests\Support\Pathfinders\InvalidPathfinder;
use Tests\Support\Pathfinders\SimplePathfinder;

class BasePathfinderTest extends CIUnitTestCase
{
    public function testNormalGetPaths()
    {
        $finder  = new SimplePathfinder();
        $subset1 = [
            'origin'      => realpath(SYSTEMPATH . '../spark'),
            'destination' => 'spark',
        ];
        $subset2 = [
            'origin'      => realpath(SYSTEMPATH . '../app/Config/App.php'),
            'destination' => 'app/Config/App.php',
        ];
        $this->assertContains($subset1, $finder->getPaths());
        $this->assertContains($subset2, $finder->getPaths());
    }

    public function testAbsoluteDestinationPathThrowsException()
    {
        $this->expectException('\Liaison\Revision\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('"' . ROOTPATH . 'spark" must be a relative path.');
        (new AbsoluteDestinationPathfinder())->getPaths();
    }

    public function testInvalidPathsGiven()
    {
        $this->expectException('\Liaison\Revision\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('"' . SYSTEMPATH . '../foo/bar" is not a valid origin file or directory.');
        (new InvalidPathfinder())->getPaths();
    }
}
