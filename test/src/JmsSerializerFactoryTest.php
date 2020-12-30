<?php

declare(strict_types=1);

namespace BluePsyduckTest\JmsSerializerFactory;

use BluePsyduck\JmsSerializerFactory\JmsSerializerFactory;
use JMS\Serializer\SerializerBuilder;
use JMS\Serializer\SerializerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

/**
 * The PHPUnit test of the JmsSerializerFactory class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class JmsSerializerFactoryTest extends TestCase
{
    public function testInvokeWithEmptyConfig(): void
    {
        $config = [];
        $configKeys = ['foo', 'bar'];

        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->once())
                  ->method('get')
                  ->with($this->identicalTo('config'))
                  ->willReturn($config);

        $expectedInstance = (new SerializerBuilder())->build();

        $factory = new JmsSerializerFactory(...$configKeys);
        $instance = $factory($container, SerializerInterface::class);

        $this->assertEquals($expectedInstance, $instance);
    }
}
