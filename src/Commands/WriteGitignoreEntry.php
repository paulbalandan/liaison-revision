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

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Writes an entry to gitignore
 *
 * @package Liaison\Revision
 */
class WriteGitignoreEntry extends BaseCommand
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
     * The Command's description.
     *
     * @var string
     */
    protected $description = 'Writes Revision\'s temp files to `.gitignore`.';

    /**
     * Actually executes the command.
     *
     * @param array $params
     *
     * @return void
     */
    public function run(array $params)
    {
        /**
         * @var \Liaison\Revision\Config\Revision
         */
        $config = config('Revision');

        if (!$config->allowGitIgnoreEntry) {
            CLI::error(lang('Revision.gitignoreWriteDenied', [static::class]), 'light_gray', 'red');
            CLI::newLine();
            return;
        }

        helper('filesystem');

        $gitignore = rtrim($config->rootPath, '\\/ ') . DIRECTORY_SEPARATOR . '.gitignore';
        if (!is_file($gitignore)) {
            CLI::write(lang('Revision.gitignoreFileMissing'), 'yellow');
            if ('n' === CLI::prompt(CLI::color(lang('Revision.createGitignoreFile'), 'yellow'), ['y', 'n'], 'required')) {
                CLI::error(lang('Revision.createGitignoreEntryFail'), 'light_gray', 'red');
                CLI::newLine();
                return;
            }

            write_file($gitignore, '');
        }

        $contents = file_get_contents($gitignore);
        if (preg_match('#writable/revision/#m', $contents)) {
            CLI::write(lang('Revision.createGitignoreEntryDuplicate'), 'yellow');
            CLI::newLine();
            return;
        }

        if (!write_file($gitignore, "#Liaison\Revision temp\nwritable/revision/", 'ab')) {
            CLI::error(lang('Revision.createGitignoreEntryFail'), 'light_gray', 'red');
            CLI::newLine();
            return;
        }

        CLI::write(lang('Revision.createGitignoreEntrySuccess'), 'green');
        CLI::newLine();
    }
}
