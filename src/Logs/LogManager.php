<?php

/**
 * This file is part of Liaison Revision.
 *
 * (c) 2020 John Paul E. Balandan, CPA <paulbalandan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Liaison\Revision\Logs;

use Liaison\Revision\Config\Revision;
use Liaison\Revision\Exception\InvalidArgumentException;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;

/**
 * @internal
 */
final class LogManager
{
    /**
     * Instances of Log handlers to use.
     *
     * @var LogHandlerInterface[]
     */
    private $logHandlers = [];

    /**
     * Default Log Handlers to use.
     *
     * @var string[]
     */
    private static $defaultLogHandlers = [
        'Liaison\Revision\Logs\JsonLogHandler',
        'Liaison\Revision\Logs\PlaintextLogHandler',
    ];

    /**
     * Instance of Revision configuration.
     *
     * @var \Liaison\Revision\Config\Revision
     */
    private $config;

    /**
     * Constructor.
     *
     * @param null|\Liaison\Revision\Config\Revision $config
     */
    public function __construct(?Revision $config = null)
    {
        $this->config = $config ?? config('Revision');
        $this->registerLogHandlers();
    }

    /**
     * Passes the message to the handlers for proper handling.
     *
     * @param string|string[] $messages
     * @param string          $level
     *
     * @return void
     */
    public function logMessage($messages, string $level = 'info')
    {
        foreach ((array) $messages as $message) {
            foreach ($this->logHandlers as $logHandler) {
                if (LogHandlerInterface::EXIT_ERROR === $logHandler->handle($level, $message)) {
                    break;
                }
            }

            if (ENVIRONMENT !== 'testing') {
                log_message($level, $message); // @codeCoverageIgnore
            }
        }
    }

    /**
     * Makes the handlers save their logs.
     *
     * @return void
     */
    public function save()
    {
        foreach ($this->logHandlers as $logHandler) {
            try {
                $logHandler->save();
            } catch (IOExceptionInterface $e) {
                if (ENVIRONMENT !== 'testing') {
                    log_message('error', $e->getMessage()); // @codeCoverageIgnore
                }
            }
        }
    }

    /**
     * Loads the defined log handlers in the config.
     *
     * @throws \Liaison\Revision\Exception\InvalidArgumentException
     *
     * @return void
     */
    private function registerLogHandlers()
    {
        /** @var string[] $handlers */
        $handlers = array_merge(self::$defaultLogHandlers, $this->config->logHandlers);

        // reassign so that this can be displayed
        $this->config->logHandlers = $handlers;

        foreach ($handlers as $handler) {
            $logHandler = new $handler($this->config);

            if (!$logHandler instanceof BaseLogHandler) {
                throw new InvalidArgumentException(lang('Revision.invalidLogHandler', [
                    $handler,
                    'Liaison\Revision\Logs\BaseLogHandler',
                    \get_class($logHandler),
                ]));
            }

            $this->logHandlers[] = $logHandler;
        }
    }
}
