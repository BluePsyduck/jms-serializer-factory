<?php

declare(strict_types=1);

namespace BluePsyduck\JmsSerializerFactory\Attribute;

use Attribute;
use BluePsyduck\JmsSerializerFactory\Resolver\JmsSerializerResolver;
use BluePsyduck\LaminasAutoWireFactory\Attribute\ResolverAttribute;
use BluePsyduck\LaminasAutoWireFactory\Resolver\ResolverInterface;

/**
 * The attribute using the JMS serializer resolver.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
#[Attribute(Attribute::TARGET_PARAMETER | Attribute::TARGET_PROPERTY)]
class UseJmsSerializer implements ResolverAttribute
{
    /** @var array<array-key> */
    private array $keys;

    public function __construct(string|int ...$keys)
    {
        $this->keys = $keys;
    }

    public function createResolver(): ResolverInterface
    {
        return new JmsSerializerResolver($this->keys);
    }
}
