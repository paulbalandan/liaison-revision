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
     * Instance of ConfigurationResolver
     *
     * @var \Liaison\Revision\Config\ConfigurationResolver
     */
    private $config;

    /**
     * Constructor.
     *
     * @param null|\Liaison\Revision\Config\ConfigurationResolver $config
     */
    public function __construct(?ConfigurationResolver $config = null)
    {
        $this->config = $config ?? new ConfigurationResolver();
        $this->registerLogHandlers();
    }

    /**
     * Passes the message to the handlers for proper handling.
     *
     * @param string[] $messages
     * @param string   $level
     */
    public function logMessage(array $messages, string $level = 'info')
    {
        foreach ($messages as $message) {
            foreach ($this->logHandlers as $logHandler) {
                $return = $logHandler->handle($level, $message);

                if (\in_array($return, [LogHandlerInterface::EXIT_ERROR, LogHandlerInterface::EXIT_CONTINUE], true)) {
                    if (LogHandlerInterface::EXIT_CONTINUE === $return) {
                        // The handler has encountered a problem but wishes to continue.
                        continue;
                    }

                    break;
                }
            }

            log_message($level, $message);
        }
    }

    /**
     * Makes the handlers save their logs.
     */
    public function save()
    {
        foreach ($this->logHandlers as $logHandler) {
            try {
                $logHandler->save();
            } catch (IOExceptionInterface $e) {
                log_message('error', $e->getMessage());
            }
        }
    }

    /**
     * Loads the defined log handlers in the config.
     *
     * @throws \Liaison\Revision\Exception\InvalidArgumentException
     */
    private function registerLogHandlers()
    {
        /** @var string $handler */
        foreach ($this->config->defaultLogHandlers as $handler) {
            $logHandler = new $handler();

            if (!$logHandler instanceof BaseLogHandler) {
                throw new InvalidArgumentException(lang('Revision.invalidLogHandler', [
                    $handler,
                    'Liaison\\Revision\\Logs\\BaseLogHandler',
                    \get_class($logHandler),
                ]));
            }

            $this->logHandlers[] = $logHandler;
        }
    }
}
