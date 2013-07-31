<?php
/**
 * @license   http://opensource.org/licenses/BSD-2-Clause BSD-2-Clause
 */

return array(
    'zf-versioning' => array(
        'vnd.mwop' => array(
            // Array of regular expressions to apply against the content-type 
            // header. All capturing expressions should be named:
            // (?P<name_to_capture>expression)
            // Default: '#^application/vnd\.(?P<zf_ver_vendor>[^.]+)\.v(?P<zf_ver_version>\d+)\.(?P<zf_ver_resource>[a-zA-Z0-9_-]+)$#'
        ),
    ),
);
