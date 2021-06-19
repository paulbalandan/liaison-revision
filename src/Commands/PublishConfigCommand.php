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

namespace Liaison\Revision\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\GeneratorTrait;

/**
 * Publish main configuration file.
 */
final class PublishConfigCommand extends BaseCommand
{
    use GeneratorTrait;

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
     * The Command's options.
     *
     * @var array<string, string>
     */
    protected $options = [
        '--namespace' => 'Sets the root namespace. Defaults to "APP_NAMESPACE".',
        '--force' => 'Force overwrite existing file in destination.',
    ];

    /**
     * Execute the config generation.
     *
     * @return void
     */
    public function run(array $params)
    {
        $params[0] = 'Revision';
        $params['namespace'] = 'Config';

        $this->component = 'Config';

        $this->execute($params);
    }

    /**
     * {@inheritDoc}
     */
    protected function renderTemplate(array $data = []): string
    {
        $file = __DIR__ . '/../Config/Revision.php';

        return is_file($file) ? file_get_contents($file) : '';
    }

    /**
     * {@inheritDoc}
     */
    protected function parseTemplate(string $class, array $search = [], array $replace = [], array $data = []): string
    {
        $namespace = trim(implode('\\', \array_slice(explode('\\', $class), 0, -1)), '\\');
        $search = 'namespace Liaison\Revision\Config;';
        $replace = 'namespace ' . $namespace . ';';

        $searchLicense = <<<'EOD'

            /**
             * This file is part of Liaison Revision.
             *
             * (c) 2020 John Paul E. Balandan, CPA <paulbalandan@gmail.com>
             *
             * For the full copyright and license information, please view
             * the LICENSE file that was distributed with this source code.
             */

            EOD;
        $replaceLicense = '';

        $searchExtends = 'extends BaseConfig';
        $replaceExtends = 'extends BaseRevision';

        $searchUse = 'use CodeIgniter\Config\BaseConfig;';
        $replaceUse = 'use Liaison\Revision\Config\Revision as BaseRevision;';

        return str_replace(
            [$search, $searchLicense, $searchExtends, $searchUse],
            [$replace, $replaceLicense, $replaceExtends, $replaceUse],
            $this->renderTemplate($data),
        );
    }
}
