<?php
declare(strict_types=1);

namespace WyriHaximus\Tests\ApiClient\Resource;

use React\Promise\PromiseInterface;
use Rx\ObservableInterface;
use WyriHaximus\ApiClient\Resource\CallAsyncTrait;
use WyriHaximus\ApiClient\Resource\TransportAwareTrait;
use WyriHaximus\ApiClient\Transport\Client;

class DummyResource
{
    use TransportAwareTrait;
    use CallAsyncTrait;

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
}
