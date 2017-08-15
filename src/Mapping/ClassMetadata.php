<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2017 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\RoutingAuto\Mapping;

use Metadata\MergeableClassMetadata;
use Metadata\MergeableInterface;

/**
 * Holds the metadata for one class.
 *
 * @author Wouter J <wouter@wouterj.nl>
 */
class ClassMetadata extends MergeableClassMetadata
{
    /**
     * @var array
     */
    private $tokenProviders = [];

    /**
     * @var array
     */
    private $conflictResolver = ['name' => 'throw_exception', 'options' => []];

    /**
     * Defunct route handler, default to remove.
     *
     * @var array
     */
    protected $defunctRouteHandler = ['name' => 'remove'];

    /**
     * @var string
     */
    protected $extendedClass;

    /**
     * @var AutoRouteDefinition[]
     */
    protected $definitions = [];

    /**
     * Add a new auto route definition for this class.
     *
     * @param string              $name
     * @param AutoRouteDefinition $definition
     */
    public function setAutoRouteDefinition($name, AutoRouteDefinition $definition)
    {
        $this->definitions[$name] = $definition;
    }

    /**
     * Add a token provider configfuration.
     *
     * @param string $tokenName
     * @param array  $provider
     * @param bool   $override
     */
    public function addTokenProvider($tokenName, array $provider = [], $override = false)
    {
        if ('schema' === $tokenName) {
            throw new \InvalidArgumentException(sprintf('Class "%s" has an invalid token name "%s": schema is a reserved token name.', $this->name, $tokenName));
        }

        if (!$override && isset($this->tokenProvider[$tokenName])) {
            throw new \InvalidArgumentException(sprintf('Class "%s" already has a token provider for token "%s", set the third argument of addTokenProvider to true to override it.', $this->name, $tokenName));
        }

        $this->tokenProviders[$tokenName] = $provider;
    }

    /**
     * Return an associative array of token provider configurations.
     * Keys are the token provider names, values are configurations in
     * array format.
     *
     * @return array
     */
    public function getTokenProviders()
    {
        return $this->tokenProviders;
    }

    /**
     * Set the conflict resolver configuration.
     *
     * @param array
     */
    public function setConflictResolver($conflictResolver)
    {
        $this->conflictResolver = $conflictResolver;
    }

    /**
     * Return the conflict resolver configuration.
     *
     * @return array
     */
    public function getConflictResolver()
    {
        return $this->conflictResolver;
    }

    /**
     * Set the defunct route handler configuration.
     *
     * e.g.
     *
     *   array('remove', array('option1' => 'value1'))
     *
     * @param array
     */
    public function setDefunctRouteHandler($defunctRouteHandler)
    {
        $this->defunctRouteHandler = $defunctRouteHandler;
    }

    /**
     * Return the defunct route handler configuration.
     */
    public function getDefunctRouteHandler()
    {
        return $this->defunctRouteHandler;
    }

    /**
     * Extend the metadata of the mapped class with given $name.
     *
     * @param string $name
     */
    public function setExtendedClass($name)
    {
        $this->extendedClass = $name;
    }

    /**
     * Return the name of the extended class (if any).
     *
     * @return string
     */
    public function getExtendedClass()
    {
        return $this->extendedClass;
    }

    /**
     * Return the name of the subject class.
     *
     * @return string
     */
    public function getClassName()
    {
        return $this->name;
    }

    /**
     * Merges another ClassMetadata into the current metadata.
     *
     * Caution: the registered token providers will be overriden when the new
     * ClassMetadata has a token provider with the same name.
     *
     * The URL schema will be overriden, you can use {parent} to refer to the
     * previous URL schema.
     *
     * @param ClassMetadata $metadata
     */
    public function merge(MergeableInterface $metadata)
    {
        parent::merge($metadata);

        foreach ($metadata->getAutoRouteDefinitions() as $definitionName => $definition) {
            if (isset($this->definitions[$definitionName])) {
                $this->definitions[$definitionName]->merge($definition);

                continue;
            }

            $this->definitions[$definitionName] = $definition;
        }

        foreach ($metadata->getTokenProviders() as $tokenName => $provider) {
            $this->addTokenProvider($tokenName, $provider, true);
        }

        if ($defunctRouteHandler = $metadata->getDefunctRouteHandler()) {
            $this->setDefunctRouteHandler($defunctRouteHandler);
        }

        if ($conflictResolver = $metadata->getConflictResolver()) {
            $this->setConflictResolver($conflictResolver);
        }
    }

    /**
     * Return the auto route definitions for the class this metadata represents.
     *
     * @return AutoRouteDefinition[]
     */
    public function getAutoRouteDefinitions()
    {
        return $this->definitions;
    }

    /**
     * Return the auto route definition with the given name.
     *
     * @param mixed $name
     *
     * @throws InvalidArgumentException
     *
     * @return AutoRouteDefinition
     */
    public function getAutoRouteDefinition($name)
    {
        if (!isset($this->definitions[$name])) {
            throw new \InvalidArgumentException(sprintf(
                'No definition exists at index "%s" in auto route metadata for class "%s"',
                $name,
                $this->name
            ));
        }

        return $this->definitions[$name];
    }
}
