<?php

declare(strict_types=1);

namespace BluePsyduck\JmsSerializerFactory\Constant;

/**
 * The interface holding the config keys.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
interface ConfigKey
{
    public const ACCESSOR_STRATEGY = 'accessorStrategy';
    public const EXPRESSION_EVALUATOR = 'expressionEvaluator';
    public const TYPE_PARSER = 'typeParser';
    public const ANNOTATION_READER = 'annotationReader';
    public const DEBUG = 'debug';
    public const CACHE_DIR = 'cacheDir';
    public const ADD_DEFAULT_HANDLERS = 'addDefaultHandlers';
    public const HANDLERS = 'handlers';
    public const ADD_DEFAULT_LISTENERS = 'addDefaultListeners';
    public const LISTENERS = 'listeners';
    public const OBJECT_CONSTRUCTOR = 'objectConstructor';
    public const PROPERTY_NAMING_STRATEGY = 'propertyNamingStrategy';
    public const SERIALIZATION_VISITORS = 'serializationVisitors';
    public const DESERIALIZATION_VISITORS = 'deserializationVisitors';
    public const ADD_DEFAULT_SERIALIZATION_VISITORS = 'addDefaultSerializationVisitors';
    public const ADD_DEFAULT_DESERIALIZATION_VISITORS = 'addDefaultDeserializationVisitors';
    public const INCLUDE_INTERFACE_METADATA = 'includeInterfaceMetadata';
    public const METADATA_DIRS = 'metadataDirs';
    public const METADATA_DRIVER_FACTORY = 'metadataDriverFactory';
    public const SERIALIZATION_CONTEXT_FACTORY = 'serializationContextFactory';
    public const DESERIALIZATION_CONTEXT_FACTORY = 'deserializationContextFactory';
    public const METADATA_CACHE = 'metadataCache';
    public const DOC_BLOCK_TYPE_RESOLVER = 'docblockTypeResolver';
}
