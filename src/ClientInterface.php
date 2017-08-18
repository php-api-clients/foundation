<?php declare(strict_types=1);

namespace ApiClients\Foundation;

use ApiClients\Foundation\Resource\ResourceInterface;
use Psr\Container\ContainerInterface;
use React\Promise\CancellablePromiseInterface;

interface ClientInterface
{
    /**
     * @return ContainerInterface
     */
    public function getContainer(): ContainerInterface;

    /**
     * @return mixed
     */
    public function getFromContainer(string $id);

    /**
     * @param $command
     * @return CancellablePromiseInterface
     */
    public function handle($command): CancellablePromiseInterface;

    /**
     * @param  string                      $resource
     * @return CancellablePromiseInterface
     */
    public function hydrate(string $resource): CancellablePromiseInterface;

    /**
     * @param  ResourceInterface           $resource
     * @return CancellablePromiseInterface
     */
    public function extract(ResourceInterface $resource): CancellablePromiseInterface;
}
