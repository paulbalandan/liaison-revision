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
     * @param null|\Liaison\Revision\Config\ConfigurationResolver $config
     * @param null|\Symfony\Component\Filesystem\Filesystem       $filesystem
     * @param string                                              $directory
     * @param string                                              $filename
     * @param string                                              $extension
     */
    public function __construct(
        ?ConfigurationResolver $config = null,
        ?Filesystem $filesystem = null,
        string $directory = 'log',
        string $filename = 'revision_',
        string $extension = '.log'
    ) {
        $config     = $config     ?? new ConfigurationResolver();
        $filesystem = $filesystem ?? new Filesystem();
        parent::__construct($config, $filesystem, $directory, $filename, $extension);
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
        $dirs     = \count($this->config->ignoreDirs);
        $files    = \count($this->config->ignoreFiles);
        $allow    = $this->config->allowGitIgnoreEntry ? 'Yes' : 'No';
        $fall     = $this->config->fallThroughToProject ? 'Yes' : 'No';
        $logs     = implode(', ', $this->config->defaultLogHandlers);
        $settings = <<<EOD
Root Path: {$this->config->rootPath}
Write Path: {$this->config->writePath}
Ignored Directories Count: {$dirs}
Ignored Files Count: {$files}
Allow Gitignore Entry: {$allow}
Fall Through to Project: {$fall}
Consolidator: {$this->config->consolidator}
Upgrader: {$this->config->upgrader}
Pathfinder: {$this->config->pathfinder},
Diff Output Builder: {$this->config->diffOutputBuilder},
Default Log Handlers: {$logs},
\n
EOD;

        $this->buffer .= "\nLoaded Configuration\n";
        $this->buffer .= str_repeat('=', 20) . "\n";
        $this->buffer .= $settings;

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
