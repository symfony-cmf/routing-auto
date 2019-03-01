<?php

declare(strict_types=1);

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\RoutingAuto\Mapping;

use Metadata\Cache\CacheInterface;
use Metadata\MetadataFactoryInterface;

/**
 * The MetadataFactory class should be used to get the metadata for a specific
 * class.
 *
 * @author Wouter J <wouter@wouterj.nl>
 */
class MetadataFactory implements \IteratorAggregate, MetadataFactoryInterface
{
    /**
     * @var ClassMetadata[]
     */
    protected $metadatas = [];

    /**
     * @var ClassMetadata[]
     */
    protected $resolvedMetadatas = [];

    /**
     * @var CacheInterface|null
     */
    protected $cache;

    /**
     * @param ClassMetadata[] $metadatas Optional
     * @param CacheInterface  $cache     Optional
     */
    public function __construct(array $metadatas = [], CacheInterface $cache = null)
    {
        $this->metadatas = $metadatas;
        $this->cache = $cache;
    }

    /**
     * Adds an array of ClassMetadata classes.
     *
     * Caution: New ClassMetadata for the same class will be merged into the
     * existing ClassMetadata, this will override token providers for the same
     * token.
     *
     * @param ClassMetadata[] $metadatas
     */
    public function addMetadatas(array $metadatas)
    {
        foreach ($metadatas as $metadata) {
            if (isset($this->metadatas[$metadata->getClassName()])) {
                $this->metadatas[$metadata->getClassName()]->merge($metadata);
            }

            $this->metadatas[$metadata->getClassName()] = $metadata;
        }
    }

    /**
     * Tries to find the metadata for the given class.
     *
     * @param string $class
     *
     * @return ClassMetadata
     */
    public function getMetadataForClass($class)
    {
        if (!isset($this->resolvedMetadatas[$class])) {
            $this->resolveMetadata($class);
        }

        return $this->resolvedMetadatas[$class];
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->metadatas);
    }

    /**
     * Resolves the metadata of parent classes of the given class.
     *
     * @param string $class
     *
     * @throws Exception\ClassNotMappedException
     */
    protected function resolveMetadata($class)
    {
        $classFqns = array_reverse(class_parents($class));
        $classFqns[] = $class;
        $metadatas = [];
        $addedClasses = [];

        try {
            foreach ($classFqns as $classFqn) {
                foreach ($this->doResolve($classFqn, $addedClasses) as $metadata) {
                    $metadatas[] = $metadata;
                }
            }
        } catch (Exception\CircularReferenceException $e) {
            throw new Exception\CircularReferenceException(sprintf($e->getMessage(), $class), $e->getCode(), $e->getPrevious());
        }

        if (0 === \count($metadatas)) {
            throw new Exception\ClassNotMappedException($class);
        }

        $metadata = null;
        foreach ($metadatas as $data) {
            if (null === $metadata) {
                $metadata = $data;
            } else {
                $metadata->merge($data);
            }
        }

        $this->resolvedMetadatas[$class] = $metadata;
    }

    protected function doResolve($classFqn, array &$addedClasses)
    {
        $metadatas = [];

        if (\in_array($classFqn, $addedClasses, true)) {
            throw new Exception\CircularReferenceException('Circular reference detected for "%s", make sure you don\'t mix PHP extends and mapping extends.');
        }

        if (isset($this->metadatas[$classFqn])) {
            $currentMetadata = $this->metadatas[$classFqn];
            $addedClasses[] = $classFqn;
            $extendedClass = $currentMetadata->getExtendedClass();

            if (isset($this->metadatas[$extendedClass])) {
                foreach ($this->doResolve($extendedClass, $addedClasses) as $extendData) {
                    $metadatas[] = $extendData;
                }
            }
            $metadatas[] = $this->metadatas[$classFqn];
        }

        return $metadatas;
    }
}
