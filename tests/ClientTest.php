<?php declare(strict_types=1);

namespace ApiClients\Tests\Foundation;

use ApiClients\Foundation\Client;
use ApiClients\Foundation\Hydrator\CommandBus\Command\ExtractFQCNCommand;
use ApiClients\Foundation\Hydrator\CommandBus\Command\HydrateFQCNCommand;
use ApiClients\Foundation\Resource\ResourceInterface;
use ApiClients\Tools\CommandBus\CommandBus;
use ApiClients\Tools\CommandBus\CommandBusInterface;
use ApiClients\Tools\TestUtilities\TestCase;
use DI\ContainerBuilder;
use League\Tactician\Handler\CommandHandlerMiddleware;
use League\Tactician\Handler\CommandNameExtractor\ClassNameExtractor;
use League\Tactician\Handler\Locator\InMemoryLocator;
use League\Tactician\Handler\MethodNameInflector\HandleInflector;
use React\EventLoop\Factory;
use function Clue\React\Block\await;
use function React\Promise\resolve;

final class ClientTest extends TestCase
{
    public function testClient()
    {
        $command = new class() {
        };
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
        $container->set(CommandBusInterface::class, $commandBus);
        $client = new Client($container);

        $this->assertSame($container, $client->getContainer());
        $this->assertSame($commandBus, $client->getFromContainer(CommandBusInterface::class));
        $this->assertSame($command, await($client->handle($command), $loop));
    }

    public function testCommandBusMissing()
    {
        try {
            new Client(ContainerBuilder::buildDevContainer());
        } catch (\DI\Definition\Exception\InvalidDefinition $exception) {
            self::assertTrue(true);

            return;
        } catch (\DI\Definition\Exception\DefinitionException $exception) {
            self::assertTrue(true);

            return;
        }

        self::assertTrue(false);
    }

    public function testHydrate()
    {
        $resource = $this->prophesize(ResourceInterface::class)->reveal();

        $commandBus = $this->prophesize(CommandBusInterface::class);
        $commandBus->handle(new HydrateFQCNCommand('stdClass', []))->shouldBeCalled()->willReturn(resolve($resource));

        $container = ContainerBuilder::buildDevContainer();
        $container->set(CommandBusInterface::class, $commandBus->reveal());
        $client = new Client($container);

        $json = json_encode([
            'class' => 'stdClass',
            'properties' => [],
        ]);

        self::assertSame($resource, await($client->hydrate($json), Factory::create()));
    }

    public function testExtract()
    {
        $resource = $this->prophesize(ResourceInterface::class)->reveal();

        $json = json_encode([
            'class' => get_class($resource),
            'properties' => [],
        ]);

        $commandBus = $this->prophesize(CommandBusInterface::class);
        $commandBus->handle(
            new ExtractFQCNCommand(get_class($resource), $resource)
        )->shouldBeCalled()->willReturn(resolve([]));

        $container = ContainerBuilder::buildDevContainer();
        $container->set(CommandBusInterface::class, $commandBus->reveal());
        $client = new Client($container);

        self::assertSame($json, await($client->extract($resource), Factory::create()));
    }
}
