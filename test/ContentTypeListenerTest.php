<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZFTest\Versioning;

use PHPUnit_Framework_TestCase as TestCase;
use Zend\EventManager\EventManager;
use Zend\Http\Request;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\RouteMatch;
use ZF\Versioning\ContentTypeListener;

class ContentTypeListenerTest extends TestCase
{
    public function setUp()
    {
        $this->event = new MvcEvent();
        $this->event->setRequest(new Request());
        $this->event->setRouteMatch(new RouteMatch([]));

        $this->listener = new ContentTypeListener();
    }

    public function testAttachesToRouteEventAtNegativePriority()
    {
        $events = new EventManager();
        $events->attach($this->listener);
        $listeners = $events->getListeners('route');
        $this->assertEquals(1, count($listeners));
        $this->assertTrue($listeners->hasPriority(-40));
        $callback = $listeners->getIterator()->current()->getCallback();
        $test     = array_shift($callback);
        $this->assertSame($this->listener, $test);
    }

    public function testDoesNothingIfNoRouteMatchPresentInEvent()
    {
        $event = new MvcEvent();
        $event->setRequest(new Request());
        $this->assertNull($this->listener->onRoute($event));
    }

    public function testDoesNothingIfNoRequestPresentInEvent()
    {
        $event = new MvcEvent();
        $event->setRouteMatch(new RouteMatch([]));
        $this->assertNull($this->listener->onRoute($event));
    }

    public function testInjectsNothingIfContentTypeHeaderIsMissing()
    {
        $this->assertNull($this->listener->onRoute($this->event));
    }

    public function validDefaultContentTypes()
    {
        return [
            [
                'application/vnd.mwop.v1.status',
                'mwop',
                1,
                'status',
            ],
            [
                'application/vnd.zend.v2.user',
                'zend',
                2,
                'user',
            ],
        ];
    }

    /**
     * @dataProvider validDefaultContentTypes
     */
    public function testInjectsRouteMatchesWhenContentTypeMatchesDefaultRegexp($header, $vendor, $version, $resource)
    {
        $request = $this->event->getRequest();
        $headers = $request->getHeaders();
        $headers->addHeaderLine('Content-Type', $header);

        $this->listener->onRoute($this->event);
        $routeMatch = $this->event->getRouteMatch();
        $this->assertEquals($vendor, $routeMatch->getParam('zf_ver_vendor', false));
        $this->assertEquals($version, $routeMatch->getParam('zf_ver_version', false));
        $this->assertEquals($resource, $routeMatch->getParam('zf_ver_resource', false));
    }

    public function invalidDefaultContentTypes()
    {
        return [
            'bad-prefix'                   => ['application/vendor.mwop.v1.status'],
            'bad-version'                  => ['application/vnd.zend.2.user'],
            'missing-version'              => ['application/vnd.zend.user'],
            'missing-version-and-resource' => ['application/vnd.zend'],
        ];
    }

    /**
     * @dataProvider invalidDefaultContentTypes
     */
    public function testInjectsNothingIntoRouteMatchesWhenContentTypeDoesNotMatchDefaultRegexp($header)
    {
        $request = $this->event->getRequest();
        $headers = $request->getHeaders();
        $headers->addHeaderLine('Content-Type', $header);

        $this->listener->onRoute($this->event);
        $routeMatch = $this->event->getRouteMatch();
        $this->assertFalse($routeMatch->getParam('zf_ver_vendor', false));
        $this->assertFalse($routeMatch->getParam('zf_ver_version', false));
        $this->assertFalse($routeMatch->getParam('zf_ver_resource', false));
    }

    public function validCustomContentTypes()
    {
        return [
            [
                'application/vendor.mwop.1.status',
                'mwop',
                1,
                'status',
            ],
            [
                'application/vendor.mwop.2.user',
                'mwop',
                2,
                'user',
            ],
        ];
    }

    /**
     * @dataProvider validCustomContentTypes
     */
    public function testWillInjectRouteMatchesWhenContentTypeMatchesCustomRegexp($header, $vendor, $version, $resource)
    {
        $this->listener->addRegexp(
            '#application/vendor\.(?<vendor>mwop)\.(?<version>\d+)\.(?<resource>(?:user|status))#'
        );

        $request = $this->event->getRequest();
        $headers = $request->getHeaders();
        $headers->addHeaderLine('Content-Type', $header);

        $this->listener->onRoute($this->event);
        $routeMatch = $this->event->getRouteMatch();
        $this->assertEquals('mwop', $routeMatch->getParam('vendor', false));
        $this->assertEquals($version, $routeMatch->getParam('version', false));
        $this->assertEquals($resource, $routeMatch->getParam('resource', false));
    }

    public function mixedContentTypes()
    {
        return [
            'default' => [
                'application/vnd.mwop.v1.status',
                [
                    'zf_ver_vendor'   => 'mwop',
                    'zf_ver_version'  => 1,
                    'zf_ver_resource' => 'status',
                ],
            ],
            'custom' => [
                'application/vnd.mwop.1.status',
                [
                    'vendor'   => 'mwop',
                    'version'  => 1,
                    'resource' => 'status',
                ],
            ],
        ];
    }

    /**
     * @dataProvider mixedContentTypes
     */
    public function testWillInjectRouteMatchesForFirstRegexpToMatch($header, array $matches)
    {
        $this->listener->addRegexp('#application/vnd\.(?<vendor>mwop)\.(?<version>\d+)\.(?<resource>(?:user|status))#');

        $request = $this->event->getRequest();
        $headers = $request->getHeaders();
        $headers->addHeaderLine('Content-Type', $header);

        $this->listener->onRoute($this->event);
        $routeMatch = $this->event->getRouteMatch();
        foreach ($matches as $key => $expected) {
            $this->assertEquals($expected, $routeMatch->getParam($key, false));
        }
    }
}
