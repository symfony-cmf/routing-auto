<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2014 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Symfony\Cmf\Component\RoutingAuto\Mapping;

use Metadata\Driver\AdvancedDriverInterface;
use Metadata\AdvancedMetadataFactoryInterface;
use Metadata\Cache\CacheInterface;
use Metadata\Driver\DriverInterface;

/**
 * The MetadataFactory class should be used to get the metadata for a specific
 * class.
 *
 * @author Wouter J <wouter@wouterj.nl>
 */
class MetadataFactory implements AdvancedMetadataFactoryInterface
{
    /** 
     * @var DriverInterface
     */
    protected $driver;

    /**
     * @var ClassMetadata[]
     */
    protected $resolvedMetadatas = array();

    /**
     * @var null|CacheInterface
     */
    protected $cache;

    /**
     * @param DriverInterface $driver
     * @param CacheInterface  $cache  Optional
     */
    public function __construct(DriverInterface $driver, CacheInterface $cache = null)
    {
        $this->driver = $driver;
        $this->cache  = $cache;
    }

    /**
     * {@inheritDoc}
     */
    public function getMetadataForClass($class, \ArrayObject $addedClasses = null)
    {
        $updateCache = false;

        if (!array_key_exists($class, $this->resolvedMetadatas)) {
            $reflection = new \ReflectionClass($class);

            if (null !== $this->cache && (null !== $metadata = $this->cache->loadClassMetadataFromCache($reflection)) && $metadata->isFresh()) {
                $this->resolvedMetadatas[$class] = $metadata;
            } elseif (null !== $metadata = $this->driver->loadMetadataForClass($reflection)) {
                $this->resolvedMetadatas[$class] = $this->resolveMetadata($class, $metadata, $addedClasses);
                $updateCache = true;
            } elseif (null !== $metadata = $this->resolveMetadata($class, null, $addedClasses)) {
                $this->resolvedMetadatas[$class] = $metadata;
                $updateCache = true;
            } else {
                return null;
            }
        }

        $resolvedMetadata = $this->resolvedMetadatas[$class];

        if (null !== $this->cache && $updateCache) {
            $this->cache->putClassMetadataInCache($resolvedMetadata);
        }

        return $resolvedMetadata;
    }

    /**
     * {@inheritDoc}
     */
    public function getAllClassNames()
    {
        if (!$this->driver instanceof AdvancedDriverInterface) {
            throw new \RuntimeException('Driver is not capable of retrieving all available class names.');
        }

        return $this->driver->getAllClassNames();
    }

    /**
     * Resolves the metadata of parent classes of the given class.
     *
     * @param string        $class
     * @param ClassMetadata $rootMetadata The metadata of the parent class (if it exists)
     * @param \ArrayObject  $addedClasses An Array object containing all resolved classes in the current run
     */
    protected function resolveMetadata($class, ClassMetadata $rootMetadata = null, \ArrayObject $addedClasses = null)
    {
        if (null == $addedClasses) {
            $addedClasses = new \ArrayObject(array($class));
        }

        $classFqns = class_parents($class);

        $extend = $rootMetadata->getExtendedClass();
        if (null !== $extend) {
            $classFqns[] = $extend;
        }

        $metadata = null;
        foreach ($classFqns as $classFqn) {
            foreach ($this->doResolve($classFqn, $addedClasses) as $childMetadata) {
                if (null === $metadata) {
                    $metadata = $childMetadata;
                } else {
                    $metadata->merge($childMetadata);
                }
            }
        }

        if (null === $metadata) {
            return $rootMetadata;
        }

        $metadata->merge($rootMetadata);

        return $metadata;
    }

    protected function doResolve($classFqn, \ArrayObject $addedClasses = null)
    {
        $metadatas = array();

        if (in_array($classFqn, $addedClasses->getArrayCopy())) {
            throw new \LogicException(sprintf('Circual reference detected: %s', implode(' > ', $addedClasses->getArrayCopy()).' -> '.$classFqn));
        }

        if (null !== $currentMetadata = $this->getMetadataForClass($classFqn, $addedClasses)) {
            $addedClasses[] = $classFqn;

            if (null !== ($extend = $currentMetadata->getExtendedClass()) && null !== $this->getMetadataForClass($extend, $addedClasses)) {
                foreach ($this->doResolve($extend, $addedClasses) as $extendData) {
                    $metadatas[] = $extendData;
                }
            }
            $metadatas[] = $currentMetadata;
        }

        return $metadatas;
    }
}
