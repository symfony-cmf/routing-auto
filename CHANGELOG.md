Changelog
=========

dev-master
----------

* **2015-03-14**: [BC Break] The adapter now accepts the `UriContext` object for
                  `createRoute` and as an additional parameter to `findRouteForUri`.
* **2015-03-14**: Changed adapter interface to accept UriContext objects
                  instead of or in addition to URI strings.
* **2015-01-29**: Added SymfonyContainerParameterTokenProvider for retrieving
                  path elements from the DI container

1.0
---

* **2014-08-19**: Added validation for yaml routing auto configuration keys
* **2014-08-10**: Replaced all instances of the term URL with URI
* Separated this package from the RoutingAutoBundle
