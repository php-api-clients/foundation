<?php
declare(strict_types=1);

namespace ApiClients\Tests\Foundation\Resource;

use Phake;
use React\EventLoop\Factory;
use React\Promise\FulfilledPromise;
use React\Promise\PromiseInterface;
use Rx\ObservableInterface;
use ApiClients\Foundation\Resource\ResourceInterface;
use ApiClients\Foundation\Transport\Client;
use ApiClients\Foundation\Transport\Hydrator;
use ApiClients\Tests\Foundation\TestCase;

class CallAsyncTraitTest extends TestCase
{
    public function testCallAsync()
    {
        $resource = new DummyResource();
        $resourceMock = Phake::mock(ResourceInterface::class);
        Phake::when($resourceMock)->refresh(1, 2, 3)->thenReturn(null);

        $hydrator = Phake::mock(Hydrator::class);
        Phake::when($hydrator)->buildAsyncFromSync('DummyResource', $resource)->thenReturn($resourceMock);

        $transport = Phake::mock(Client::class);
        Phake::when($transport)->getHydrator()->thenReturn($hydrator);

        $resource->setTransport($transport);
        $resource->callAsyncWrapper('refresh', 1, 2, 3);

        Phake::verify($resourceMock)->refresh(1, 2, 3);
    }

    public function testObservableToPromise()
    {
        $resource = new DummyResource();
        $observable = Phake::mock(ObservableInterface::class);
        $this->assertInstanceOf(PromiseInterface::class, $resource->observableToPromiseWrapper($observable));
    }

    public function testWait()
    {
        $resource = new DummyResource();
        $transport = Phake::mock(Client::class);
        Phake::when($transport)->getLoop()->thenReturn(Factory::create());
        $resource->setTransport($transport);
        $this->assertSame('abc', $resource->waitWrapper(new FulfilledPromise('abc')));
    }
}
