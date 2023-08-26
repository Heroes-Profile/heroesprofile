<?php

namespace App\Logging;

use Google\Cloud\Logging\LoggingClient;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\PsrHandler;
use Monolog\Logger;

class CreateStackdriverLogger
{
    public function __invoke(array $config)
    {
        $logger = new Logger('stackdriver');
        $formatter = new LineFormatter(null, null, true, true);
        $handler = new PsrHandler(LoggingClient::psrBatchLogger($config['projectId'], $config['logName']));
        $handler->setFormatter($formatter);
        $logger->pushHandler($handler);

        return $logger;
    }
}
