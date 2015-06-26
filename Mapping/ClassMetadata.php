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

use Metadata\MergeableInterface;
use Metadata\MergeableClassMetadata;

/**
 * Holds the metadata for one class.
 *
 * @author Wouter J <wouter@wouterj.nl>
 */
class ClassMetadata extends MergeableClassMetadata
{
    /**
     * @var string
     */
    protected $extendedClass;

    /**
     * @var AutoRouteMetadata[]
     */
    protected $routeMetadatas;

    /**
     * Return true if this class metadata has route metadata for the given key.
     *
     * @param string $key
     * @return boolean
     */
    public function hasRouteMetadata($key)
    {
        return isset($this->routeMetadatas[$key]);
    }

    /**
     * @param string $name
     * @param RouteMetadata[] $routeMetadatas
     */
    public function __construct($name, array $routeMetadatas)
    {
        parent::__construct($name);
        $this->routeMetadatas = $routeMetadatas;
    }

    /**
     * Return the route metadata for the given key
     *
     * @param string $key
     * @return RouteMetadata
     * @throws InvalidArgumentException
     */
    public function getRouteMetadata($key)
    {
        if (!isset($this->routeMetadatas[$key])) {
            throw new \InvalidArgumentException(sprintf(
                'Unknown route metadata for key "%s", known keys: "%s"',
                $key, implode('", "', array_keys($this->routeMetadatas))
            ));
        }

        return $this->routeMetadatas[$key];
    }

    /**
     * Return the name of the extended class (if any)
     *
     * @return string
     */
    public function getExtendedClass()
    {
        return $this->extendedClass;
    }

    /**
     * Return the name of the subject class
     *
     * @return string
     */
    public function getClassName()
    {
        return $this->name;
    }

    /**
     * {@inheritDoc}
     */
    public function merge(MergeableInterface $metadata)
    {
        parent::merge($metadata);

        foreach ($this->routeMetadatas as $key => $routeMetadata) {
            if (false === $metadata->hasRouteMetadata($key)) {
                continue;
            }

            $routeMetadata->merge($metadata->getRouteMetadata($key));
        }
    }
}
