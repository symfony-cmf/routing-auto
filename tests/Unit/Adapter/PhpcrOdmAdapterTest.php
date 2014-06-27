<?php

namespace Symfony\Cmf\Component\RoutingAuto\Tests\Unit\Adapter;

use Symfony\Cmf\Component\RoutingAuto\Tests\Unit\BaseTestCase;
use Symfony\Cmf\Component\RoutingAuto\Adapter\PhpcrOdmAdapter;
use Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Phpcr\Route;
use Symfony\Cmf\Component\RoutingAuto\Model\AutoRouteInterface;

class PhpcrOdmAdapterTest extends BaseTestCase
{
    protected $dm;
    protected $baseRoutePath;

    public function setUp()
    {
        parent::setUp();

        $this->dm = $this->prophet->prophesize('Doctrine\ODM\PHPCR\DocumentManager');
        $this->metadataFactory = $this->prophet->prophesize('Doctrine\ODM\PHPCR\Mapping\ClassMetadataFactory');
        $this->metadata = $this->prophet->prophesize('Doctrine\ODM\PHPCR\Mapping\ClassMetadata');
        $this->contentDocument = new \stdClass;
        $this->contentDocument2 = new \stdClass;
        $this->baseNode = new \stdClass;
        $this->parentRoute = new \stdClass;
        $this->route = $this->prophet->prophesize('Symfony\Cmf\Component\RoutingAuto\Model\AutoRouteInterface');

        $this->phpcrSession = $this->prophet->prophesize('PHPCR\SessionInterface');
        $this->phpcrRootNode = $this->prophet->prophesize('PHPCR\NodeInterface');
        $this->baseRoutePath = '/test';

        $this->adapter = new PhpcrOdmAdapter($this->dm->reveal(), $this->baseRoutePath, 'Symfony\Cmf\Component\RoutingAuto\Tests\Unit\Adapter\AutoRoute');
    }

    public function provideGetLocales()
    {
        return array(
            array(true, array('fr', 'de')),
            array(false),
        );
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Specified PHPCR-ODM AutoRouting document of class "Not\Existing\Class" does not exist
     */
    public function testDocumentNotExist()
    {
        new PhpcrOdmAdapter($this->dm->reveal(), $this->baseRoutePath, 'Not\Existing\Class');
    }

    /**
     * @dataProvider provideGetLocales
     */
    public function testGetLocales($isTranslateable, $locales = array())
    {
        $this->dm->isDocumentTranslatable($this->contentDocument)
            ->willReturn($isTranslateable);

        if ($isTranslateable) {
            $this->dm->getLocalesFor($this->contentDocument)
                ->willReturn($locales);
        }

        $res = $this->adapter->getLocales($this->contentDocument);
        $this->assertEquals($locales, $res);
    }

    public function provideTranslatedObject()
    {
        return array(
            array('stdClass', 'some/path', 'fr'),
        );
    }

    /**
     * @dataProvider provideTranslatedObject
     */
    public function testTranslateObject($className, $id, $locale)
    {
        $this->dm->getMetadataFactory()
            ->willReturn($this->metadataFactory->reveal());
        $this->metadataFactory->getMetadataFor($className)
            ->willReturn($this->metadata->reveal());
        $this->metadata->getName()
            ->willReturn($className);
        $this->metadata->getIdentifierValue($this->contentDocument)
            ->willReturn($id);

        $this->dm->findTranslation($className, $id, $locale)
            ->willReturn($this->contentDocument);

        $res = $this->adapter->translateObject($this->contentDocument, $locale);
        $this->assertSame($this->contentDocument, $res);
    }

    public function provideCreateRoute()
    {
        return array(
            array('/foo/bar', '/test/foo', 'bar', true)
        );
    }

    /**
     * @dataProvider provideCreateRoute
     */
    public function testCreateAutoRoute($path, $expectedParentPath, $expectedName, $parentPathExists)
    {
        $this->dm->getPhpcrSession()->willReturn($this->phpcrSession);
        $this->phpcrSession->getRootNode()->willReturn($this->phpcrRootNode);
        $this->dm->find(null, $this->baseRoutePath)->willReturn($this->baseNode);

        if ($parentPathExists) {
            $this->dm->find(null, $expectedParentPath)
                ->willReturn($this->parentRoute);
        } else {
            $this->dm->find(null, $expectedParentPath)
                ->willReturn(null);
        }

        $res = $this->adapter->createAutoRoute($path, $this->contentDocument, 'fr');
        $this->assertNotNull($res);
        $this->assertInstanceOf('Symfony\Cmf\Component\RoutingAuto\Model\AutoRouteInterface', $res);
        $this->assertEquals($expectedName, $res->getName());

        $this->assertSame($this->parentRoute, $res->getParent());
        $this->assertSame($this->contentDocument, $res->getContent());
    }

    public function testGetRealClassName()
    {
        $res = $this->adapter->getRealClassName('Class/Foo');
        $this->assertEquals('Class/Foo', $res);
    }

    public function provideCompareRouteContent()
    {
        return array(
            array(true),
            array(false),
        );
    }

    /**
     * @dataProvider provideCompareRouteContent
     */
    public function testCompareRouteContent($isMatch)
    {
        $this->route->getContent()->willReturn($this->contentDocument);
        $content = $isMatch ? $this->contentDocument : $this->contentDocument2;

        $this->adapter->compareAutoRouteContent($this->route->reveal(), $this->contentDocument);
    }

    public function testGetReferringRoutes()
    {
        $this->dm->getReferrers($this->contentDocument, null, null, null, 'Symfony\Cmf\Component\RoutingAuto\Model\AutoRouteInterface')
            ->willReturn(array($this->route));
        $res = $this->adapter->getReferringAutoRoutes($this->contentDocument);

        $this->assertSame(array($this->route->reveal()), $res);
    }

    public function testFindRouteForUrl()
    {
        $url = '/this/is/url';
        $expectedRoutes = array($this->route->reveal());

        $this->dm->find(null, $this->baseRoutePath . $url)->willReturn($expectedRoutes);

        $res = $this->adapter->findRouteForUrl($url);
        $this->assertSame($expectedRoutes, $res);
    }
}

class AutoRoute extends Route implements AutoRouteInterface
{
    const DEFAULT_KEY_AUTO_ROUTE_TAG = '_auto_route_tag';

    /** 
     * {@inheritDoc}
     */
    public function setAutoRouteTag($autoRouteTag)
    {   
        $this->setDefault(self::DEFAULT_KEY_AUTO_ROUTE_TAG, $autoRouteTag);
    }   

    /** 
     * {@inheritDoc}
     */
    public function getAutoRouteTag()
    {   
        return $this->getDefault(self::DEFAULT_KEY_AUTO_ROUTE_TAG);
    }   
}
