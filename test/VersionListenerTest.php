<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZFTest\Versioning;

use PHPUnit_Framework_TestCase as TestCase;
use Zend\EventManager\EventManager;
use Zend\EventManager\Test\EventListenerIntrospectionTrait;
use Zend\Http\Request;
use Zend\Mvc\MvcEvent;
use ZF\Versioning\VersionListener;

class VersionListenerTest extends TestCase
{
    use EventListenerIntrospectionTrait;
    use RouteMatchFactoryTrait;

    public function setUp()
    {
        $this->event = new MvcEvent();
        $this->event->setRouteMatch($this->createRouteMatch([]));

        $this->listener = new VersionListener();
    }

    public function testAttachesToRouteEventAtNegativePriority()
    {
        $events = new EventManager();
        $this->listener->attach($events);

        $this->assertListenerAtPriority(
            [$this->listener, 'onRoute'],
            -41,
            MvcEvent::EVENT_ROUTE,
            $events
        );
    }

    public function testDoesNothingIfNoRouteMatchPresentInEvent()
    {
        $event = new MvcEvent();
        $this->assertNull($this->listener->onRoute($event));
    }

    public function testDoesNothingIfNoVersionAndNoZfVerVersionParameterInRouteMatch()
    {
        $this->assertNull($this->listener->onRoute($this->event));
    }

    public function testDoesNothingIfNoControllerParameterInRouteMatch()
    {
        $matches = $this->event->getRouteMatch();
        $matches->setParam('version', 2);
        $this->assertNull($this->listener->onRoute($this->event));
    }

    public function testDoesNothingIfControllerHasNoVersionNamespace()
    {
        $matches = $this->event->getRouteMatch();
        $matches->setParam('version', 2);
        $matches->setParam('controller', 'Foo\Bar\Controller');
        $this->assertNull($this->listener->onRoute($this->event));
    }

    public function testDoesNothingIfVersionAndControllerVersionNamespaceAreSame()
    {
        $matches = $this->event->getRouteMatch();
        $matches->setParam('version', 2);
        $matches->setParam('controller', 'Foo\V2\Rest\Bar\Controller');
        $this->assertNull($this->listener->onRoute($this->event));
    }

    public function testAltersControllerVersionNamespaceToReflectVersion()
    {
        $matches = $this->event->getRouteMatch();
        $matches->setParam('version', 2);
        $matches->setParam('controller', 'Foo\V1\Rest\Bar\Controller');
        $result = $this->listener->onRoute($this->event);
        $this->assertInstanceOf($this->getRouteMatchClass(), $result);
        $this->assertEquals('Foo\V2\Rest\Bar\Controller', $result->getParam('controller'));
    }

    /**
     * @group 12
     */
    public function testAltersControllerVersionNamespaceToReflectVersionForOptionsRequests()
    {
        $request = $this->prophesize(Request::class);
        $request->isOptions()->shouldNotBeCalled();

        $this->event->setRequest($request->reveal());

        $matches = $this->event->getRouteMatch();
        $matches->setParam('version', 2);
        $matches->setParam('controller', 'Foo\V1\Rest\Bar\Controller');
        $result = $this->listener->onRoute($this->event);
        $this->assertInstanceOf($this->getRouteMatchClass(), $result);
        $this->assertEquals('Foo\V2\Rest\Bar\Controller', $result->getParam('controller'));
    }
}
