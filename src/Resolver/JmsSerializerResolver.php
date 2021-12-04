<?php

declare(strict_types=1);

namespace BluePsyduck\JmsSerializerFactory\Resolver;

use BluePsyduck\JmsSerializerFactory\Constant\ConfigKey;
use BluePsyduck\LaminasAutoWireFactory\Resolver\ConfigResolver;
use JMS\Serializer\EventDispatcher\EventDispatcher;
use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\Handler\HandlerRegistry;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\SerializerBuilder;
use JMS\Serializer\Visitor\Factory\DeserializationVisitorFactory;
use JMS\Serializer\Visitor\Factory\SerializationVisitorFactory;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;

/**
 * The resolver using the SerializerBuilder and the config to create the serializer instance.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class JmsSerializerResolver extends ConfigResolver
{
    private const DEPENDENCIES = [
        ConfigKey::ACCESSOR_STRATEGY => 'setAccessorStrategy',
        ConfigKey::EXPRESSION_EVALUATOR => 'setExpressionEvaluator',
        ConfigKey::TYPE_PARSER => 'setTypeParser',
        ConfigKey::ANNOTATION_READER => 'setAnnotationReader',
        ConfigKey::OBJECT_CONSTRUCTOR => 'setObjectConstructor',
        ConfigKey::PROPERTY_NAMING_STRATEGY => 'setPropertyNamingStrategy',
        ConfigKey::METADATA_DRIVER_FACTORY => 'setMetadataDriverFactory',
        ConfigKey::SERIALIZATION_CONTEXT_FACTORY => 'setSerializationContextFactory',
        ConfigKey::DESERIALIZATION_CONTEXT_FACTORY => 'setDeserializationContextFactory',
        ConfigKey::METADATA_CACHE => 'setMetadataCache',
    ];

    private const VALUES = [
        ConfigKey::DEBUG => 'setDebug',
        ConfigKey::CACHE_DIR => 'setCacheDir',
        ConfigKey::INCLUDE_INTERFACE_METADATA => 'includeInterfaceMetadata',
        ConfigKey::METADATA_DIRS => 'setMetadataDirs',
        ConfigKey::DOC_BLOCK_TYPE_RESOLVER => 'setDocBlockTypeResolver',
    ];

    private const FLAGS = [
        ConfigKey::ADD_DEFAULT_HANDLERS => 'addDefaultHandlers',
        ConfigKey::ADD_DEFAULT_LISTENERS => 'addDefaultListeners',
        ConfigKey::ADD_DEFAULT_SERIALIZATION_VISITORS => 'addDefaultSerializationVisitors',
        ConfigKey::ADD_DEFAULT_DESERIALIZATION_VISITORS => 'addDefaultDeserializationVisitors',
    ];

    public function resolve(ContainerInterface $container): mixed
    {
        $config = parent::resolve($container);

        $builder = new SerializerBuilder();

        $this->configureDependencies($builder, $config, $container); // @phpstan-ignore-line
        $this->configureValues($builder, $config); // @phpstan-ignore-line
        $this->configureFlags($builder, $config); // @phpstan-ignore-line
        $this->configureHandlers($builder, $config, $container); // @phpstan-ignore-line
        $this->configureListeners($builder, $config, $container); // @phpstan-ignore-line
        $this->configureVisitors($builder, $config, $container); // @phpstan-ignore-line

        return $builder->build();
    }

    /**
     * Configures the dependencies in the builder, which get retrieved from the container using the config values.
     * @param SerializerBuilder $builder
     * @param array<string, string> $config
     * @param ContainerInterface $container
     * @throws ContainerExceptionInterface
     */
    private function configureDependencies(
        SerializerBuilder $builder,
        array $config,
        ContainerInterface $container
    ): void {
        foreach (self::DEPENDENCIES as $configKey => $method) {
            if (isset($config[$configKey])) {
                $builder->$method($container->get($config[$configKey]));
            }
        }
    }

    /**
     * Configures the values which are set from the config to the builder, without any manipulation.
     * @param SerializerBuilder $builder
     * @param array<mixed> $config
     */
    private function configureValues(SerializerBuilder $builder, array $config): void
    {
        foreach (self::VALUES as $configKey => $method) {
            if (isset($config[$configKey])) {
                $builder->$method($config[$configKey]);
            }
        }
    }

    /**
     * Configures the flags on the builder, of they are set to true in the config
     * @param SerializerBuilder $builder
     * @param array<mixed> $config
     */
    private function configureFlags(SerializerBuilder $builder, array $config): void
    {
        foreach (self::FLAGS as $configKey => $method) {
            if (isset($config[$configKey]) && $config[$configKey] === true) {
                $builder->$method();
            }
        }
    }

    /**
     * Configures the handlers in the builder, using the config values as container aliases.
     * @param SerializerBuilder $builder
     * @param array{handlers?: array<string>} $config
     * @param ContainerInterface $container
     * @throws ContainerExceptionInterface
     */
    private function configureHandlers(SerializerBuilder $builder, array $config, ContainerInterface $container): void
    {
        if (isset($config[ConfigKey::HANDLERS])) {
            $handlers = $config[ConfigKey::HANDLERS];

            $builder->configureHandlers(function (HandlerRegistry $registry) use ($handlers, $container): void {
                foreach ($handlers as $handler) {
                    $instance = $container->get($handler);
                    if ($instance instanceof SubscribingHandlerInterface) {
                        $registry->registerSubscribingHandler($instance);
                    }
                }
            });
        }
    }

    /**
     * Configures the listeners in the builder, using the config values as container aliases.
     * @param SerializerBuilder $builder
     * @param array{listeners?: array<string>} $config
     * @param ContainerInterface $container
     * @throws ContainerExceptionInterface
     */
    private function configureListeners(SerializerBuilder $builder, array $config, ContainerInterface $container): void
    {
        if (isset($config[ConfigKey::LISTENERS])) {
            $listeners = $config[ConfigKey::LISTENERS];

            $builder->configureListeners(function (EventDispatcher $dispatcher) use ($listeners, $container): void {
                foreach ($listeners as $listener) {
                    $instance = $container->get($listener);
                    if ($instance instanceof EventSubscriberInterface) {
                        $dispatcher->addSubscriber($instance);
                    }
                }
            });
        }
    }

    /**
     * Configures the visitors to the builder.
     * @param SerializerBuilder $builder
     * @param array{serializationVisitors?: array<string>, deserializationVisitors?: array<string>} $config
     * @param ContainerInterface $container
     * @throws ContainerExceptionInterface
     */
    private function configureVisitors(SerializerBuilder $builder, array $config, ContainerInterface $container): void
    {
        foreach ($config[ConfigKey::SERIALIZATION_VISITORS] ?? [] as $format => $visitor) {
            $instance = $container->get($visitor);
            if ($instance instanceof  SerializationVisitorFactory) {
                $builder->setSerializationVisitor($format, $instance);
            }
        }
        foreach ($config[ConfigKey::DESERIALIZATION_VISITORS] ?? [] as $format => $visitor) {
            $instance = $container->get($visitor);
            if ($instance instanceof  DeserializationVisitorFactory) {
                $builder->setDeserializationVisitor($format, $instance);
            }
        }
    }
}
