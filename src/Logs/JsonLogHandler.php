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
use Liaison\Revision\Exception\RevisionException;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Logs into JSON files.
 */
final class JsonLogHandler extends AbstractLogHandler
{
    /**
     * JSON array to serialize later.
     *
     * @var array<string, mixed>
     */
    public $json = [];

    /**
     * Constructor.
     */
    public function __construct(
        ?Revision $config = null,
        ?Filesystem $filesystem = null,
        string $directory = 'json',
        string $filename = 'revision_',
        string $extension = '.json'
    ) {
        if (! \extension_loaded('json')) {
            throw new RevisionException(lang('Revision.cannotUseLogHandler', [self::class, 'ext-json'])); // @codeCoverageIgnore
        }

        $config = $config ?? config('Revision');
        $filesystem = $filesystem ?? new Filesystem();
        parent::__construct($config, $filesystem, $directory, $filename, $extension);
    }

    /**
     * {@inheritDoc}
     */
    public function initialize()
    {
        // Headers
        $this->json['application'] = [
            'name' => Application::NAME,
            'version' => Application::VERSION,
            'run-date' => date('D, d F Y, H:i:s') . ' UTC' . date('P'),
        ];

        // Settings
        $this->json['settings'] = [
            lang('Revision.configurationClassLabel') => \get_class($this->config),
            lang('Revision.rootPathLabel') => $this->config->rootPath,
            lang('Revision.writePathLabel') => $this->config->writePath,
            lang('Revision.ignoredDir') => $this->config->ignoreDirs,
            lang('Revision.ignoredFile') => $this->config->ignoreFiles,
            lang('Revision.allowGitignoreLabel') => $this->config->allowGitIgnoreEntry,
            lang('Revision.fallThroughToProjectLabel') => $this->config->fallThroughToProject,
            lang('Revision.maximumRetriesLabel') => $this->config->retries,
            lang('Revision.consolidatorLabel') => $this->config->consolidator,
            lang('Revision.upgraderLabel') => $this->config->upgrader,
            lang('Revision.pathfinderLabel') => $this->config->pathfinder,
            lang('Revision.diffOutputBuilderLabel') => $this->config->diffOutputBuilder,
            lang('Revision.logHandlers') => $this->config->logHandlers,
        ];

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function handle(string $level, string $message): int
    {
        if (! isset($this->json['logs'])) {
            $this->json['logs'] = [];
        }

        $this->json['logs'][] = '[' . date('Y-m-d H:i:s') . '] ' . $level . ': ' . $message;

        return LogHandlerInterface::EXIT_SUCCESS;
    }

    /**
     * {@inheritDoc}
     */
    public function save()
    {
        $json = $this->json;
        $this->json = [];

        $this->filesystem->dumpFile(
            $this->directory . $this->filename . $this->extension,
            json_encode($json, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n",
        );
    }
}
