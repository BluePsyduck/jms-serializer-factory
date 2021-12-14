<?php

declare(strict_types=1);

namespace BluePsyduckTest\JmsSerializerFactory;

use BluePsyduck\JmsSerializerFactory\JmsSerializerFactory;
use BluePsyduck\JmsSerializerFactory\Resolver\JmsSerializerResolver;
use BluePsyduck\TestHelper\ReflectionTrait;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * The PHPUnit test of the JmsSerializerFactory class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 *
 * @covers \BluePsyduck\JmsSerializerFactory\JmsSerializerFactory
 */
class JmsSerializerFactoryTest extends TestCase
{
    use ReflectionTrait;

    /**
     * @throws ReflectionException
     */
    public function testCreateResolver(): void
    {
        $keys = ['abc', 'def'];
        $expectedResult = new JmsSerializerResolver($keys);

        $instance = new JmsSerializerFactory(...$keys);

        $result = $this->invokeMethod($instance, 'createResolver', $keys);
        $this->assertEquals($expectedResult, $result);
    }
}
