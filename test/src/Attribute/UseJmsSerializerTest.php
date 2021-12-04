<?php

declare(strict_types=1);

namespace BluePsyduckTest\JmsSerializerFactory\Attribute;

use BluePsyduck\JmsSerializerFactory\Attribute\UseJmsSerializer;
use BluePsyduck\JmsSerializerFactory\Resolver\JmsSerializerResolver;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the UseJmsSerializer class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 *
 * @covers \BluePsyduck\JmsSerializerFactory\Attribute\UseJmsSerializer
 */
class UseJmsSerializerTest extends TestCase
{
    public function testCreateResolver(): void
    {
        $keys = ['abc', 'def'];
        $expectedResult = new JmsSerializerResolver($keys);

        $instance = new UseJmsSerializer(...$keys);
        $result = $instance->createResolver();

        $this->assertEquals($expectedResult, $result);
    }
}
