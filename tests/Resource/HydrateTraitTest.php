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

class HydrateTraitTest extends TestCase
{
    public function testHydrate()
    {
        $resourceName = 'Beer';
        $resourceJson = [
            'brewery' => 'Nøgne',
            'name' => 'Dark Horizon 4th edition',
        ];

        $resource = new DummyResource();

        $hydrator = Phake::mock(Hydrator::class);
        Phake::when($hydrator)->hydrate($resourceName, $resourceJson)->thenReturn(Phake::mock(ResourceInterface::class));

        $transport = Phake::mock(Client::class);
        Phake::when($transport)->getHydrator()->thenReturn($hydrator);

        $resource->setTransport($transport);
        $resource->hydrateWrapper($resourceName, $resourceJson);

        Phake::verify($hydrator)->hydrate($resourceName, $resourceJson);
    }
}
