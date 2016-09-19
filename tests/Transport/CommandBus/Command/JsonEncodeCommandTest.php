<?php declare(strict_types=1);

namespace ApiClients\Tests\Foundation\Transport\CommandBus\Command;

use ApiClients\Foundation\Transport\CommandBus\Command\JsonEncodeCommand;
use ApiClients\Tests\Foundation\TestCase;

class JsonEncodeCommandTest extends TestCase
{
    public function testCommand()
    {
        $json = [];
        $command = new JsonEncodeCommand($json);
        $this->assertSame($json, $command->getJson());
    }
}
