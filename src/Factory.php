<?php declare(strict_types=1);

namespace ApiClients\Foundation;

use ApiClients\Foundation\Events\CommandLocatorEvent;
use ApiClients\Foundation\Hydrator\Factory as HydratorFactory;
use ApiClients\Foundation\Hydrator\Hydrator;
use ApiClients\Foundation\Transport\Client as TransportClient;
use ApiClients\Foundation\Transport\Factory as TransportFactory;
use Interop\Container\ContainerInterface;
use League\Container\Container;
use League\Container\ReflectionContainer;
use League\Event\Emitter;
use League\Event\EmitterInterface;
use League\Tactician\CommandBus;
use League\Tactician\Container\ContainerLocator;
use League\Tactician\Handler\CommandHandlerMiddleware;
use League\Tactician\Handler\CommandNameExtractor\ClassNameExtractor;
use League\Tactician\Handler\MethodNameInflector\HandleInflector;
use React\EventLoop\LoopInterface;

final class Factory
{
    public static function create(
        LoopInterface $loop = null,
        ContainerInterface $wrappedContainer = null,
        array $options = []
    ): Client {
        $container = self::createContainer($wrappedContainer);

        $container->share(EmitterInterface::class, new Emitter());
        $container->share(TransportClient::class, self::createTransport($container, $loop, $options));
        $container->share(Hydrator::class, self::createHydrator($container, $options));
        $container->share(CommandBus::class, function () use ($container) {
            return self::createCommandBus($container);
        });

        return new Client(
            $container
        );
    }

    private static function createContainer(ContainerInterface $wrappedContainer = null): Container
    {
        $container = new Container();
        $container->delegate(new ReflectionContainer());

        if ($wrappedContainer instanceof ContainerInterface) {
            $container->delegate($wrappedContainer);
        }

        return $container;
    }

    private static function createCommandBus(ContainerInterface $container): CommandBus
    {
        $commandToHandlerMap = self::mapCommandsToHandlers($container->get(EmitterInterface::class));

        $containerLocator = new ContainerLocator(
            $container,
            $commandToHandlerMap
        );

        $commandHandlerMiddleware = new CommandHandlerMiddleware(
            new ClassNameExtractor(),
            $containerLocator,
            new HandleInflector()
        );

        return new CommandBus([
            $commandHandlerMiddleware,
        ]);
    }

    private static function mapCommandsToHandlers(Emitter $emitter): array
    {
        return $emitter->emit(CommandLocatorEvent::create())->getMap();
    }

    private static function createTransport(
        ContainerInterface $container,
        LoopInterface $loop = null,
        array $options = []
    ): TransportClient {
        return TransportFactory::create($container, $loop, $options);
    }

    private static function createHydrator(ContainerInterface $container, array $options = [])
    {
        if (isset($options[Options::HYDRATOR]) && $options[Options::HYDRATOR] instanceof Hydrator) {
            return $options[Options::HYDRATOR];
        }

        if (!isset($options[Options::HYDRATOR_OPTIONS])) {
            throw new \Exception('Missing Hydrator options');
        }

        return HydratorFactory::create($container, $options[Options::HYDRATOR_OPTIONS]);
    }
}
