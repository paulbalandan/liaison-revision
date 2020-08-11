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

use JsonSerializable;
use Liaison\Revision\Config\ConfigurationResolver;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * JsonLogHandler.
 */
class JsonLogHandler extends BaseLogHandler implements JsonSerializable
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
    public function jsonSerialize()
    {
        return $this->json;
    }

    /**
     * {@inheritdoc}
     */
    public function initialize(): LogHandlerInterface
    {
        // Headers
        $this->json = [
            'application' => 'Liaison Revision',
            'version'     => '', // change later
            'run-date'    => date('l, d F Y, H:i:s') . ' UTC' . date('P'),
        ];

        // Settings
        $this->json['settings'] = [];
        foreach (get_object_vars($this->config) as $key => $value) {
            $this->json['settings'][$key] = $value;
        }

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

        $this->json['logs'][] = '[' . date('Y-m-d H:i:s') . '] ' . mb_strtoupper($level) . ' -- ' . $message;

        return static::EXIT_SUCCESS;
    }

    /**
     * {@inheritdoc}
     */
    public function save(): bool
    {
        try {
            $this->fs->dumpFile(
                $this->directory . \DIRECTORY_SEPARATOR . $this->filename . $this->extension,
                json_encode($this, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n"
            );

            return true;
        } catch (IOExceptionInterface $e) {
            return false;
        }
    }
}
