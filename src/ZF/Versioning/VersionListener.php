<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Versioning;

use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;
use Zend\Http\Request as HttpRequest;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\RouteMatch;

class VersionListener extends AbstractListenerAggregate
{
    /**
     * @param EventManagerInterface $events
     */
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach(MvcEvent::EVENT_ROUTE, array($this, 'onRoute'), -41);
    }

    /**
     * Determine if versioning is in the route matches, and update the controller accordingly
     *
     * @param MvcEvent $e
     */
    public function onRoute(MvcEvent $e)
    {
        $request = $e->getRequest();
        if ($request instanceof HttpRequest
            && $request->isOptions()
        ) {
            return;
        }

        $routeMatches = $e->getRouteMatch();
        if (!$routeMatches instanceof RouteMatch) {
            return;
        }

        $version = $this->getVersionFromRouteMatch($routeMatches);
        if (!$version) {
            // No version found in matches; done
            return;
        }

        $controller = $routeMatches->getParam('controller', false);
        if (!$controller) {
            // no controller; we have bigger problems!
            return;
        }

        $pattern = '#' . preg_quote('\V') . '(\d+)' . preg_quote('\\') . '#';
        if (!preg_match($pattern, $controller, $matches)) {
            // controller does not have a version subnamespace
            return;
        }

        $replacement = preg_replace($pattern, '\V' . $version . '\\', $controller);
        if ($controller === $replacement) {
            return;
        }
        $routeMatches->setParam('controller', $replacement);
        return $routeMatches;
    }

    /**
     * Retrieve the version from the route match.
     *
     * The route prototype sets "version", while the Content-Type listener sets
     * "zf_ver_version"; check both to obtain the version, giving priority to the
     * route prototype result.
     *
     * @param  RouteMatch $routeMatches
     * @return int|false
     */
    protected function getVersionFromRouteMatch(RouteMatch $routeMatches)
    {
        $version = $routeMatches->getParam('zf_ver_version', false);
        if ($version) {
            return $version;
        }
        return $routeMatches->getParam('version', false);
    }
}
