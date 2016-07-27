<?php
declare(strict_types=1);

namespace ApiClients\Tests\Foundation\Annotations;

use ApiClients\Tests\Foundation\Resources\Sync\Resource;
use ApiClients\Tests\Foundation\TestCase;
use function ApiClients\Foundation\get_properties;
use function ApiClients\Foundation\get_property;
use function ApiClients\Foundation\resource_pretty_print;

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
        $expected = "ApiClients\Tests\Foundation\Resources\Sync\Resource
	id: 1
	slug: Wyrihaximus/php-travis-client
	sub: ApiClients\Tests\Foundation\Resources\Async\SubResource
		id: 1
		slug: Wyrihaximus/php-travis-client
	subs: [
		ApiClients\Tests\Foundation\Resources\Async\SubResource
			id: 1
			slug: Wyrihaximus/php-travis-client
		ApiClients\Tests\Foundation\Resources\Async\SubResource
			id: 2
			slug: Wyrihaximus/php-travis-client
		ApiClients\Tests\Foundation\Resources\Async\SubResource
			id: 3
			slug: Wyrihaximus/php-travis-client
	]
";
        ob_start();
        resource_pretty_print($resource);
        $actual = ob_get_clean();

        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $expected = str_replace(
                [
                    "\r",
                    "\n",
                ],
                '',
                $expected
            );
            $actual = str_replace(
                [
                    "\r",
                    "\n",
                ],
                '',
                $actual
            );
        }

        $this->assertSame($expected, $actual);
    }
}
