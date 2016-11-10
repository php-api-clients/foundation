<?php declare(strict_types=1);

namespace ApiClients\Foundation;

use ApiClients\Tools\CommandBus\CommandBus;
use Interop\Container\ContainerInterface;
use InvalidArgumentException;
use React\Promise\CancellablePromiseInterface;

final class Client
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        if (!$this->container->has(CommandBus::class)) {
            throw new InvalidArgumentException();
        }
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    public function handle($command): CancellablePromiseInterface
    {
        return $this->container->get(CommandBus::class)->handle($command);
    }
}
