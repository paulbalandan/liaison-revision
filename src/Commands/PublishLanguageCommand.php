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
use CodeIgniter\CLI\CLI;
use CodeIgniter\CLI\GeneratorTrait;

/**
 * Publish the main language file to the user's language folder
 * or to another locale folder.
 */
final class PublishLanguageCommand extends BaseCommand
{
    use GeneratorTrait;

    /**
     * The group the command is lumped under
     * when listing commands.
     *
     * @var string
     */
    protected $group = 'Revision';

    /**
     * The Command's name.
     *
     * @var string
     */
    protected $name = 'revision:language';

    /**
     * The Command's short description.
     *
     * @var string
     */
    protected $description = 'Publishes Revision\'s main language file to a translation folder.';

    /**
     * The Command's usage.
     *
     * @var string
     */
    protected $usage = 'revision:language [options]';

    /**
     * The Command's arguments.
     *
     * @var array<string, string>
     */
    protected $arguments = [
        'name' => '[NOT USED] Language class name.',
    ];

    /**
     * The Command's options.
     *
     * @var array<string, string>
     */
    protected $options = [
        '--lang' => 'The specific language/locale directory to publish to.',
        '--force' => 'Force overwrite existing file in destination.',
    ];

    /**
     * Execute the publication.
     *
     * @return void
     */
    public function run(array $params)
    {
        $params[0] = 'Revision';
        $params['lang'] = $params['lang'] ?? CLI::getOption('lang') ?? 'en';

        $this->component = 'Language';
        $this->directory = 'Language/' . $params['lang'];

        $this->execute($params);
    }

    /**
     * {@inheritDoc}
     */
    protected function renderTemplate(array $data = []): string
    {
        $file = __DIR__ . '/../Language/en/Revision.php';

        return is_file($file) ? file_get_contents($file) : '';
    }

    /**
     * {@inheritDoc}
     */
    protected function parseTemplate(string $class, array $search = [], array $replace = [], array $data = []): string
    {
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

        return str_replace($searchLicense, $replaceLicense, $this->renderTemplate($data));
    }
}
