<?php declare(strict_types=1);

namespace ApiClients\Tests\Foundation;

use ApiClients\Foundation\Client;
use ApiClients\Tools\TestUtilities\TestCase;
use League\Container\ContainerInterface;
use League\Tactician\Setup\QuickStart;

final class ClientTest extends TestCase
{
    public function testClient()
    {
        $command = new class() {};
        $handler = new class() {
            public function handle($command)
            {
                return $command;
            }
        };

        $container = $this->prophesize(ContainerInterface::class)->reveal();
        $commandBus = QuickStart::create([
            get_class($command) => $handler,
        ]);
        $client = new Client($container, $commandBus);

        $this->assertSame($container, $client->getContainer());
        $this->assertSame($command, $client->handle($command));
    }
}
