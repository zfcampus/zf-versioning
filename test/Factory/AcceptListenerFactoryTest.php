<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2016 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZFTest\Versioning\Factory;

use Interop\Container\ContainerInterface;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ZF\Versioning\AcceptListener;
use ZF\Versioning\Factory\AcceptListenerFactory;

class AcceptListenerFactoryTest extends TestCase
{
    public function setUp()
    {
        $this->container = $this->prophesize(ContainerInterface::class);

        $r = new ReflectionClass(AcceptListener::class);
        $props = $r->getDefaultProperties();
        $this->defaultRegexes = $props['regexes'];
    }

    public function testCreatesEmptyAcceptListenerIfNoConfigServicePresent()
    {
        $this->container->has('config')->willReturn(false);
        $factory = new AcceptListenerFactory();
        $listener = $factory($this->container->reveal());
        $this->assertInstanceOf(AcceptListener::class, $listener);
        $this->assertAttributeSame($this->defaultRegexes, 'regexes', $listener);
    }

    public function testCreatesEmptyAcceptListenerIfNoVersioningConfigPresent()
    {
        $this->container->has('config')->willReturn(true);
        $this->container->get('config')->willReturn(['foo' => 'bar']);
        $factory = new AcceptListenerFactory();
        $listener = $factory($this->container->reveal());
        $this->assertInstanceOf(AcceptListener::class, $listener);
        $this->assertAttributeSame($this->defaultRegexes, 'regexes', $listener);
    }

    public function testCreatesEmptyAcceptListenerIfNoVersioningContentTypeConfigPresent()
    {
        $this->container->has('config')->willReturn(true);
        $this->container->get('config')->willReturn(['zf-versioning' => ['foo' => 'bar']]);
        $factory = new AcceptListenerFactory();
        $listener = $factory($this->container->reveal());
        $this->assertInstanceOf(AcceptListener::class, $listener);
        $this->assertAttributeSame($this->defaultRegexes, 'regexes', $listener);
    }

    public function testConfiguresAcceptListeneWithRegexesFromConfiguration()
    {
        $this->container->has('config')->willReturn(true);
        $this->container->get('config')->willReturn(['zf-versioning' => [
            'content-type' => [
                '#foo=bar#',
            ],
        ]]);
        $factory = new AcceptListenerFactory();
        $listener = $factory($this->container->reveal());
        $this->assertInstanceOf(AcceptListener::class, $listener);
        $this->assertAttributeContains('#foo=bar#', 'regexes', $listener);

        foreach ($this->defaultRegexes as $regex) {
            $this->assertAttributeContains($regex, 'regexes', $listener);
        }
    }
}
