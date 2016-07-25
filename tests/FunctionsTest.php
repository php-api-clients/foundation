<?php
declare(strict_types=1);

namespace WyriHaximus\Tests\ApiClient\Annotations;

use WyriHaximus\Tests\ApiClient\Resources\Sync\Resource;
use WyriHaximus\Tests\ApiClient\TestCase;
use function WyriHaximus\ApiClient\get_properties;
use function WyriHaximus\ApiClient\get_property;

class FunctionsTest extends TestCase
{
    public function testGetProperties()
    {
        $properties = [];

        foreach (get_properties(new Resource()) as $property) {
            $properties[] = $property->getName();
        }

        $this->assertSame([
            'id',
            'slug',
            'sub',
            'subs',
        ], $properties);
    }

    public function testGetProperty()
    {
        $syncRepository = $this->hydrate(
            Resource::class,
            $this->getJson(),
            'Async'
        );

        $this->assertSame(
            $this->getJson()['id'],
            get_property($syncRepository, 'id')->getValue($syncRepository)
        );
    }

    public function testResourcePrettyPrint()
    {
        $resource = $this->hydrate(
            Resource::class,
            $this->getJson(),
            'Async'
        );
        $expected = "WyriHaximus\Tests\ApiClient\Resources\Sync\Resource
	id: 1
	slug: Wyrihaximus/php-travis-client
	sub: WyriHaximus\Tests\ApiClient\Resources\Async\SubResource
		id: 1
		slug: Wyrihaximus/php-travis-client
	subs: [
		WyriHaximus\Tests\ApiClient\Resources\Async\SubResource
			id: 1
			slug: Wyrihaximus/php-travis-client
		WyriHaximus\Tests\ApiClient\Resources\Async\SubResource
			id: 2
			slug: Wyrihaximus/php-travis-client
		WyriHaximus\Tests\ApiClient\Resources\Async\SubResource
			id: 3
			slug: Wyrihaximus/php-travis-client
	]
";
        ob_start();
        \WyriHaximus\ApiClient\resource_pretty_print($resource);
        $actual = ob_get_clean();
        $this->assertSame($expected, $actual);
    }
}
