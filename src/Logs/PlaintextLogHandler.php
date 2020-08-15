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

use Liaison\Revision\Config\ConfigurationResolver;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
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
        $version = str_pad('', 45); // change later
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
        $config = get_object_vars($this->config);
        $maxKey = max(array_map('strlen', array_keys($config)));
        $maxVal = max(array_map([$this, 'stringify'], array_values($config)));
        $this->buffer .= str_repeat('=', $maxKey + $maxVal + 3) . "\n";

        foreach ($config as $key => $value) {
            $this->buffer .= str_pad($key, $maxKey) . ' : ' . $this->stringify($value) . "\n";
        }

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

        return static::EXIT_SUCCESS;
    }

    /**
     * {@inheritdoc}
     */
    public function save(): bool
    {
        try {
            $buffer       = $this->buffer;
            $this->buffer = '';

            $this->filesystem->dumpFile(
                $this->directory . \DIRECTORY_SEPARATOR . $this->filename . $this->extension,
                $buffer
            );

            return true;
        } catch (IOExceptionInterface $e) {
            log_message('error', $e->getMessage());

            return false;
        }
    }
}
