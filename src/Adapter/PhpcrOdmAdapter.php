<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2013 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Component\RoutingAuto\Adapter;

use Doctrine\ODM\PHPCR\DocumentManager;
use Doctrine\ODM\PHPCR\Document\Generic;
use Doctrine\Common\Util\ClassUtils;
use PHPCR\InvalidItemStateException;
use Symfony\Cmf\Component\RoutingAuto\Model\AutoRouteInterface;
use Symfony\Cmf\Component\RoutingAuto\UrlContext;
use Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Phpcr\RedirectRoute;
use Symfony\Cmf\Component\RoutingAuto\AdapterInterface;

/**
 * Adapter for PHPCR-ODM
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
class PhpcrOdmAdapter implements AdapterInterface
{
    const TAG_NO_MULTILANG = 'no-multilang';

    protected $dm;
    protected $baseRoutePath;
    protected $autoRouteFqcn;

    /**
     * @param DocumentManager $dm
     * @param string          $routeBasePath Route path for all routes
     * @param string          $autoRouteFqcn The FQCN of the AutoRoute document to use
     */
    public function __construct(DocumentManager $dm, $routeBasePath, $autoRouteFqcn = 'Symfony\Cmf\Bundle\RoutingAutoBundle\Model\AutoRoute')
    {
        $this->dm = $dm;
        $this->baseRoutePath = $routeBasePath;

        if (!class_exists($autoRouteFqcn)) {
            throw new \InvalidArgumentException(sprintf(
                'Specified PHPCR-ODM AutoRouting document of class "%s" does not exist.',
                $autoRouteFqcn
            ));
        }

        $reflection = new \ReflectionClass($autoRouteFqcn);
        if (!$reflection->isSubclassOf('Symfony\Cmf\Component\RoutingAuto\Model\AutoRouteInterface')) {
            throw new \InvalidArgumentException(sprintf('AutoRoute documents have to implement the AutoRouteInterface, "%s" does not.', $autoRouteFqcn));
        }

        $this->autoRouteFqcn = $autoRouteFqcn;
    }

    /**
     * {@inheritDoc}
     */
    public function getLocales($contentDocument)
    {
        if ($this->dm->isDocumentTranslatable($contentDocument)) {
            return $this->dm->getLocalesFor($contentDocument);
        }

        return array();
    }

    /**
     * {@inheritDoc}
     */
    public function translateObject($contentDocument, $locale)
    {
        $meta = $this->dm->getMetadataFactory()->getMetadataFor(get_class($contentDocument));
        $contentDocument = $this->dm->findTranslation($meta->getName(), $meta->getIdentifierValue($contentDocument), $locale);

        return $contentDocument;
    }

    /**
     * {@inheritDoc}
     */
    public function generateAutoRouteTag(UrlContext $urlContext)
    {
        return $urlContext->getLocale() ? : self::TAG_NO_MULTILANG;
    }

    /**
     * {@inheritDoc}
     */
    public function removeDefunctRoute(AutoRouteInterface $autoRoute, $newRoute)
    {
        $session = $this->dm->getPhpcrSession();
        try {
            $node = $this->dm->getNodeForDocument($autoRoute);
            $newNode = $this->dm->getNodeForDocument($newRoute);
        } catch (InvalidItemStateException $e) {
            // nothing ..
        }

        $session->save();
    }

    /**
     * {@inheritDoc}
     */
    public function migrateAutoRouteChildren(AutoRouteInterface $srcAutoRoute, AutoRouteInterface $destAutoRoute)
    {
        $session = $this->dm->getPhpcrSession();
        $srcAutoRouteNode = $this->dm->getNodeForDocument($srcAutoRoute);
        $destAutoRouteNode = $this->dm->getNodeForDocument($destAutoRoute);

        $srcAutoRouteChildren = $srcAutoRouteNode->getNodes();

        foreach ($srcAutoRouteChildren as $srcAutoRouteChild) {
            $session->move($srcAutoRouteChild->getPath(), $destAutoRouteNode->getPath() . '/' . $srcAutoRouteChild->getName());
        }
    }

    /**
     * {@inheritDoc}
     */
    public function removeAutoRoute(AutoRouteInterface $autoRoute)
    {
        $session = $this->dm->getPhpcrSession();
        $node = $this->dm->getNodeForDocument($autoRoute);
        $session->removeItem($node->getPath());
        $session->save();
    }

    /**
     * {@inheritDoc}
     */
    public function createAutoRoute($url, $contentDocument, $autoRouteTag)
    {
        $path = $this->baseRoutePath;
        $parentDocument = $this->dm->find(null, $path);
        $segments = preg_split('#/#', $url, null, PREG_SPLIT_NO_EMPTY);
        $headName = array_pop($segments);
        foreach ($segments as $segment) {
            $path .= '/' . $segment;
            $document = $this->dm->find(null, $path);

            if (null === $document) {
                $document = new Generic();
                $document->setParent($parentDocument);
                $document->setNodeName($segment);
                $this->dm->persist($document);
            }
            $parentDocument = $document;
        }

        $headRoute = new $this->autoRouteFqcn();
        $headRoute->setContent($contentDocument);
        $headRoute->setName($headName);
        $headRoute->setParent($document);
        $headRoute->setAutoRouteTag($autoRouteTag);

        return $headRoute;
    }

    private function buildParentPathForUrl($url)
    {

        return $document;
    }

    /**
     * {@inheritDoc}
     */
    public function createRedirectRoute(AutoRouteInterface $referringAutoRoute, AutoRouteInterface $newRoute)
    {
        $parentDocument = $referringAutoRoute->getParent();

        $redirectRoute = new RedirectRoute();
        $redirectRoute->setName($referringAutoRoute->getName());
        $redirectRoute->setRouteTarget($newRoute);
        $redirectRoute->setParent($parentDocument);

        $this->dm->persist($redirectRoute);
    }

    /**
     * {@inheritDoc}
     */
    public function getRealClassName($className)
    {
        return ClassUtils::getRealClass($className);
    }

    /**
     * {@inheritDoc}
     */
    public function compareAutoRouteContent(AutoRouteInterface $autoRoute, $contentDocument)
    {
        if ($autoRoute->getContent() === $contentDocument) {
            return true;
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function getReferringAutoRoutes($contentDocument)
    {
         return $this->dm->getReferrers($contentDocument, null, null, null, 'Symfony\Cmf\Component\RoutingAuto\Model\AutoRouteInterface');
    }

    /**
     * {@inheritDoc}
     */
    public function findRouteForUrl($url)
    {
        $path = $this->getPathFromUrl($url);

        return $this->dm->find(null, $path);
    }

    private function getPathFromUrl($url)
    {
        return $this->baseRoutePath . $url;
    }
}
