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
use Liaison\Revision\Exception\LogicException;

/**
 * Writes an entry to gitignore.
 */
final class GitignoreCommand extends BaseCommand
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
    protected $name = 'revision:gitignore';

    /**
     * The Command's usage.
     *
     * @var string
     */
    protected $usage = 'revision:gitignore [options]';

    /**
     * The Command's options.
     *
     * @var array<string, string>
     */
    protected $options = [
        '--allow-entry' => 'Override config `$allowGitIgnoreEntry` to force allow write.',
        '--disallow-entry' => 'Override config `$allowGitIgnoreEntry` to force disallow write.',
        '--write-if-missing' => 'Write a new .gitignore if none is found.',
    ];

    /**
     * The Command's description.
     *
     * @var string
     */
    protected $description = 'Writes an entry of Revision\'s temp files to `.gitignore`.';

    /**
     * Actually executes the command.
     *
     * @return void
     */
    public function run(array $params)
    {
        /** @var \Liaison\Revision\Config\Revision */
        $config = config('Revision');

        $allow = \array_key_exists('allow-entry', $params) || (bool) CLI::getOption('allow-entry');
        $deny = \array_key_exists('disallow-entry', $params) || (bool) CLI::getOption('disallow-entry');

        if ($allow && $deny) {
            throw new LogicException(lang('Revision.mutExOptionsForWriteGiven', ['allow-entry', 'disallow-entry']));
        }

        $write = $config->allowGitIgnoreEntry;
        $write = $allow ? true : ($deny ? false : $write);

        if (! $write) {
            CLI::error(lang('Revision.gitignoreWriteDenied', [self::class]), 'light_gray', 'red');
            CLI::newLine();

            return;
        }

        helper('filesystem');

        $gitignore = $config->rootPath . '.gitignore';

        if (! is_file($gitignore)) {
            CLI::write(lang('Revision.gitignoreFileMissing'), 'yellow');
            $writeNew = \array_key_exists('write-if-missing', $params) || (bool) CLI::getOption('write-if-missing');

            if (! $writeNew && 'n' === CLI::prompt(CLI::color(lang('Revision.createGitignoreFile'), 'yellow'), ['y', 'n'], 'required')) {
                // @codeCoverageIgnoreStart
                CLI::error(lang('Revision.createGitignoreEntryFail'), 'light_gray', 'red');
                CLI::newLine();

                return;
                // @codeCoverageIgnoreEnd
            }

            write_file($gitignore, '');
        }

        $contents = file_get_contents($gitignore);
        $writable = rtrim(str_replace($config->rootPath, '', $config->writePath), '\\/ ');

        if (preg_match("#{$writable}/revision/#m", $contents)) {
            CLI::write(lang('Revision.createGitignoreEntryDuplicate'), 'yellow');
            CLI::newLine();

            return;
        }

        if (! write_file($gitignore, "\n# Liaison\\Revision temp\n{$writable}/revision/\n", 'ab')) {
            CLI::error(lang('Revision.createGitignoreEntryFail'), 'light_gray', 'red');
            CLI::newLine();

            return;
        }

        CLI::write(lang('Revision.createGitignoreEntrySuccess'), 'green');
        CLI::newLine();
    }
}
