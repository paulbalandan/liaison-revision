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

namespace Liaison\Revision\Upgrade;

use CodeIgniter\CLI\CLI;
use Liaison\Revision\Config\Revision;
use Liaison\Revision\Exception\RevisionException;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

/**
 * Upgrader for Composer-installed projects.
 */
final class ComposerUpgrader implements UpgraderInterface
{
    /**
     * Instance of Revision configuration.
     *
     * @var \Liaison\Revision\Config\Revision
     */
    private $config;

    /**
     * Constructor.
     */
    public function __construct(?Revision $config = null)
    {
        $this->config = $config ?? config('Revision');
    }

    /**
     * Installs the project.
     *
     * @param string[] $options
     *
     * @deprecated v1.0.4 This method is not part of the interface and Composer v2 uses `update` mainly.
     *
     * @codeCoverageIgnore
     */
    public function install(string $rootPath, array $options = []): int
    {
        return $this->upgrade($rootPath, $options);
    }

    /**
     * {@inheritDoc}
     */
    public function upgrade(string $rootPath, array $options = []): int
    {
        /**
         * Default behavior now is to exclude non-system directory.
         * Add `--prefer-source` to download these.
         *
         * @see https://github.com/codeigniter4/CodeIgniter4/pull/3438
         */
        $cmd = $this->findComposerPhar() . ' update --ansi --prefer-source --no-interaction';
        $cmd = $this->applyCommandOptions($cmd, $options);

        return $this->runProcess($cmd, $rootPath);
    }

    /**
     * Gets the path of the composer executable.
     *
     * @throws RevisionException
     */
    private function findComposerPhar(): string
    {
        $phpBinary = (string) (new PhpExecutableFinder())->find(false);
        $composerLocal = $this->config->rootPath . 'composer.phar';

        if (is_file($composerLocal)) {
            // @codeCoverageIgnoreStart
            return sprintf(
                '%s %s',
                escapeshellarg($phpBinary),
                escapeshellarg($composerLocal),
            );
            // @codeCoverageIgnoreEnd
        }

        if (null === (new ExecutableFinder())->find('composer')) {
            // @codeCoverageIgnoreStart
            throw new RevisionException(lang('Revision.incompatibleUpgraderHandler', [self::class, 'No composer executable found.']));
            // @codeCoverageIgnoreEnd
        }

        return 'composer';
    }

    /**
     * Appends additional options to a command string.
     *
     * @param string[] $options
     */
    private function applyCommandOptions(string $command, array $options): string
    {
        if (\in_array('no-ansi', $options, true)) {
            $command = str_replace('--ansi', '--no-ansi', $command);
        }

        if (\in_array('dry-run', $options, true)) {
            $command .= ' --dry-run';
        }

        if (ENVIRONMENT === 'testing' && false === strpos($command, '--quiet')) {
            $command .= ' --quiet';
        }

        if (\in_array('no-dev', $options, true)) {
            $command .= ' --no-dev'; // @codeCoverageIgnore
        }

        return $command . ' --no-scripts';
    }

    /**
     * Runs the command in a subprocess.
     *
     * @throws \Liaison\Revision\Exception\RevisionException
     */
    private function runProcess(string $command, string $cwd): int
    {
        $process = Process::fromShellCommandline($command, $cwd, null, null, null);

        try {
            $process->mustRun(static function ($type, $line): void {
                CLI::print($line); // @codeCoverageIgnore
            });

            return EXIT_SUCCESS;
        } catch (\Throwable $e) {
            throw new RevisionException($e->getMessage());
        }
    }
}
