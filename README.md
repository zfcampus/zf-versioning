ZF Versioning
=============

[![Build Status](https://travis-ci.org/zfcampus/zf-versioning.png)](https://travis-ci.org/zfcampus/zf-versioning)
[![Coverage Status](https://coveralls.io/repos/zfcampus/zf-versioning/badge.png?branch=master)](https://coveralls.io/r/zfcampus/zf-versioning)

Introduction
------------

`zf-versioning` is a ZF2 module for automating service versioning through both URLs and
Accept/Content-Type mediatypes.  Information extracted from either the URL or mediatype
that relates to versioning will be made available in the route match object.  In situations
where a controller service name is utilizing a sub-namespace with a V(\d) as a namespace,
these controller service names will be updated with the currently matched version.

Installation
------------

Run the following `composer` command:

    composer require "zfcampus/zf-versioning:~1.0-dev"

Or, add the following to your `composer.json`, in the `require` section:

    "require": {
        "zfcampus/zf-versioning": "~1.0-dev"
    }

Run `composer update` to ensure the module is installed.  Finally add the module name to
your projects `config/application.config.php` under the `modules` key:

    <?php
    return array(
        'modules' => array(
            'ZF\Versioning',
        ),
    );


Configuration
-------------

### User Configuration ###

The top-level configuration key for user configuration of this module is `zf-versioning`

#### Key: `content-type` ####

`content-type` key is used for specifying an array of named regular expressions that will be
used in both parsing `Content-Type` and `Accept` headers for media-type based versioning
information.  A default regular expression is provided in the implementation which should
also serve as an example of what kind of regex to create for more specific parsing:

    '#^application/vnd\.(?P<zf_ver_vendor>[^.]+)\.v(?P<zf_ver_version>\d+)\.(?P<zf_ver_resource>[a-zA-Z0-9_-]+)$#'

This rule with match the following pseudo-code route:

    application/vnd.{apiname}.v{version}(.{resource})?+json

All captured parts should utilize named parameters.  A more specific example, with the top
level key would look like:

```php
return array(
    'zf-versioning' => array(
        'content-type' => array(
            '#^application/vendor\.(?P<vendor>mwop)\.v(?P<version>\d+)\.(?P<resource>status|user)$#'
        )
    )
```

#### Key: `default_version` ####

`default_version` key is the default version number to use in case a version is not provided by
the client.  `1` is the default for `default_version`.

Full Example:

```php
return array(
    'zf-versioning' => array(
        'default_version' => 1
    )
```

#### Key: `uri` ####

`uri` key is responsible for identifying which routes need to be prepended with route matching
information for URL based versioning.  This key is an array of route names that is used in the
ZF2 `router` => `routes` configuration.  If a particular route is a child route, the chain will
happen at the top-most ancestor.

The route matching segment consists of a rule of `[/v:version]` while specifying a constraint
of digits only for the version parameter.

Example:

```php
return array(
    'zf-versioning' => array(
        'uri' => array(
            'api',
            'status',
            'user'
        )
    )
);
```

### System Configuration ###

The following configuration is provided through the `module.config.php` to enable proper function
of this module:

```php
'service_manager' => array(
    'invokables' => array(
        'ZF\Versioning\VersionListener' => 'ZF\Versioning\VersionListener',
    ),
),
```


ZF2 Events
----------

`zf-versioning` provides no new events, but does provide 4 distinct listeners:

#### `ZF\Versioning\PrototypeRouteListener` #####

This listener is attached to `ModuleEvent::EVENT_MERGE_CONFIG`.  It is responsible for
iterating the routes provided in the `zf-versioning` => `uri` configuration to look for
corresponding routes in the `router` => `route` part of configuration.  When a match is
detected, this listener will apply the versioning route match configuration to the
route configuration.

#### `ZF\Versioning\VersionListener` ####

This listener is attached to the `MvcEvent::EVENT_ROUTE` at a priority of -41.  This
listener is responsible for updating controller service names that utilize a versioned
namespace naming scheme.  For example, if the currently matched route provides a controller
name such as `Foo\V1\Bar`, and the currently selected version through URL or media-type
is 4, then the controller name will be updated to `Foo\V4\Bar`;

#### `ZF\Versioning\AcceptListener` ####

This listener is attached to the `MvcEvent::EVENT_ROUTE` at a priority of -40. This
listener is responsible for parsing out information from the provided regular expressions
(see the `content-type` configuration key for details) from any `Accept` header
that is present in the request and assigning that information to the route match, with
the regex parameter names as keys.

#### `ZF\Versioning\ContentTypeListener` ####

This listener is attached to the `MvcEvent::EVENT_ROUTE` at a priority of -40. This
listener is responsible for parsing out information from the provided regular expressions
(see the `content-type` configuration key for details) from any `Content-Type` header
that is present in the request and assigning that information to the route match, with
the regex parameter names as keys.

ZF2 Services
------------

`zf-versioning` provides no unique services other than those that serve the purpose
of event listeners, namely:

- `ZF\Versioning\VersionListener`
- `ZF\Versioning\AcceptListener`
- `ZF\Versioning\ContentTypeListener`