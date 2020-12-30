<?php

declare(strict_types=1);

namespace BluePsyduckTest\JmsSerializerFactory;

use BluePsyduck\JmsSerializerFactory\Constant\ConfigKey;
use BluePsyduck\JmsSerializerFactory\JmsSerializerFactory;
use Doctrine\Common\Annotations\Reader;
use JMS\Serializer\Accessor\AccessorStrategyInterface;
use JMS\Serializer\Builder\DriverFactoryInterface;
use JMS\Serializer\Construction\ObjectConstructorInterface;
use JMS\Serializer\ContextFactory\DeserializationContextFactoryInterface;
use JMS\Serializer\ContextFactory\SerializationContextFactoryInterface;
use JMS\Serializer\EventDispatcher\EventDispatcher;
use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\Expression\ExpressionEvaluatorInterface;
use JMS\Serializer\Handler\HandlerRegistry;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\Naming\PropertyNamingStrategyInterface;
use JMS\Serializer\SerializerBuilder;
use JMS\Serializer\SerializerInterface;
use JMS\Serializer\Type\ParserInterface;
use JMS\Serializer\Visitor\Factory\DeserializationVisitorFactory;
use JMS\Serializer\Visitor\Factory\SerializationVisitorFactory;
use Metadata\Cache\CacheInterface;
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

        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->any())
                  ->method('get')
                  ->willReturnMap([
                      ['config', $config],
                  ]);

        $expectedInstance = (new SerializerBuilder())->build();

        $factory = new JmsSerializerFactory('foo', 'bar');
        $instance = $factory($container, SerializerInterface::class);

        $this->assertEquals($expectedInstance, $instance);
    }

    public function testInvokeWithFullConfig(): void
    {
        $config = [
            'foo' => [
                'bar' => [
                    ConfigKey::ACCESSOR_STRATEGY => AccessorStrategyInterface::class,
                    ConfigKey::EXPRESSION_EVALUATOR => ExpressionEvaluatorInterface::class,
                    ConfigKey::TYPE_PARSER => ParserInterface::class,
                    ConfigKey::ANNOTATION_READER => Reader::class,
                    ConfigKey::DEBUG => true,
                    ConfigKey::CACHE_DIR => __DIR__,
                    ConfigKey::HANDLERS => [
                        'handler.abc',
                        'handler.def',
                    ],
                    ConfigKey::ADD_DEFAULT_HANDLERS => true,
                    ConfigKey::LISTENERS => [
                        'listener.abc',
                        'listener.def',
                    ],
                    ConfigKey::ADD_DEFAULT_LISTENERS => true,
                    ConfigKey::OBJECT_CONSTRUCTOR => ObjectConstructorInterface::class,
                    ConfigKey::SERIALIZATION_VISITORS => [
                        'abc' => 'visitor.serialize.abc',
                        'def' => 'visitor.serialize.def',
                    ],
                    ConfigKey::DESERIALIZATION_VISITORS => [
                        'abc' => 'visitor.deserialize.abc',
                        'def' => 'visitor.deserialize.def',
                    ],
                    ConfigKey::PROPERTY_NAMING_STRATEGY => PropertyNamingStrategyInterface::class,
                    ConfigKey::ADD_DEFAULT_SERIALIZATION_VISITORS => true,
                    ConfigKey::ADD_DEFAULT_DESERIALIZATION_VISITORS => true,
                    ConfigKey::INCLUDE_INTERFACE_METADATA => true,
                    ConfigKey::METADATA_DIRS => [
                        'Abc' => __DIR__,
                    ],
                    ConfigKey::METADATA_DRIVER_FACTORY => DriverFactoryInterface::class,
                    ConfigKey::SERIALIZATION_CONTEXT_FACTORY => SerializationContextFactoryInterface::class,
                    ConfigKey::DESERIALIZATION_CONTEXT_FACTORY => DeserializationContextFactoryInterface::class,
                    ConfigKey::METADATA_CACHE => CacheInterface::class,
                    ConfigKey::DOC_BLOCK_TYPE_RESOLVER => true,
                ],
            ],
        ];

        $accessorStrategy = $this->createMock(AccessorStrategyInterface::class);
        $expressionEvaluator = $this->createMock(ExpressionEvaluatorInterface::class);
        $typeParser = $this->createMock(ParserInterface::class);
        $annotationReader = $this->createMock(Reader::class);
        $objectConstructor = $this->createMock(ObjectConstructorInterface::class);
        $serializationVisitor1 = $this->createMock(SerializationVisitorFactory::class);
        $serializationVisitor2 = $this->createMock(SerializationVisitorFactory::class);
        $deserializationVisitor1 = $this->createMock(DeserializationVisitorFactory::class);
        $deserializationVisitor2 = $this->createMock(DeserializationVisitorFactory::class);
        $propertyNamingStrategy = $this->createMock(PropertyNamingStrategyInterface::class);
        $metadataDriverFactory = $this->createMock(DriverFactoryInterface::class);
        $serializationContextFactory = $this->createMock(SerializationContextFactoryInterface::class);
        $deserializationContextFactory = $this->createMock(DeserializationContextFactoryInterface::class);
        $metadataCache = $this->createMock(CacheInterface::class);

        $handler1 = new class implements SubscribingHandlerInterface {
            /** @return array<mixed> */
            public static function getSubscribingMethods(): array
            {
                return [];
            }
        };
        $handler2 = new class implements SubscribingHandlerInterface {
            /** @return array<mixed> */
            public static function getSubscribingMethods(): array
            {
                return [];
            }
        };

        $listener1 = new class implements EventSubscriberInterface {
            /** @return array<mixed> */
            public static function getSubscribedEvents(): array
            {
                return [];
            }
        };
        $listener2 = new class implements EventSubscriberInterface {
            /** @return array<mixed> */
            public static function getSubscribedEvents(): array
            {
                return [];
            }
        };

        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->any())
                  ->method('get')
                  ->willReturnMap([
                      ['config', $config],
                      [AccessorStrategyInterface::class, $accessorStrategy],
                      [ExpressionEvaluatorInterface::class, $expressionEvaluator],
                      [ParserInterface::class, $typeParser],
                      [Reader::class, $annotationReader],
                      ['handler.abc', $handler1],
                      ['handler.def', $handler2],
                      ['listener.abc', $listener1],
                      ['listener.def', $listener2],
                      [ObjectConstructorInterface::class, $objectConstructor],
                      ['visitor.serialize.abc', $serializationVisitor1],
                      ['visitor.serialize.def', $serializationVisitor2],
                      ['visitor.deserialize.abc', $deserializationVisitor1],
                      ['visitor.deserialize.def', $deserializationVisitor2],
                      [PropertyNamingStrategyInterface::class, $propertyNamingStrategy],
                      [DriverFactoryInterface::class, $metadataDriverFactory],
                      [SerializationContextFactoryInterface::class, $serializationContextFactory],
                      [DeserializationContextFactoryInterface::class, $deserializationContextFactory],
                      [CacheInterface::class, $metadataCache],
                  ]);

        $builder = new SerializerBuilder();
        $builder->setAccessorStrategy($accessorStrategy)
                ->setExpressionEvaluator($expressionEvaluator)
                ->setTypeParser($typeParser)
                ->setAnnotationReader($annotationReader)
                ->setDebug(true)
                ->setCacheDir(__DIR__)
                ->addDefaultHandlers()
                ->configureHandlers(function (HandlerRegistry $registry) use ($handler1, $handler2): void {
                    $registry->registerSubscribingHandler($handler1);
                    $registry->registerSubscribingHandler($handler2);
                })
                ->addDefaultListeners()
                ->configureListeners(function (EventDispatcher $dispatcher) use ($listener1, $listener2): void {
                    $dispatcher->addSubscriber($listener1);
                    $dispatcher->addSubscriber($listener2);
                })
                ->setObjectConstructor($objectConstructor)
                ->addDefaultSerializationVisitors()
                ->setSerializationVisitor('abc', $serializationVisitor1)
                ->setSerializationVisitor('def', $serializationVisitor2)
                ->addDefaultDeserializationVisitors()
                ->setDeserializationVisitor('abc', $deserializationVisitor1)
                ->setDeserializationVisitor('def', $deserializationVisitor2)
                ->includeInterfaceMetadata(true)
                ->setMetadataDirs(['Abc' => __DIR__])
                ->setMetadataDriverFactory($metadataDriverFactory)
                ->setSerializationContextFactory($serializationContextFactory)
                ->setDeserializationContextFactory($deserializationContextFactory)
                ->setMetadataCache($metadataCache)
                ->setDocBlockTypeResolver(true);

        $expectedInstance = $builder->build();

        $factory = new JmsSerializerFactory('foo', 'bar');
        $instance = $factory($container, SerializerInterface::class);

        $this->assertEquals($expectedInstance, $instance);
    }
}
