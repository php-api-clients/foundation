<?php declare(strict_types=1);

namespace ApiClients\Tests\Foundation;

use ApiClients\Foundation\Client;
use ApiClients\Tools\CommandBus\CommandBus;
use ApiClients\Tools\TestUtilities\TestCase;
use DI\ContainerBuilder;
use League\Tactician\Handler\CommandHandlerMiddleware;
use League\Tactician\Handler\CommandNameExtractor\ClassNameExtractor;
use League\Tactician\Handler\Locator\InMemoryLocator;
use League\Tactician\Handler\MethodNameInflector\HandleInflector;
use function Clue\React\Block\await;
use React\EventLoop\Factory;
use function React\Promise\resolve;

final class ClientTest extends TestCase
{
    public function testClient()
    {
        $command = new class() {};
        $handler = new class() {
            public function handle($command)
            {
                return resolve($command);
            }
        };

        $loop = Factory::create();

        $commandToHandlerMap = [
            get_class($command) => $handler,
        ];

        $handlerMiddleware = new CommandHandlerMiddleware(
            new ClassNameExtractor(),
            new InMemoryLocator($commandToHandlerMap),
            new HandleInflector()
        );

        $commandBus = new CommandBus($loop, $handlerMiddleware);

        $container = ContainerBuilder::buildDevContainer();
        $container->set(CommandBus::class, $commandBus);
        $client = new Client($container);

        $this->assertSame($container, $client->getContainer());
        $this->assertSame($command, await($client->handle($command), $loop));
    }
}
