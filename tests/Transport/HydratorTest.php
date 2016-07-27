<?php
declare(strict_types=1);

namespace ApiClients\Tests\Foundation\Transport;

use Phake;
use ApiClients\Tests\Foundation\Resources\Async\Resource as AsyncResource;
use ApiClients\Tests\Foundation\Resources\Async\SubResource as AsyncSubResource;
use ApiClients\Tests\Foundation\Resources\Sync\Resource as SyncResource;
use ApiClients\Tests\Foundation\TestCase;
use ApiClients\Foundation\Transport\Client;
use ApiClients\Foundation\Transport\Hydrator;

class HydratorTest extends TestCase
{
    public function testBuildAsyncFromSync()
    {
        $hydrator = new Hydrator(Phake::mock(Client::class), [
            'namespace' => 'ApiClients\Tests\Foundation\Resources',
            'resource_namespace' => 'Async',
            'resource_hydrator_cache_dir' => $this->getTmpDir(),
            'resource_hydrator_namespace' => $this->getRandomNameSpace(),
        ]);
        $syncRepository = $this->hydrate(
            SyncResource::class,
            $this->getJson(),
            'Async'
        );
        $asyncRepository = $hydrator->buildAsyncFromSync('Resource', $syncRepository);
        $this->assertInstanceOf(AsyncResource::class, $asyncRepository);
        $this->assertSame(1, $asyncRepository->id());
        $this->assertSame('Wyrihaximus/php-travis-client', $asyncRepository->slug());
        $this->assertInstanceOf(AsyncSubResource::class, $asyncRepository->sub());
        $this->assertSame(1, $asyncRepository->sub()->id());
        $this->assertSame('Wyrihaximus/php-travis-client', $asyncRepository->sub()->slug());
        $this->assertSame(3, count($asyncRepository->subs()));
        for ($i = 0; $i < count($asyncRepository->subs()); $i++) {
            $this->assertInstanceOf(AsyncSubResource::class, $asyncRepository->subs()[$i]);
            $this->assertSame($i + 1, $asyncRepository->subs()[$i]->id());
            $this->assertSame('Wyrihaximus/php-travis-client', $asyncRepository->subs()[$i]->slug());
        }
    }

    public function testSetGeneratedClassesTargetDir()
    {
        $tmpDir = $this->getTmpDir();
        $hydrator = new Hydrator(Phake::mock(Client::class), [
            'namespace' => 'ApiClients\Tests\Foundation\Resources',
            'resource_namespace' => 'Async',
            'resource_hydrator_cache_dir' => $tmpDir,
            'resource_hydrator_namespace' => $this->getRandomNameSpace(),
        ]);
        $hydrator->hydrate(
            'Resource',
            $this->getJson()
        );
        $files = [];
        $directory = dir($tmpDir);
        while (false !== ($entry = $directory->read())) {
            if (in_array($entry, ['.', '..'])) {
                continue;
            }

            if (is_file($tmpDir . $entry)) {
                $files[] = $tmpDir . $entry;
                continue;
            }
        }
        $directory->close();
        $this->assertSame(2, count($files));
    }

    public function testExtract()
    {
        $json = $this->getJson();
        $tmpDir = $this->getTmpDir();
        $hydrator = new Hydrator(Phake::mock(Client::class), [
            'namespace' => 'ApiClients\Tests\Foundation\Resources',
            'resource_namespace' => 'Async',
            'resource_hydrator_cache_dir' => $tmpDir,
            'resource_hydrator_namespace' => $this->getRandomNameSpace(),
        ]);
        $repository = $hydrator->hydrate(
            'Resource',
            $json
        );
        $this->assertEquals($json, $hydrator->extract('Resource', $repository));
    }
}
