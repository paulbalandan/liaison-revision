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
 * JsonLogHandler.
 */
class JsonLogHandler extends BaseLogHandler
{
    /**
     * JSON array to serialize later.
     *
     * @var array
     */
    public $json = [];

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
        string $directory = 'json',
        string $filename = 'revision_',
        string $extension = '.json',
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
        // Headers
        $this->json = [
            'application' => 'Liaison Revision',
            'version'     => Application::VERSION,
            'run-date'    => date('D, d F Y, H:i:s') . ' UTC' . date('P'),
        ];

        // Settings
        $this->json['settings'] = [
            'Root Path'                 => $this->config->rootPath,
            'Write Path'                => $this->config->writePath,
            'Ignored Directories Count' => \count($this->config->ignoreDirs),
            'Ignored Files Count'       => \count($this->config->ignoreFiles),
            'Allow Gitignore Entry'     => $this->config->allowGitIgnoreEntry,
            'Fall Through to Project'   => $this->config->fallThroughToProject,
            'Consolidator'              => $this->config->consolidator,
            'Upgrader'                  => $this->config->upgrader,
            'Pathfinder'                => $this->config->pathfinder,
            'Diff Output Builder'       => $this->config->diffOutputBuilder,
            'Default Log Handlers'      => $this->config->defaultLogHandlers,
        ];

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(string $level, string $message): int
    {
        if (!isset($this->json['logs'])) {
            $this->json['logs'] = [];
        }

        $this->json['logs'][] = '[' . date('Y-m-d H:i:s') . '] ' . mb_strtoupper($level) . ' : ' . $message;

        return LogHandlerInterface::EXIT_SUCCESS;
    }

    /**
     * {@inheritdoc}
     */
    public function save()
    {
        $json       = $this->json;
        $this->json = [];

        $this->filesystem->dumpFile(
            $this->directory . $this->filename . $this->extension,
            json_encode($json, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n"
        );
    }
}
