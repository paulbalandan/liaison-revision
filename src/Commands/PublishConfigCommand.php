<?php

/**
 * This file is part of Liaison Revision.
 *
 * (c) 2020 John Paul E. Balandan, CPA <paulbalandan@gmail.com>
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
     * The Command's arguments.
     *
     * @var array<string, string>
     */
    protected $arguments = [
        'name' => '[NOT USED] Config class name.',
    ];

    /**
     * Execute the config generation.
     *
     * @param array<int|string, string> $params
     *
     * @return void
     */
    public function run(array $params)
    {
        $params[0]   = 'Revision';
        $params['n'] = 'Config';

        parent::run($params);
    }

    /**
     * {@inheritdoc}
     */
    protected function getNamespacedClass(string $rootNamespace, string $class): string
    {
        return $rootNamespace . '\\' . $class;
    }

    /**
     * {@inheritdoc}
     */
    protected function getTemplate(): string
    {
        $file = __DIR__ . '/../Config/Revision.php';

        return is_file($file) ? file_get_contents($file) : '';
    }

    /**
     * {@inheritdoc}
     */
    protected function setReplacements(string $template, string $class): string
    {
        $search  = 'namespace Liaison\Revision\Config;';
        $replace = 'namespace ' . $this->getNamespace($class) . ';';

        $searchLicense = <<<'EOD'

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

        $searchExtends  = 'extends BaseConfig';
        $replaceExtends = 'extends BaseRevision';

        $searchUse  = 'use CodeIgniter\Config\BaseConfig;';
        $replaceUse = 'use Liaison\Revision\Config\Revision as BaseRevision;';

        return str_replace(
            [$search, $searchLicense, $searchExtends, $searchUse],
            [$replace, $replaceLicense, $replaceExtends, $replaceUse],
            $template
        );
    }
}
