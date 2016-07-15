<?php
declare(strict_types=1);

namespace WyriHaximus\Tests\ApiClient\Transport;

use Phake;
use WyriHaximus\Tests\ApiClient\Resources\Async\Resource as AsyncResource;
use WyriHaximus\Tests\ApiClient\Resources\Async\SubResource as AsyncSubResource;
use WyriHaximus\Tests\ApiClient\Resources\Sync\Resource as SyncResource;
use WyriHaximus\Tests\ApiClient\TestCase;
use WyriHaximus\ApiClient\Transport\Client;
use WyriHaximus\ApiClient\Transport\Hydrator;

class HydratorTest extends TestCase
{
    public function testBuildAsyncFromSync()
    {
        $hydrator = new Hydrator(Phake::mock(Client::class), [
            'namespace' => 'WyriHaximus\Tests\ApiClient\Resources',
            'resource_namespace' => 'Async',
            'resource_hydrator_cache_dir' => $this->getTmpDir(),
            'resource_hydrator_namespace' => $this->getRandomNameSpace(),
        ]);
        $syncRepository = $this->hydrate(
            SyncResource::class,
            [
                'id' => 1,
                'slug' => 'Wyrihaximus/php-travis-client',
                'sub' => [
                    'id' => 1,
                    'slug' => 'Wyrihaximus/php-travis-client',
                ],
            ],
            'Async'
        );
        $asyncRepository = $hydrator->buildAsyncFromSync('Resource', $syncRepository);
        $this->assertInstanceOf(AsyncResource::class, $asyncRepository);
        $this->assertSame(1, $asyncRepository->id());
        $this->assertSame('Wyrihaximus/php-travis-client', $asyncRepository->slug());
        $this->assertInstanceOf(AsyncSubResource::class, $asyncRepository->sub());
        $this->assertSame(1, $asyncRepository->sub()->id());
        $this->assertSame('Wyrihaximus/php-travis-client', $asyncRepository->sub()->slug());
    }

    public function testSetGeneratedClassesTargetDir()
    {
        $json = [
            'id' => 1,
            'slug' => 'Wyrihaximus/php-travis-client',
            'sub' => [
                'id' => 1,
                'slug' => 'Wyrihaximus/php-travis-client',
            ],
        ];
        $tmpDir = $this->getTmpDir();
        $hydrator = new Hydrator(Phake::mock(Client::class), [
            'namespace' => 'WyriHaximus\Tests\ApiClient\Resources',
            'resource_namespace' => 'Async',
            'resource_hydrator_cache_dir' => $tmpDir,
            'resource_hydrator_namespace' => $this->getRandomNameSpace(),
        ]);
        $hydrator->hydrate(
            'Resource',
            $json
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
        $json = [
            'id' => 1,
            'slug' => 'Wyrihaximus/php-travis-client',
            'sub' => [
                'id' => 1,
                'slug' => 'Wyrihaximus/php-travis-client',
            ],
        ];
        $tmpDir = $this->getTmpDir();
        $hydrator = new Hydrator(Phake::mock(Client::class), [
            'namespace' => 'WyriHaximus\Tests\ApiClient\Resources',
            'resource_namespace' => 'Async',
            'resource_hydrator_cache_dir' => $tmpDir,
            'resource_hydrator_namespace' => $this->getRandomNameSpace(),
        ]);
        $repository = $hydrator->hydrate(
            'Resource',
            $json
        );
        $this->assertSame($json, $hydrator->extract('Resource', $repository));
    }
}
