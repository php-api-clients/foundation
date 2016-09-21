<?php declare(strict_types=1);

namespace ApiClients\Tests\Foundation\Transport\CommandBus\Command;

use ApiClients\Foundation\Transport\CommandBus\Command\JsonDecodeCommand;
use ApiClients\Tests\Foundation\TestCase;

class JsonDecodeCommandTest extends TestCase
{
    public function testCommand()
    {
        $json = '[]';
        $command = new JsonDecodeCommand($json);
        $this->assertSame($json, $command->getJson());
    }
}
