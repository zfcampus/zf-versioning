<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZFTest\Versioning;

use PHPUnit_Framework_TestCase as TestCase;
use Zend\EventManager\EventManager;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\RouteMatch;
use ZF\Versioning\VersionListener;

class VersionListenerTest extends TestCase
{
    public function setUp()
    {
        $this->event = new MvcEvent();
        $this->event->setRouteMatch(new RouteMatch(array()));

        $this->listener = new VersionListener();
    }

    public function testAttachesToRouteEventAtNegativePriority()
    {
        $events = new EventManager();
        $events->attach($this->listener);
        $listeners = $events->getListeners('route');
        $this->assertEquals(1, count($listeners));
        $this->assertTrue($listeners->hasPriority(-41));
        $callback = $listeners->getIterator()->current()->getCallback();
        $test     = array_shift($callback);
        $this->assertSame($this->listener, $test);
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
        $this->assertInstanceOf('Zend\Mvc\Router\RouteMatch', $result);
        $this->assertEquals('Foo\V2\Rest\Bar\Controller', $result->getParam('controller'));
    }
}
