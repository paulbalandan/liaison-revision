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

use Liaison\Revision\Config\Revision;
use Liaison\Revision\Exception\InvalidArgumentException;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;

/**
 * A generic implementation for a log manager.
 *
 * @internal
 */
final class LogManager implements LogManagerInterface
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
     */
    public function __construct(?Revision $config = null)
    {
        $this->config = $config ?? config('Revision');
        $this->registerLogHandlers();
    }

    /**
     * {@inheritDoc}
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
     * {@inheritDoc}
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

            if (! $logHandler instanceof AbstractLogHandler) {
                throw new InvalidArgumentException(lang('Revision.invalidLogHandler', [
                    $handler,
                    AbstractLogHandler::class,
                    \get_class($logHandler),
                ]));
            }

            $this->logHandlers[] = $logHandler;
        }
    }
}
