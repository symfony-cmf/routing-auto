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

/**
 * Represents the definition of an auto route.
 */
class AutoRouteDefinition
{
    /**
     * @var string
     */
    private $uriSchema;

    /**
     * @var array
     */
    private $defaults = [];

    public function __construct($uriSchema, array $defaults = [])
    {
        $this->uriSchema = $uriSchema;
        $this->defaults = $defaults;
    }

    /**
     * Return the URI schema.
     *
     * @return string
     */
    public function getUriSchema()
    {
        return $this->uriSchema;
    }

    /**
     * Return the default route options.
     *
     * @return array
     */
    public function getDefaults()
    {
        return $this->defaults;
    }

    /**
     * Merge a "child" definition onto this definition.
     * When resolving metadata, the most remote anscestor is used as a base
     * and descendant metadatas are merged onto *it*.
     *
     * @param AutoRouteDefinition $definition
     */
    public function merge(self $definition)
    {
        $this->uriSchema = str_replace('{parent}', $this->uriSchema, $definition->getUriSchema());

        foreach ($definition->getDefaults() as $defaultName => $defaultValue) {
            $this->defaults[$defaultName] = $defaultValue;
        }
    }
}
