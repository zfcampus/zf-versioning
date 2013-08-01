<?php
/**
 * @license   http://opensource.org/licenses/BSD-2-Clause BSD-2-Clause
 */

namespace ZF\Versioning;

use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;
use Zend\Http\Request;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\RouteMatch;

class ContentTypeListener extends AbstractListenerAggregate
{
    protected $regexes = array(
        '#^application/vnd\.(?P<zf_ver_vendor>[^.]+)\.v(?P<zf_ver_version>\d+)\.(?P<zf_ver_resource>[a-zA-Z0-9_-]+)$#',
    );

    /**
     * @param EventManagerInterface $events 
     */
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach(MvcEvent::EVENT_ROUTE, array($this, 'onRoute'), -40);
    }

    /**
     * Add a regular expression to the stack
     * 
     * @param  string $regex 
     * @return self
     */
    public function addRegexp($regex)
    {
        if (!is_string($regex)) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s expects a string regular expression as an argument; received %s',
                __METHOD__,
                (is_object($regex) ? get_class($regex) : gettype($regex))
            ));
        }
        $this->regexes[] = $regex;
        return $this;
    }

    /**
     * Match against the Content-Type header and inject into the route matches
     * 
     * @param MvcEvent $e 
     */
    public function onRoute(MvcEvent $e)
    {
        $routeMatches = $e->getRouteMatch();
        if (!$routeMatches instanceof RouteMatch) {
            return;
        }

        $request = $e->getRequest();
        if (!$request instanceof Request) {
            return;
        }

        $headers = $request->getHeaders();
        if (!$headers->has('content-type')) {
            return;
        }

        $header = $headers->get('content-type');
        $value  = $header->getFieldValue();
        $parts  = explode(';', $value);

        $contentType = array_shift($parts);
        $contentType = trim($contentType);

        foreach (array_reverse($this->regexes) as $regex) {
            if (!preg_match($regex, $contentType, $matches)) {
                continue;
            }

            $this->injectRouteMatches($routeMatches, $matches);
            break;
        }
    }

    /**
     * Inject regex matches into the route matches
     * 
     * @param  RouteMatch $routeMatches 
     * @param  array $matches 
     */
    protected function injectRouteMatches(RouteMatch $routeMatches, array $matches)
    {
        foreach ($matches as $key => $value) {
            if (is_numeric($key) || is_int($key) || $value === '') {
                continue;
            }
            $routeMatches->setParam($key, $value);
        }
    }
}
