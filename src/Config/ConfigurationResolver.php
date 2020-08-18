<?php

/**
 * This file is part of Liaison Revision.
 *
 * (c) John Paul E. Balandan, CPA <paulbalandan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Liaison\Revision\Config;

/**
 * Resolves the correct configuration to load and
 * wraps itself to it.
 */
class ConfigurationResolver
{
    /**
     * Instance of Revision config.
     *
     * @var \Liaison\Revision\Config\Revision
     */
    protected $config;

    /**
     * Constructor.
     *
     * @param null|\Liaison\Revision\Config\Revision $config
     */
    public function __construct(?Revision $config = null)
    {
        $this->config = $config ?? (class_exists('Config\Revision', false)
            ? new \Config\Revision() // @codeCoverageIgnore
            : new \Liaison\Revision\Config\Revision());

        $this->normalizePaths();
    }

    /**
     * Allows access to resolved config's properties.
     *
     * @param string $property
     *
     * @return mixed
     */
    public function __get(string $property)
    {
        if ($this->__isset($property)) {
            return $this->config->{$property};
        }
    }

    /**
     * Simple way to know if a property exists on the config.
     *
     * @param string $property
     *
     * @return bool
     */
    public function __isset(string $property)
    {
        return property_exists($this->config, $property);
    }

    /**
     * Gets the current instance of the $config object.
     *
     * @return \Liaison\Revision\Config\Revision
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Ensures all string paths are normalized.
     */
    private function normalizePaths()
    {
        $this->config->rootPath  = realpath(rtrim($this->config->rootPath, '\\/ ')) . \DIRECTORY_SEPARATOR;
        $this->config->writePath = realpath(rtrim($this->config->writePath, '\\/ ')) . \DIRECTORY_SEPARATOR;
    }
}
