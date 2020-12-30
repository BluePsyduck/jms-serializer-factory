<?php

declare(strict_types=1);

namespace BluePsyduck\JmsSerializerFactory;

use BluePsyduck\JmsSerializerFactory\Constant\ConfigKey;
use JMS\Serializer\EventDispatcher\EventDispatcher;
use JMS\Serializer\Handler\HandlerRegistry;
use JMS\Serializer\SerializerBuilder;
use JMS\Serializer\SerializerInterface;
use Psr\Container\ContainerInterface;

/**
 * The factory for initializing a JMS serializer using the SerializerBuilder.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class JmsSerializerFactory
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

    /**
     * The alias under which the config is accessible in the container.
     * @var string
     */
    public static string $configAlias = 'config';

    /** @var array<mixed> */
    private array $configKeys;

    /**
     * @param mixed ...$configKeys
     */
    public function __construct(...$configKeys)
    {
        $this->configKeys = $configKeys;
    }

    /**
     * @param array<mixed> $state
     * @return self
     */
    public static function __set_state(array $state): self
    {
        return new self(...($state['configKeys'] ?? []));
    }

    public function __invoke(ContainerInterface $container, string $requestedName): SerializerInterface
    {
        $config = $this->readConfig($container, $this->configKeys);
        $builder = new SerializerBuilder();

        $this->configureDependencies($builder, $config, $container);
        $this->configureValues($builder, $config);
        $this->configureFlags($builder, $config);
        $this->configureHandlers($builder, $config, $container);
        $this->configureListeners($builder, $config, $container);
        $this->configureVisitors($builder, $config, $container);

        return $builder->build();
    }

    /**
     * Reads the factory config from the container using the configKeys.
     * @param ContainerInterface $container
     * @param array<mixed> $configKeys
     * @return array<mixed>
     */
    private function readConfig(ContainerInterface $container, array $configKeys): array
    {
        $config = $container->get(self::$configAlias);
        foreach ($configKeys as $configKey) {
            $config = $config[$configKey] ?? [];
        }
        return $config;
    }

    /**
     * Configures the dependencies in the builder, which get retrieved from the container using the config values.
     * @param SerializerBuilder $builder
     * @param array<mixed> $config
     * @param ContainerInterface $container
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
     * @param array<mixed> $config
     * @param ContainerInterface $container
     */
    private function configureHandlers(SerializerBuilder $builder, array $config, ContainerInterface $container): void
    {
        if (isset($config[ConfigKey::HANDLERS])) {
            $handlers = $config[ConfigKey::HANDLERS];

            $builder->configureHandlers(function (HandlerRegistry $registry) use ($handlers, $container): void {
                foreach ($handlers as $handler) {
                    $registry->registerSubscribingHandler($container->get($handler));
                }
            });
        }
    }

    /**
     * Configures the listeners in the builder, using the config values as container aliases.
     * @param SerializerBuilder $builder
     * @param array<mixed> $config
     * @param ContainerInterface $container
     */
    private function configureListeners(SerializerBuilder $builder, array $config, ContainerInterface $container): void
    {
        if (isset($config[ConfigKey::LISTENERS])) {
            $listeners = $config[ConfigKey::LISTENERS];

            $builder->configureListeners(function (EventDispatcher $dispatcher) use ($listeners, $container): void {
                foreach ($listeners as $listener) {
                    $dispatcher->addSubscriber($container->get($listener));
                }
            });
        }
    }

    /**
     * Configures the visitors to the builder.
     * @param SerializerBuilder $builder
     * @param array<mixed> $config
     * @param ContainerInterface $container
     */
    private function configureVisitors(SerializerBuilder $builder, array $config, ContainerInterface $container): void
    {
        foreach ($config[ConfigKey::SERIALIZATION_VISITORS] ?? [] as $format => $visitor) {
            $builder->setSerializationVisitor($format, $container->get($visitor));
        }
        foreach ($config[ConfigKey::DESERIALIZATION_VISITORS] ?? [] as $format => $visitor) {
            $builder->setDeserializationVisitor($format, $container->get($visitor));
        }
    }
}
