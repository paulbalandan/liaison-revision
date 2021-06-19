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

namespace Liaison\Revision\Logs;

use Liaison\Revision\Application;
use Liaison\Revision\Config\Revision;
use Symfony\Component\Filesystem\Filesystem;

/**
 * PlaintextLogHandler.
 */
final class PlaintextLogHandler extends AbstractLogHandler
{
    /**
     * Buffer to write to log file.
     *
     * @var string
     */
    public $buffer = '';

    /**
     * Constructor.
     */
    public function __construct(
        ?Revision $config = null,
        ?Filesystem $filesystem = null,
        string $directory = 'log',
        string $filename = 'revision_',
        string $extension = '.log'
    ) {
        $config = $config ?? config('Revision');
        $filesystem = $filesystem ?? new Filesystem();
        parent::__construct($config, $filesystem, $directory, $filename, $extension);
    }

    /**
     * {@inheritDoc}
     */
    public function initialize()
    {
        $name = Application::NAME;
        $version = str_pad(Application::VERSION, 45);
        $date = str_pad(sprintf('%s UTC%s', date('D, d F Y, H:i:s'), date('P')), 44);

        // Headers
        $buffer = <<<EOD
            +========================================================+
            | {$name}                                       |
            | Version: {$version} |
            | Run Date: {$date} |
            +========================================================+

            EOD;

        // Settings
        $config = \get_class($this->config);
        $dirs = \count($this->config->ignoreDirs);
        $files = \count($this->config->ignoreFiles);
        $allow = $this->config->allowGitIgnoreEntry ? lang('Revision.accessAllowed') : lang('Revision.accessDenied');
        $fall = $this->config->fallThroughToProject ? lang('Revision.accessAllowed') : lang('Revision.accessDenied');
        $logs = \count($this->config->logHandlers);

        // Labels
        $loadedConfig = lang('Revision.loadedConfigLabel');
        $configLabel = lang('Revision.configurationClassLabel');
        $rootLabel = lang('Revision.rootPathLabel');
        $writeLabel = lang('Revision.writePathLabel');
        $dirsLabel = lang('Revision.ignoredDirCount');
        $filesLabel = lang('Revision.ignoredFileCount');
        $allowLabel = lang('Revision.allowGitignoreLabel');
        $fallLabel = lang('Revision.fallThroughToProjectLabel');
        $retriesLabel = lang('Revision.maximumRetriesLabel');
        $consolidator = lang('Revision.consolidatorLabel');
        $upgrader = lang('Revision.upgraderLabel');
        $pathfinder = lang('Revision.pathfinderLabel');
        $diffLabel = lang('Revision.diffOutputBuilderLabel');
        $logsLabel = lang('Revision.logHandlersCount');

        $settings = <<<EOD
            {$configLabel}: {$config}
            {$rootLabel}: {$this->config->rootPath}
            {$writeLabel}: {$this->config->writePath}
            {$dirsLabel}: {$dirs}
            {$filesLabel}: {$files}
            {$allowLabel}: {$allow}
            {$fallLabel}: {$fall}
            {$retriesLabel}: {$this->config->retries}
            {$consolidator}: {$this->config->consolidator}
            {$upgrader}: {$this->config->upgrader}
            {$pathfinder}: {$this->config->pathfinder}
            {$diffLabel}: {$this->config->diffOutputBuilder}
            {$logsLabel}: {$logs}
            \n
            EOD;

        $buffer .= "\n{$loadedConfig}\n";
        $buffer .= str_repeat('=', \strlen($loadedConfig)) . "\n";
        $buffer .= $settings;

        $this->buffer = $buffer;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function handle(string $level, string $message): int
    {
        $this->buffer .= '[' . date('Y-m-d H:i:s') . '] ' . $level . ': ' . $message . "\n";

        return LogHandlerInterface::EXIT_SUCCESS;
    }

    /**
     * {@inheritDoc}
     */
    public function save()
    {
        $buffer = $this->buffer;
        $this->buffer = '';

        $this->filesystem->dumpFile(
            $this->directory . $this->filename . $this->extension,
            $buffer,
        );
    }
}
