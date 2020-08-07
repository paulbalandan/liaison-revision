<?php

/**
 * This file is part of Liaison Revision.
 *
 * (c) John Paul E. Balandan, CPA <paulbalandan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Liaison\Revision\Commands;

use CodeIgniter\CLI\GeneratorCommand;

/**
 * Publish main configuration file.
 */
class PublishConfigCommand extends GeneratorCommand
{
    /**
     * The Command's group.
     *
     * @var string
     */
    protected $group = 'Revision';

    /**
     * The Command's name.
     *
     * @var string
     */
    protected $name = 'revision:config';

    /**
     * The Command's usage.
     *
     * @var string
     */
    protected $usage = 'revision:config [options] [--] [<name>]';

    /**
     * The Command's description.
     *
     * @var string
     */
    protected $description = 'Publishes Revision\'s main config file.';

    /**
     * {@inheritDoc}
     */
    public function run(array $params)
    {
        $params[0]   = 'Revision';
        $params['n'] = 'Config';
        parent::run($params);
    }

    /**
     * {@inheritDoc}
     */
    protected function getNamespacedClass(string $rootNamespace, string $class): string
    {
        return $rootNamespace . '\\' . $class;
    }

    /**
     * {@inheritDoc}
     */
    protected function getTemplate(): string
    {
        return file_get_contents(__DIR__ . '/../Config/Revision.php') ?: '';
    }

    /**
     * {@inheritDoc}
     */
    protected function replaceNamespace(string &$template, string $class)
    {
        $search  = 'namespace Liaison\\Revision\\Config;';
        $replace = 'namespace ' . $this->getNamespace($class) . ';';

        $searchLicense = <<<EOD

/**
 * This file is part of Liaison Revision.
 *
 * (c) John Paul E. Balandan, CPA <paulbalandan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

EOD;
        $replaceLicense = '';
        $template       = str_replace([$search, $searchLicense], [$replace, $replaceLicense], $template);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    protected function replaceClass(string $template, string $class): string
    {
        $searchExtends  = 'extends BaseConfig';
        $replaceExtends = 'extends BaseRevision';

        $searchUse  = 'use CodeIgniter\\Config\\BaseConfig;';
        $replaceUse = 'use Liaison\\Revision\\Config\\Revision as BaseRevision;';

        return str_replace([$searchExtends, $searchUse], [$replaceExtends, $replaceUse], $template);
    }
}
