<?php
declare(strict_types=1);

namespace ApiClients\Tests\Foundation\Resource;

use React\Promise\PromiseInterface;
use Rx\ObservableInterface;
use ApiClients\Foundation\Annotations\Collection;
use ApiClients\Foundation\Annotations\Nested;
use ApiClients\Foundation\Resource\CallAsyncTrait;
use ApiClients\Foundation\Resource\HydrateTrait;
use ApiClients\Foundation\Resource\ResourceInterface;
use ApiClients\Foundation\Resource\TransportAwareTrait;
use ApiClients\Foundation\Transport\Client;

/**
 * @Nested(foo="Acme\Bar", bar="Acme\Foo")
 * @Collection(foo="Acme\Bar", bar="Acme\Foo")
 */
class DummyResource implements ResourceInterface
{
    use TransportAwareTrait;
    use CallAsyncTrait;
    use HydrateTrait;

    public function getTransportWrapper(): Client
    {
        return $this->getTransport();
    }

    public function callAsyncWrapper(string $method, ...$args)
    {
        return $this->callAsync($method, ...$args);
    }

    public function observableToPromiseWrapper(ObservableInterface $observable): PromiseInterface
    {
        return $this->observableToPromise($observable);
    }

    public function waitWrapper(PromiseInterface $promise)
    {
        return $this->wait($promise);
    }

    public function hydrateWrapper(string $class, array $json): ResourceInterface
    {
        return $this->hydrate($class, $json);
    }

    public function refresh()
    {
        // void
    }
}
