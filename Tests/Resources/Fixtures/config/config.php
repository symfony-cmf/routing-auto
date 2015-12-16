<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2015 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$container->loadFromExtension('cmf_routing_auto', array(
    'auto_mapping' => false,
    'mapping' => array(
        'resources' => array(
            'Resources/config/SpecificObject.yml',
            array('path' => 'Document/Post.php', 'type' => 'annotation'),
            array('path' => 'Resources/config/foo.xml'),
        ),
    ),
    'persistence' => array(
        'phpcr' => array(
            'route_basepath' => '/routes',
        ),
    ),
));
