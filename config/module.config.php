<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

return array(
    'zf-versioning' => array(
        'content-type' => array(
            // Array of regular expressions to apply against the content-type
            // header. All capturing expressions should be named:
            // (?P<name_to_capture>expression)
            // Default: '#^application/vnd\.(?P<zf_ver_vendor>[^.]+)\.v(?P<zf_ver_version>\d+)\.(?P<zf_ver_resource>[a-zA-Z0-9_-]+)$#'
            //
            // Example:
            // '#^application/vendor\.(?P<vendor>mwop)\.v(?P<version>\d+)\.(?P<resource>status|user)$#',
        ),
        // Default version number to use if none is provided by the API consumer. Default: 1
        'default_version' => 1,
        'uri' => array(
            // Array of routes that should prepend the "zf-versioning" route
            // (i.e., "/v:version"). Any route in this array will be chained to
            // that route, but can still be referenced by their route name.
            //
            // If the route is a child route, the chain will happen against the
            // top-most ancestor.
            //
            // Example:
            //     "api", "status", "user"
            //
            // would chain the above named routes, and version them.
        ),
    ),
    'service_manager' => array(
        'invokables' => array(
            'ZF\Versioning\VersionListener' => 'ZF\Versioning\VersionListener',
        ),
    ),
);
