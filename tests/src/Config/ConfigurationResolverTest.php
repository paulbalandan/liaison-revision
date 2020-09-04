<?php

/**
 * This file is part of Liaison Revision.
 *
 * (c) John Paul E. Balandan, CPA <paulbalandan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Liaison\Revision\Tests\Config;

use CodeIgniter\Test\CIUnitTestCase;
use Liaison\Revision\Config\ConfigurationResolver;

/**
 * @internal
 */
final class ConfigurationResolverTest extends CIUnitTestCase
{
    public function testConstructorInstantiation()
    {
        $this->assertInstanceOf('Liaison\Revision\Config\Revision', (new ConfigurationResolver())->getConfig());
    }

    public function testClassGetters()
    {
        $config = new ConfigurationResolver();

        $this->assertIsString($config->rootPath);
        $this->assertIsArray($config->ignoreDirs);
        $this->assertIsBool($config->allowGitIgnoreEntry);
        $this->assertNull($config->inexistent);
    }
}
