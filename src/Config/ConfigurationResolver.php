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
     * @param \Liaison\Revision\Config\Revision|null $config
     */
    public function __construct(?Revision $config = null)
    {
        $this->config = $config ?? (class_exists('Config\Revision', false)
            ? new \Config\Revision() // @codeCoverageIgnore
            : new \Liaison\Revision\Config\Revision());
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
     * Allows access to resolved config's properties.
     *
     * @param string $property
     *
     * @return mixed
     */
    public function __get($property)
    {
        if ($this->__isset($property)) {
            return $this->config->{$property};
        }

        return null;
    }

    /**
     * Simple way to know if a property exists on the config.
     *
     * @param string $property
     *
     * @return bool
     */
    public function __isset($property)
    {
        return property_exists($this->config, $property);
    }
}
