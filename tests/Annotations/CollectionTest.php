<?php
declare(strict_types=1);

namespace ApiClients\Tests\Foundation\Annotations;

use Doctrine\Common\Annotations\AnnotationReader;
use ApiClients\Foundation\Annotations\Collection;
use ApiClients\Tests\Foundation\Resource\DummyResource;
use ApiClients\Tests\Foundation\TestCase;

class CollectionTest extends TestCase
{
    public function testProperties()
    {
        $collection = new Collection([
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
            $collection->properties()
        );
    }

    public function testHas()
    {
        $collection = new Collection([
            'a' => 'a',
            'b' => 'b',
            'c' => 'c',
            'd' => 'd',
            'e' => 'e',
        ]);
        $this->assertTrue($collection->has('a'));
        $this->assertTrue($collection->has('b'));
        $this->assertTrue($collection->has('c'));
        $this->assertTrue($collection->has('d'));
        $this->assertTrue($collection->has('e'));
        $this->assertFalse($collection->has('f'));
        $this->assertFalse($collection->has('g'));
        $this->assertFalse($collection->has('h'));
        $this->assertFalse($collection->has('i'));
        $this->assertFalse($collection->has('j'));
    }

    public function testGet()
    {
        $collection = new Collection([
            'a' => 'a',
            'b' => 'b',
            'c' => 'c',
            'd' => 'd',
            'e' => 'e',
        ]);
        $this->assertSame('a', $collection->get('a'));
        $this->assertSame('b', $collection->get('b'));
        $this->assertSame('c', $collection->get('c'));
        $this->assertSame('d', $collection->get('d'));
        $this->assertSame('e', $collection->get('e'));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetException()
    {
        $collection = new Collection([]);
        $collection->get('a');
    }

    public function testDummyResourceAnnotation()
    {
        $dummy = new DummyResource();
        $reader = new AnnotationReader();
        $annotaion = $reader->getClassAnnotation(new \ReflectionClass($dummy), Collection::class);
        $this->assertInstanceOf(Collection::class, $annotaion);
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
