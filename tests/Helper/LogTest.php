<?php

namespace Test\Helper;

use Monolog\Handler\StreamHandler;
use Monolog\Handler\TestHandler;
use Neoan\Helper\Log;
use PHPUnit\Framework\TestCase;

class LogTest extends TestCase
{
    private Log $log;

    protected function setUp(): void
    {
        $this->log = (new Log())();
    }

    public function testSetFileLocation()
    {
        $this->log->setFileLocation('log.txt');
        $logger = $this->log->getMonologInstance();
        $handler = $logger->getHandlers()[0];
        $this->assertInstanceOf(StreamHandler::class, $handler);
        $handler->close();
    }


    public function testLogging()
    {
        $this->log->addHandler(new TestHandler());
        $this->log->info('test-logging-message');
        $handler = $this->log->getMonologInstance()->getHandlers()[0];
        $this->assertInstanceOf(TestHandler::class, $handler);
        $this->assertCount(1, $handler->getRecords());
        $this->assertStringContainsString('test-logging-message', $handler->getRecords()[0]['message']);
        $this->log->unknown('test-logging-message');
        $this->assertStringContainsString('Log-method not found: unknown', $handler->getRecords()[1]['message']);


    }
}
