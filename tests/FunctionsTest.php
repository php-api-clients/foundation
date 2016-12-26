<?php declare(strict_types=1);

namespace ApiClients\Tests\Foundation;

use ApiClients\Tools\TestUtilities\TestCase;
use function ApiClients\Foundation\options_merge;

final class FunctionsTest extends TestCase
{
    public function optionsProvider()
    {
        yield [
            [],
            [],
            [],
        ];

        yield [
            [],
            ['foo' => 'bar'],
            ['foo' => 'bar'],
        ];

        yield [
            [],
            [
                'foo' => 'bar',
                'middleware' => [],
            ],
            [
                'foo' => 'bar',
                'middleware' => [],
            ],
        ];

        yield [
            [
                'middleware' => [
                    'foo',
                ],
            ],
            [
                'foo' => 'bar',
                'middleware' => [
                    'bar',
                ],
            ],
            [
                'middleware' => [
                    'foo',
                    'bar',
                ],
                'foo' => 'bar',
            ],
        ];

        yield [
            [
                'middleware' => [
                    'foo',
                    'bar',
                ],
            ],
            [
                'foo' => 'bar',
                'middleware' => [
                    'bar',
                ],
            ],
            [
                'middleware' => [
                    'foo',
                    'bar',
                ],
                'foo' => 'bar',
            ],
        ];
    }

    /**
     * @dataProvider optionsProvider
     */
    public function testOptionsMerge(array $base, array $options, array $expected)
    {
        self::assertSame(
            $expected,
            options_merge(
                $base,
                $options
            )
        );
    }
}
