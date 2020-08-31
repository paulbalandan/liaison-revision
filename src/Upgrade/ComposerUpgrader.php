<?php

/**
 * This file is part of Liaison Revision.
 *
 * (c) John Paul E. Balandan, CPA <paulbalandan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Liaison\Revision\Upgrade;

use CodeIgniter\CLI\CLI;
use Liaison\Revision\Config\ConfigurationResolver;
use Liaison\Revision\Exception\RevisionException;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;
use Throwable;

/**
 * Upgrader for Composer-installed projects.
 */
class ComposerUpgrader implements UpgraderInterface
{
    /**
     * Instance of ConfigurationResolver
     *
     * @var \Liaison\Revision\Config\ConfigurationResolver
     */
    protected $config;

    /**
     * Constructor.
     *
     * @param null|\Liaison\Revision\Config\ConfigurationResolver $config
     */
    public function __construct(?ConfigurationResolver $config = null)
    {
        $this->config = $config ?? new ConfigurationResolver();
    }

    /**
     * Installs the project.
     *
     * @param string   $rootPath
     * @param string[] $options
     *
     * @return int
     */
    public function install(string $rootPath, array $options = []): int
    {
        /**
         * Default behavior now is to exclude non-system directory.
         * Add `--prefer-source` to download these.
         *
         * @see https://github.com/codeigniter4/CodeIgniter4/pull/3438
         */
        $cmd = $this->findComposerPhar() . ' install --ansi --prefer-source';
        $cmd = $this->applyCommandOptions($cmd, $options);

        return $this->runProcess($cmd, $rootPath);
    }

    /**
     * {@inheritdoc}
     */
    public function upgrade(string $rootPath, array $options = []): int
    {
        /**
         * Default behavior now is to exclude non-system directory.
         * Add `--prefer-source` to download these.
         *
         * @see https://github.com/codeigniter4/CodeIgniter4/pull/3438
         */
        $cmd = $this->findComposerPhar() . ' update --ansi --prefer-source';
        $cmd = $this->applyCommandOptions($cmd, $options);

        return $this->runProcess($cmd, $rootPath);
    }

    /**
     * Gets the path of the composer executable.
     *
     * @throws RevisionException
     *
     * @return string
     */
    protected function findComposerPhar(): string
    {
        $phpBinary     = (string) (new PhpExecutableFinder())->find(false);
        $composerLocal = $this->config->rootPath . 'composer.phar';

        if (is_file($composerLocal)) {
            // @codeCoverageIgnoreStart
            return sprintf(
                '%s %s',
                escapeshellarg($phpBinary),
                escapeshellarg($composerLocal)
            );
            // @codeCoverageIgnoreEnd
        }

        if (null === (new ExecutableFinder())->find('composer')) {
            // @codeCoverageIgnoreStart
            throw new RevisionException(lang('Revision.incompatibleUpgraderHandler', [static::class, 'No composer executable found.']));
            // @codeCoverageIgnoreEnd
        }

        return 'composer';
    }

    /**
     * Appends additional options to a command string.
     *
     * @param string   $command
     * @param string[] $options
     *
     * @return string
     */
    private function applyCommandOptions(string $command, array $options): string
    {
        if (\in_array('no-ansi', $options, true)) {
            $command = str_replace('--ansi', '--no-ansi', $command);
        }

        if (\in_array('dry-run', $options, true)) {
            $command .= ' --dry-run';
        }

        if (ENVIRONMENT === 'testing' && false === mb_strpos($command, '--quiet')) {
            $command .= ' --quiet';
        }

        if (\in_array('no-dev', $options, true)) {
            $command .= ' --no-dev'; // @codeCoverageIgnore
        }

        return $command;
    }

    /**
     * Runs the command in a subprocess.
     *
     * @param string $command
     * @param string $cwd
     *
     * @throws \Liaison\Revision\Exception\RevisionException
     *
     * @return int
     */
    private function runProcess(string $command, string $cwd): int
    {
        $process = Process::fromShellCommandline($command, $cwd, null, null, null);

        try {
            $process->mustRun(static function ($type, $line) {
                CLI::print($line); // @codeCoverageIgnore
            });

            return EXIT_SUCCESS;
        } catch (Throwable $e) {
            throw new RevisionException($e->getMessage());
        }
    }
}
