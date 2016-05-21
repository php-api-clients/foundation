<?php
declare(strict_types=1);

namespace WyriHaximus\Tests\ApiClient\Resource;

use React\Promise\PromiseInterface;
use Rx\ObservableInterface;
use WyriHaximus\ApiClient\Resource\CallAsyncTrait;
use WyriHaximus\ApiClient\Resource\HydrateTrait;
use WyriHaximus\ApiClient\Resource\ResourceInterface;
use WyriHaximus\ApiClient\Resource\TransportAwareTrait;
use WyriHaximus\ApiClient\Transport\Client;

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
