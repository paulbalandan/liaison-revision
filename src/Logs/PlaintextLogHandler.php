<?php

/**
 * This file is part of Liaison Revision.
 *
 * (c) John Paul E. Balandan, CPA <paulbalandan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Liaison\Revision\Logs;

use Liaison\Revision\Application;
use Liaison\Revision\Config\ConfigurationResolver;
use Symfony\Component\Filesystem\Filesystem;

/**
 * PlaintextLogHandler.
 */
class PlaintextLogHandler extends BaseLogHandler
{
    /**
     * Buffer to write to log file.
     *
     * @var string
     */
    public $buffer = '';

    /**
     * Constructor.
     *
     * @param string                                              $directory
     * @param string                                              $filename
     * @param string                                              $extension
     * @param null|\Liaison\Revision\Config\ConfigurationResolver $config
     * @param null|\Symfony\Component\Filesystem\Filesystem       $filesystem
     */
    public function __construct(
        string $directory = 'log',
        string $filename = 'revision_',
        string $extension = '.log',
        ?ConfigurationResolver $config = null,
        ?Filesystem $filesystem = null
    ) {
        parent::__construct($directory, $filename, $extension, $config, $filesystem);
        $this->initialize();
    }

    /**
     * {@inheritdoc}
     */
    public function initialize()
    {
        $version = str_pad(Application::VERSION, 45);
        $date    = str_pad(sprintf('%s UTC%s', date('D, d F Y, H:i:s'), date('P')), 44);

        // Headers
        $this->buffer = <<<EOD
+========================================================+
| Liaison Revision                                       |
| Version: {$version} |
| Run Date: {$date} |
+========================================================+

EOD;

        // Settings
        $this->buffer .= "Loaded Configuration\n";
        $this->buffer .= str_repeat('=', 20) . "\n";

        $dirs  = \count($this->config->ignoreDirs);
        $files = \count($this->config->ignoreFiles);
        $allow = $this->config->allowGitIgnoreEntry ? 'true' : 'false';
        $logs  = implode(', ', $this->config->defaultLogHandlers);

        $this->buffer .= <<<EOD
Root Path: {$this->config->rootPath}
Write Path: {$this->config->writePath}
Ignored Directories Count: {$dirs}
Ignored Files Count: {$files}
Allow Gitignore Entry: {$allow}
Consolidator: {$this->config->consolidator}
Upgrader: {$this->config->upgrader}
Pathfinder: {$this->config->pathfinder},
Diff Output Builder: {$this->config->diffOutputBuilder},
Default Log Handlers: {$logs},

EOD;

        // Add final new line
        $this->buffer .= "\n";

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(string $level, string $message): int
    {
        $this->buffer .= '[' . date('Y-m-d H:i:s') . '] ' . mb_strtoupper($level) . ' -- ' . $message . "\n";

        return LogHandlerInterface::EXIT_SUCCESS;
    }

    /**
     * {@inheritdoc}
     */
    public function save()
    {
        $buffer       = $this->buffer;
        $this->buffer = '';

        $this->filesystem->dumpFile(
            $this->directory . $this->filename . $this->extension,
            $buffer
        );
    }
}
