# Upgrade from 1.2 to 2.0

## Manager

 * The `AutoRouteManager` constructor now requires an `UriContextCollectionBuilder`.

## Configuration

 * The `uri_schema` option now requires an array of uri schema's instead of a
   single string.

## Token Providers

 * The argument of the `ContentMethodProvider` constructor has been changed to
   use the new SlugifierApi interface.

 * The `configureOptions()` method argument has been changed from
   `OptionsResolverInterface` to `OptionsResolver`.

 * An empty or `/` token value will now trigger an exception. Use the
   `allow_empty` option to allow empty token values.

## Auto Routes

 * The typehint of `setRedirectTarget()` has been removed.

 * The `setAutoRouteTag()`/`getAutoRouteTag()` methods have been renamed to
   `setLocale()`/`getLocale()`.

 * The `getAutoRouteByTag()` has been renamed to `getAutoRouteByLocale()`.

## Adapters

 * `AdapterInterface::translateObject()` has been changed to return the
   translated object instead of modifying it by reference.

## Uri Context

 * The `subjectObject` and `translatedSubjectObject` with their related methods
   are renamed to `subject` and `translatedSubject`.

## Customization

A lot of previously protected fields and some methods where moved to private, 
as they are not meant to represent part of the contract. If you have a valid 
use case to extend one of the classes, please explain in a github issue and
we will see whether that property should be made protected or whether ther is a
cleaner sulution.
