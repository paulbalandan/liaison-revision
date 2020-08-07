<?php

namespace Liaison\Revision\Tests\Upgrade;

use CodeIgniter\Test\CIUnitTestCase;
use Liaison\Revision\Upgrade\ComposerUpgrader;

class ComposerUpgraderTest extends CIUnitTestCase
{
    public function testComposerUpdateSuccess()
    {
        // Use getcwd to run composer update against our own path.
        $exitCode = (new ComposerUpgrader())->upgrade(getcwd());
        $this->assertEquals(0, $exitCode);
    }

    public function testComposerUpdateFail()
    {
        $this->expectException(\Liaison\Revision\Exception\RevisionException::class);
        (new ComposerUpgrader())->upgrade(getcwd() . '/inexistent/path');
    }
}
