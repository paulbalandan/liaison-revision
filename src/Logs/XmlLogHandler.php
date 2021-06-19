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
 * Writes logs into XML files.
 */
final class XmlLogHandler extends AbstractLogHandler
{
    /**
     * The DOMDocument object.
     *
     * @var null|\DOMDocument
     */
    public $dom;

    /**
     * The root XML node.
     *
     * @var \DOMElement
     */
    private $root;

    /**
     * The logs XML node.
     *
     * @var null|\DOMElement
     */
    private $logs;

    /**
     * Constructor.
     */
    public function __construct(
        ?Revision $config = null,
        ?Filesystem $filesystem = null,
        string $directory = 'xml',
        string $filename = 'revision_',
        string $extension = '.xml'
    ) {
        if (! \extension_loaded('dom')) {
            throw new RevisionException(lang('Revision.cannotUseLogHandler', [self::class, 'ext-dom'])); // @codeCoverageIgnore
        }

        helper('inflector');

        $config = $config ?? config('Revision');
        $filesystem = $filesystem ?? new Filesystem();
        parent::__construct($config, $filesystem, $directory, $filename, $extension);
    }

    /**
     * {@inheritDoc}
     */
    public function initialize()
    {
        // Initialize the xml document
        $this->dom = new \DOMDocument('1.0', 'UTF-8');
        $this->root = $this->dom->createElement('revision');
        $this->dom->appendChild($this->root);

        // Create the application node
        $appXML = $this->dom->createElement('application');
        $appXML->appendChild($this->dom->createElement('name', Application::NAME));
        $appXML->appendChild($this->dom->createElement('version', Application::VERSION));
        $appXML->appendChild($this->dom->createElement('run_date', date('D, d F Y, H:i:s') . ' UTC' . date('P')));
        $this->root->appendChild($appXML);

        //Create the configuration node
        $this->root->appendChild($this->createConfigurationNode());

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function handle(string $level, string $message): int
    {
        if (! $this->logs instanceof \DOMElement) {
            $this->logs = $this->dom->createElement('logs');
            $this->root->appendChild($this->logs);
        }

        $log = $this->dom->createElement('log', $message);
        $log->setAttribute('date', date('Y-m-d-H.i.s'));
        $log->setAttribute('level', $level);
        $this->logs->appendChild($log);

        return LogHandlerInterface::EXIT_SUCCESS;
    }

    /**
     * {@inheritDoc}
     */
    public function save()
    {
        $dom = $this->dom;
        $this->dom = null;

        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;

        $this->filesystem->dumpFile(
            $this->directory . $this->filename . $this->extension,
            $dom->saveXML(),
        );
    }

    /**
     * Creates a XML node for the configuration settings.
     */
    protected function createConfigurationNode(): \DOMElement
    {
        $settings = [
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
            lang('Revision.logHandlersCount') => $this->config->logHandlers,
        ];

        $inflect = static function ($value): string {
            if (\is_bool($value)) {
                return $value ? lang('Revision.accessAllowed') : lang('Revision.accessDenied');
            }

            return (string) $value;
        };

        $config = $this->dom->createElement('settings');

        foreach ($settings as $key => $value) {
            $key = strtolower(underscore($key));

            if (\is_array($value)) {
                $setting = $this->dom->createElement($key);

                foreach ($value as $val) {
                    $subnode = $this->dom->createElement('name', $inflect($val));
                    $setting->appendChild($subnode);
                }
            } else {
                $setting = $this->dom->createElement($key, $inflect($value));
            }

            $config->appendChild($setting);
        }

        unset($inflect);

        return $config;
    }
}
