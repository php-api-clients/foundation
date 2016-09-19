<?php declare(strict_types=1);

namespace ApiClients\Tests\Foundation\Transport\CommandBus\Handler;

use ApiClients\Foundation\Transport\CommandBus\Command\JsonEncodeCommand;
use ApiClients\Foundation\Transport\CommandBus\Handler\JsonEncodeHandler;
use ApiClients\Tests\Foundation\TestCase;
use React\EventLoop\Factory;
use function Clue\React\Block\await;

class JsonEncodeHandlerTest extends TestCase
{
    public function testHandler()
    {
        $loop = Factory::create();
        $command = new JsonEncodeCommand([]);
        $handler = new JsonEncodeHandler($loop);
        $this->assertSame('[]', await($handler->handle($command), $loop));
    }
}
