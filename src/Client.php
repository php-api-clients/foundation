<?php declare(strict_types=1);

namespace ApiClients\Foundation;

use ApiClients\Foundation\Hydrator\CommandBus\Command\ExtractFQCNCommand;
use ApiClients\Foundation\Hydrator\CommandBus\Command\HydrateFQCNCommand;
use ApiClients\Foundation\Resource\ResourceInterface;
use ApiClients\Tools\CommandBus\CommandBusInterface;
use InvalidArgumentException;
use Psr\Container\ContainerInterface;
use React\Promise\CancellablePromiseInterface;
use function React\Promise\resolve;

final class Client implements ClientInterface
{
    /**
     * @var CommandBusInterface
     */
    private $commandBus;

    /**
     * @param CommandBusInterface $commandBus
     */
    public function __construct(CommandBusInterface $commandBus)
    {
        $this->commandBus = $commandBus;
    }

    /**
     * @param $command
     * @return CancellablePromiseInterface
     */
    public function handle($command): CancellablePromiseInterface
    {
        return $this->commandBus->handle($command);
    }

    public function hydrate(string $resource): CancellablePromiseInterface
    {
        $resource = json_decode($resource, true);
        if (!isset($resource['class'], $resource['properties'])) {
            throw new InvalidArgumentException();
        }

        if (!class_exists($resource['class'])) {
            throw new InvalidArgumentException();
        }

        $class = $resource['class'];
        $json = $resource['properties'];

        return $this->handle(new HydrateFQCNCommand($class, $json));
    }

    public function extract(ResourceInterface $resource): CancellablePromiseInterface
    {
        $class = get_class($resource);

        return $this->handle(
            new ExtractFQCNCommand($class, $resource)
        )->then(function ($json) use ($class) {
            return resolve(json_encode([
                'class' => $class,
                'properties' => $json,
            ]));
        });
    }
}
