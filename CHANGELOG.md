Changelog
=========

1.1.0
-----

* **2016-03-31**: [BC Break] Moved the SymfonyContainerParameterTokenProvider to the
                  RoutingAutoBundle package

1.1.0-RC1
---------

* **2016-02-01**: [BC Break] Removed CmfCoreBundle dependency in favor of
                  SlugifierApi and changed constructor signature of `ContentMethodProvider`
                  to use the new interface.
* **2015-09-05**: "Leave Redirect" defunct route handler now migrates any children
                  of the original route.
* **2015-08-23**: `AdapterInterface::translateObject()` now has to return the
                  translated object, by reference is still supported for BC reasons.
* **2015-04-23**: Added EventDispatching adapter
* **2015-04-19**: [BC Break] Empty token values will now throw an exception
* **2015-04-19**: Added `allow_empty` option to permit empty values and
                  remove any trailing slash.
* **2015-01-29**: Added SymfonyContainerParameterTokenProvider for retrieving
                  path elements from the DI container

1.0
---

* **2014-08-19**: Added validation for yaml routing auto configuration keys
* **2014-08-10**: Replaced all instances of the term URL with URI
* Separated this package from the RoutingAutoBundle
