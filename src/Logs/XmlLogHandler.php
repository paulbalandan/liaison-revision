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

use DOMDocument;
use DOMElement;
use Liaison\Revision\Application;
use Liaison\Revision\Config\ConfigurationResolver;
use Liaison\Revision\Exception\RevisionException;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Writes logs into XML files.
 */
class XmlLogHandler extends BaseLogHandler
{
    /**
     * The DOMDocument object
     *
     * @var null|DOMDocument
     */
    public $dom;

    /**
     * The root XML node.
     *
     * @var DOMElement
     */
    protected $root;

    /**
     * The logs XML node.
     *
     * @var null|DOMElement
     */
    protected $logs;

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
        string $directory = 'xml',
        string $filename = 'revision_',
        string $extension = '.xml'
    ) {
        if (!\extension_loaded('dom')) {
            throw new RevisionException(lang('Revision.cannotUseLogHandler', [static::class, 'ext-dom'])); // @codeCoverageIgnore
        }

        helper('inflector');

        $config     = $config     ?? new ConfigurationResolver();
        $filesystem = $filesystem ?? new Filesystem();
        parent::__construct($config, $filesystem, $directory, $filename, $extension);
    }

    /**
     * {@inheritdoc}
     */
    public function initialize()
    {
        // Initialize the xml document
        $this->dom  = new DOMDocument('1.0', 'UTF-8');
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
     * {@inheritdoc}
     */
    public function handle(string $level, string $message): int
    {
        if (!$this->logs instanceof DOMElement) {
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
     * {@inheritdoc}
     */
    public function save()
    {
        $dom       = $this->dom;
        $this->dom = null;

        $dom->preserveWhiteSpace = false;
        $dom->formatOutput       = true;

        $this->filesystem->dumpFile(
            $this->directory . $this->filename . $this->extension,
            $dom->saveXML()
        );
    }

    /**
     * Creates a XML node for the configuration settings.
     *
     * @return DOMElement
     */
    protected function createConfigurationNode(): DOMElement
    {
        $settings = [
            'Config class'              => \get_class($this->config->getConfig()),
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
            'Log Handlers'              => $this->config->logHandlers,
        ];

        $inflect = static function ($value) {
            if (\is_bool($value)) {
                return $value ? 'Allowed' : 'Denied';
            }

            return $value;
        };

        $config = $this->dom->createElement('settings');

        foreach ($settings as $key => $value) {
            $key = mb_strtolower(underscore($key));

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
