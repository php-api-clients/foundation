<?php
declare(strict_types=1);

namespace ApiClients\Tests\Foundation\Annotations;

use Doctrine\Common\Annotations\AnnotationReader;
use ApiClients\Foundation\Annotations\Nested;
use ApiClients\Tests\Foundation\Resource\DummyResource;
use ApiClients\Tests\Foundation\TestCase;

class NestedTest extends TestCase
{
    public function testProperties()
    {
        $nested = new Nested([
            'a' => 'a',
            'b' => 'b',
            'c' => 'c',
            'd' => 'd',
            'e' => 'e',
        ]);
        $this->assertSame(
            [
                'a',
                'b',
                'c',
                'd',
                'e',
            ],
            $nested->properties()
        );
    }

    public function testHas()
    {
        $nested = new Nested([
            'a' => 'a',
            'b' => 'b',
            'c' => 'c',
            'd' => 'd',
            'e' => 'e',
        ]);
        $this->assertTrue($nested->has('a'));
        $this->assertTrue($nested->has('b'));
        $this->assertTrue($nested->has('c'));
        $this->assertTrue($nested->has('d'));
        $this->assertTrue($nested->has('e'));
        $this->assertFalse($nested->has('f'));
        $this->assertFalse($nested->has('g'));
        $this->assertFalse($nested->has('h'));
        $this->assertFalse($nested->has('i'));
        $this->assertFalse($nested->has('j'));
    }

    public function testGet()
    {
        $nested = new Nested([
            'a' => 'a',
            'b' => 'b',
            'c' => 'c',
            'd' => 'd',
            'e' => 'e',
        ]);
        $this->assertSame('a', $nested->get('a'));
        $this->assertSame('b', $nested->get('b'));
        $this->assertSame('c', $nested->get('c'));
        $this->assertSame('d', $nested->get('d'));
        $this->assertSame('e', $nested->get('e'));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetException()
    {
        $nested = new Nested([]);
        $nested->get('a');
    }

    public function testDummyResourceAnnotation()
    {
        $dummy = new DummyResource();
        $reader = new AnnotationReader();
        $annotaion = $reader->getClassAnnotation(new \ReflectionClass($dummy), Nested::class);
        $this->assertInstanceOf(Nested::class, $annotaion);
        $this->assertSame(
            [
                'foo',
                'bar',
            ],
            $annotaion->properties()
        );
        $this->assertTrue($annotaion->has('foo'));
        $this->assertTrue($annotaion->has('bar'));
        $this->assertFalse($annotaion->has('baz'));
        $this->assertSame('Acme\Bar', $annotaion->get('foo'));
        $this->assertSame('Acme\Foo', $annotaion->get('bar'));
    }
}
