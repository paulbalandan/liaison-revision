<?php

namespace Liaison\Revision\Tests\Config;

use CodeIgniter\Test\CIUnitTestCase;
use Liaison\Revision\Config\ConfigurationResolver;

class ConfigurationResolverTest extends CIUnitTestCase
{
    public function testConstructorInstantiation()
    {
        $this->assertInstanceOf('Liaison\\Revision\\Config\\Revision', (new ConfigurationResolver())->getConfig());
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
