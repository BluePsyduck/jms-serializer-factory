# JMS Serializer Factory

[![GitHub release](https://img.shields.io/github/v/release/BluePsyduck/jms-serializer-factory)](https://github.com/BluePsyduck/jms-serializer-factory/releases)
[![GitHub](https://img.shields.io/github/license/BluePsyduck/jms-serializer-factory)](LICENSE.md)
[![build](https://img.shields.io/github/workflow/status/BluePsyduck/jms-serializer-factory/CI?logo=github)](https://github.com/BluePsyduck/jms-serializer-factory/actions)
[![Codecov](https://img.shields.io/codecov/c/gh/BluePsyduck/jms-serializer-factory?logo=codecov)](https://codecov.io/gh/BluePsyduck/jms-serializer-factory)

This library provides a Laminas-compatible factory to create [JMS serializer](https://github.com/schmittjoh/serializer)
instances from the config without the need to write an actual factory.

## Usage

Install the package through composer with:

    composer require bluepsyduck/jms-serializer-factory

The first step is to add the settings to your Laminas config. Use the `ConfigKey` interface to get the names of the 
config options. A full list of options can be found below.

```php
<?php
// config/serializers.php

use BluePsyduck\JmsSerializerFactory\Constant\ConfigKey;
use JMS\Serializer\Naming\IdenticalPropertyNamingStrategy;

return [
    'serializers' => [
      'my-fancy-serializer' => [
          ConfigKey::PROPERTY_NAMING_STRATEGY => IdenticalPropertyNamingStrategy::class,
          ConfigKey::HANDLERS => [
              MyFancyHandler::class,
          ],
          ConfigKey::METADATA_DIRS => [
              'My\Fancy\Namespace' => __DIR__ . '/../serializer',
          ],
          ConfigKey::CACHE_DIR => __DIR__ . '/../../data/cache',
      ],
    ],
];
```

The JMS Serializer Factory will request any dependencies from the container, so make sure to register all of them. If
they do not have any dependencies themselves, use the `InvokableFactory` to register them.

```php
<?php
// config/dependencies.php

use BluePsyduck\JmsSerializerFactory\JmsSerializerFactory;
use Laminas\ServiceManager\Factory\InvokableFactory;
use JMS\Serializer\Naming\IdenticalPropertyNamingStrategy;

return [
    'dependencies' => [
        'factories' => [
            // Add the services used in the serializer config to the container.
            IdenticalPropertyNamingStrategy::class => InvokableFactory::class,
            MyFancyHandler::class => InvokableFactory::class,
            
            // Add the actual serializer instance to the container.
            'MyFancySerializer' => new JmsSerializerFactory('serializers', 'my-fancy-serializer'),
            // This will take the config for the serializer from $config['serializers']['my-fancy-serializer']
        ],
    ],
];
```

With this configuration, you now can get your serializer instance from the container:

```php
<?php

/* @var \JMS\Serializer\SerializerInterface $myFancySerializer */
$myFancySerializer = $container->get('MyFancySerializer');

// Use it as usual.
$json = $myFancySerializer->serialize($data, 'json');
```

## All config options

The following table shows the full list of config values supported by the factory. `Constant` refers to the name of the
constant in the `ConfigKey` interface, and the `SerializerBuilder method` column refers to the method of the builder 
used for that config value. For further details, please check the method signatures and doc-blocks of the builder.

Constant                             | Expected value                    | SerializerBuilder method
------------------------------------ | --------------------------------- | -------------------------------------
ACCESSOR_STRATEGY                    | container alias                   | ->setAccessorStrategy()
EXPRESSION_EVALUATOR                 | container alias                   | ->setExpressionEvaluator()
TYPE_PARSER                          | container alias                   | ->setTypeParser()
ANNOTATION_READER                    | container alias                   | ->setAnnotationReader()
DEBUG                                | bool                              | ->setDebug()
CACHE_DIR                            | string                            | ->setCacheDir()
ADD_DEFAULT_HANDLERS                 | true                              | ->addDefaultHandlers()
HANDLERS                             | array\<container aliases\>        | ->configureHandlers()
ADD_DEFAULT_LISTENERS                | true                              | ->addDefaultListeners()
LISTENERS                            | array\<container alias\>          | ->configureListeners()
OBJECT_CONSTRUCTOR                   | container alias                   | ->setObjectConstructor()
PROPERTY_NAMING_STRATEGY             | container alias                   | ->setPropertyNamingStrategy()
SERIALIZATION_VISITORS               | array\<string, container alias\>  | ->setSerializationVisitor()
DESERIALIZATION_VISITORS             | array\<string, container alias\>  | ->setDeserializationVisitor()
ADD_DEFAULT_SERIALIZATION_VISITORS   | true                              | ->addDefaultSerializationVisitors()
ADD_DEFAULT_DESERIALIZATION_VISITORS | true                              | ->addDefaultDeserializationVisitors()
INCLUDE_INTERFACE_METADATA           | bool                              | ->includeInterfaceMetadata() 
METADATA_DIRS                        | array\<string, string\>           | ->setMetadataDirs()
METADATA_DRIVER_FACTORY              | container alias                   | ->setMetadataDriverFactory()
SERIALIZATION_CONTEXT_FACTORY        | container alias                   | ->setSerializationContextFactory()
DESERIALIZATION_CONTEXT_FACTORY      | container alias                   | ->setDeserializationContextFactory()
METADATA_CACHE                       | container alias                   | ->setMetadataCache()
DOC_BLOCK_TYPE_RESOLVER              | bool                              | ->setDocBlockTypeResolver()

###### Notes:

- The expected value `container alias` means that a string is expected, which is used in the container to retrieve an 
  actual instance to set to the serializer builder. The actually needed  type of the instance can be checked in the 
  related method of the [SerializerBuilder](https://github.com/schmittjoh/serializer/blob/master/src/SerializerBuilder.php).
- The serialization and deserialization visitors get added to the builder together with their format string. The config
  is expecting the format as key, and an alias to the visitor factory as value.
- `METADATA_DIRS` expects the array key to be the namespace and the value to be the directory with the meta config
  files.
- The factory does only support classes implementing the `SubscribingHandlerInterface` as handlers, and classes 
  implementing the `EventSubscriberInterface` as listeners. It is not possible to use callables for these two cases.
