<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2013 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\RoutingAuto\Mapping\Loader;

use Symfony\Cmf\Component\RoutingAuto\Mapping\ClassMetadata;
use Symfony\Component\Config\Loader\LoaderInterface;
use Metadata\Driver\AdvancedDriverInterface;

/**
 * @author Wouter J <wouter@wouterj.nl>
 */
class MetadataDriver implements AdvancedDriverInterface
{
    /**
     * @var LoaderInterface
     */
    protected $loader;

    /**
     * An array of resource locations.
     * Each value is an array containing the path and optional type:
     *
     * array('path' => 'path/to/file.xml', 'type' => 'xml')
     *
     * @var array
     */
    protected $resources = array();

    /**
     * Loaded metadata from the loaders.
     *
     * @var null|array
     */
    protected $loadedMetadata;

    /**
     * @param LoaderInterface $loader
     * @param array           $resources The location of all resources as 
     *                                   array values (e.g. array('path' => 'path/to/file.xml', 'type' => 'xml')
     */
    public function __construct(LoaderInterface $loader, array $resources)
    {
        $this->loader = $loader;
        $this->resources = $resources;
    }

    /**
     * {@inheritDoc}
     */
    public function loadMetadataForClass(\ReflectionClass $class)
    {
        if (null === $this->loadedMetadata) {
            $this->loadResources();
        }

        if (!isset($this->loadedMetadata[$class->name])) {
            return new ClassMetadata($class->name);
        }

        return $this->loadedMetadata[$class->name];
    }

    /**
     * {@inheritDoc}
     */
    public function getAllClassNames()
    {
        if (null === $this->loadedMetadata) {
            $this->loadResources();
        }

        return array_keys($this->loadedMetadata);
    }

    protected function loadResources()
    {
        foreach ($this->resources as $resource) {
            foreach ($this->loader->load($resource['path'], isset($resource['type']) ? $resource['type'] : null) as $metadata) {
                if (isset($this->loadedMetadata[$metadata->getClassName()])) {
                    $this->loadedMetadata[$metadata->getClassName()]->merge($metadata);

                    continue;
                }

                $this->loadedMetadata[$metadata->getClassName()] = $metadata;
            }
        }
    }
}

