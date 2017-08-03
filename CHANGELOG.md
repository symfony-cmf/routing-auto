Changelog
=========

2.0.0-RC3
---------

* **2017-08-03**: [BC Break] Removed second argument $contentDocument from `AdapterInterface::createAutoRoute`.
                  If you call this method manually or implemented the interface, you need to adjust your code.

2.0.0-RC2
---------

* **2017-06-09**: [BC Break] Added the `AdapterInterface::compareAutoRouteLocale` method.

2.0.0-RC1
---------

* **2016-02-17**: [BC Break] Semantics for URI definitions have changed to
                  permit the definition of multiple routes, see:
                  http://symfony.com/doc/current/cmf/bundles/routing_auto/introduction.html#usage
* **2016-02-01**: [BC Break] Removed CmfCoreBundle dependency in favor of
                  SlugifierApi and changed constructor signature of `ContentMethodProvider`
                  to use the new interface.
* **2015-12-31**: [BC Break] Changed the type hint of `TokenProviderInterface::configureOptions()`
                  from `OptionsResolverInterface` to `OptionsResolver` to be compatible
                  with Symfony 3.
* **2015-12-28**: [BC Break] AutoRouteManager requires an
                  UriContextCollectionBuilder in its constructor.
* **2015-09-15**: [BC Break] Removed type hint from `AutoRouteInterface::setRedirectTarget()`
                  to allow the redirect target to be a content document.
* **2015-09-05**: "Leave Redirect" defunct route handler now migrates any children
                  of the original route.
* **2015-08-23**: `AdapterInterface::translateObject()` now has to return the
                  translated object, by reference is still supported for BC reasons.
* **2015-04-23**: Added EventDispatching adapter
* **2015-04-19**: [BC Break] Empty token values will now throw an exception
* **2015-04-19**: Added `allow_empty` option to permit empty values and
                  remove any trailing slash.
* **2015-03-14**: [BC Break] The adapter now accepts the `UriContext` object for
                  `createRoute()` and as an additional parameter to `findRouteForUri()`.
* **2015-03-14**: Changed adapter interface to accept UriContext objects
                  instead of or in addition to URI strings.
* **2015-01-29**: Added SymfonyContainerParameterTokenProvider for retrieving
                  path elements from the DI container

1.0
---

* **2014-08-19**: Added validation for yaml routing auto configuration keys
* **2014-08-10**: Replaced all instances of the term URL with URI
* Separated this package from the RoutingAutoBundle
