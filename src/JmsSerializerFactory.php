<?php

declare(strict_types=1);

namespace BluePsyduck\JmsSerializerFactory;

use BluePsyduck\JmsSerializerFactory\Resolver\JmsSerializerResolver;
use BluePsyduck\LaminasAutoWireFactory\Factory\AbstractConfigResolverFactory;
use BluePsyduck\LaminasAutoWireFactory\Resolver\ResolverInterface;

/**
 * The factory for initializing a JMS serializer using the SerializerBuilder.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class JmsSerializerFactory extends AbstractConfigResolverFactory
{
    protected function createResolver(array $keys): ResolverInterface
    {
        return new JmsSerializerResolver($keys);
    }
}
