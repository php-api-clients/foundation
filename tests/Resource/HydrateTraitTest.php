<?php
declare(strict_types=1);

namespace WyriHaximus\Tests\ApiClient\Resource;

use Phake;
use React\EventLoop\Factory;
use React\Promise\FulfilledPromise;
use React\Promise\PromiseInterface;
use Rx\ObservableInterface;
use WyriHaximus\ApiClient\Resource\ResourceInterface;
use WyriHaximus\ApiClient\Transport\Client;
use WyriHaximus\ApiClient\Transport\Hydrator;
use WyriHaximus\Tests\ApiClient\TestCase;

class HydrateTraitTest extends TestCase
{
    public function testHydrate()
    {
        $resource = new DummyResource();

        $hydrator = Phake::mock(Hydrator::class);
        Phake::when($hydrator)->hydrate('Beer', [
            'brewery' => 'Nøgne',
            'name' => 'Dark Horizon 4th edition',
        ])->thenReturn(Phake::mock(ResourceInterface::class));

        $transport = Phake::mock(Client::class);
        Phake::when($transport)->getHydrator()->thenReturn($hydrator);

        $resource->setTransport($transport);
        $resource->hydrateWrapper('Beer', [
            'brewery' => 'Nøgne',
            'name' => 'Dark Horizon 4th edition',
        ]);

        Phake::verify($hydrator)->hydrate('Beer', [
            'brewery' => 'Nøgne',
            'name' => 'Dark Horizon 4th edition',
        ]);
    }
}
