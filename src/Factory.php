<?php declare(strict_types=1);

namespace ApiClients\Foundation;

use Acclimate\Container\CompositeContainer;
use ApiClients\Foundation\Hydrator\Factory as HydratorFactory;
use ApiClients\Foundation\Hydrator\Hydrator;
use ApiClients\Foundation\Middleware\Locator\ContainerLocator;
use ApiClients\Foundation\Middleware\Locator\Locator;
use ApiClients\Foundation\Transport\ClientInterface as TransportClientInterface;
use ApiClients\Foundation\Transport\Factory as TransportFactory;
use ApiClients\Tools\CommandBus\CommandBusInterface;
use ApiClients\Tools\CommandBus\Factory as CommandBusFactory;
use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;
use InvalidArgumentException;
use React\EventLoop\LoopInterface;

final class Factory
{
    public static function create(
        LoopInterface $loop,
        array $options = []
    ): Client {
        return new Client(
            self::createContainer($loop, $options)
        );
    }
    private static function createContainer(
        LoopInterface $loop,
        array $options
    ): ContainerInterface {
        $builder = new ContainerBuilder();

        $builder->addDefinitions([
            LoopInterface::class => $loop,
            Locator::class => function (ContainerInterface $container) {
                return new ContainerLocator($container);
            },
            TransportClientInterface::class => function (
                Locator $locator,
                LoopInterface $loop
            ) use ($options) {
                return self::createTransport($locator, $loop, $options);
            },
            Hydrator::class => function (LoopInterface $loop, CommandBusInterface $commandBus) use ($options) {
                return self::createHydrator($loop, $commandBus, $options);
            },
            CommandBusInterface::class => function (ContainerInterface $container) {
                return CommandBusFactory::create($container);
            },
        ]);
        $builder->addDefinitions($options[Options::CONTAINER_DEFINITIONS] ?? []);

        return $builder->build();
    }

    private static function createTransport(
        Locator $locator,
        LoopInterface $loop,
        array $options = []
    ): TransportClientInterface {
        if (!isset($options[Options::TRANSPORT_OPTIONS])) {
            throw new InvalidArgumentException('Missing Transport options');
        }

        return TransportFactory::create($locator, $loop, $options[Options::TRANSPORT_OPTIONS]);
    }

    private static function createHydrator(LoopInterface $loop, CommandBusInterface $commandBus, array $options = [])
    {
        if (!isset($options[Options::HYDRATOR_OPTIONS])) {
            throw new InvalidArgumentException('Missing Hydrator options');
        }

        return HydratorFactory::create($loop, $commandBus, $options[Options::HYDRATOR_OPTIONS]);
    }
}
