<?php declare(strict_types=1);

namespace ApiClients\Tests\Foundation;

use ApiClients\Foundation\Client;
use ApiClients\Tools\TestUtilities\TestCase;
use InvalidArgumentException;
use League\Container\Container;
use League\Tactician\CommandBus;
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

        $commandBus = QuickStart::create([
            get_class($command) => $handler,
        ]);
        $container = new Container();
        $container->share(CommandBus::class, $commandBus);
        $client = new Client($container);

        $this->assertSame($container, $client->getContainer());
        $this->assertSame($command, $client->handle($command));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testMissingCommandBus()
    {
        new Client(new Container());
    }
}
