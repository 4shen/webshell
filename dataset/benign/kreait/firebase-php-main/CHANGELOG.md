# CHANGELOG

## [Unreleased]

## [5.5.0] - 2020-06-19
### Added
* It is now possible to log outgoing HTTP requests and responses to the Firebase APIs. 
  ([Documentation](https://firebase-php.readthedocs.io/en/latest/setup.html#logging))
### Changed
* `Kreait\Firebase\Factory::withEnabledDebug()` now accepts an instance of 
  `Psr\Log\LoggerInterface` as parameter to log HTTP messages.
### Deprecated
* Calling `Kreait\Firebase\Factory::withEnabledDebug()` without a Logger continues to enable Guzzle's
  default debug behaviour to print HTTP debug output to STDOUT, but will trigger a deprecation notice suggesting using a Logger instead.

## [5.4.0] - 2020-06-09
### Added
* `Kreait\Firebase\Auth::setCustomUserClaims()` as a replacement for `Kreait\Firebase\Auth::setCustomUserAttributes()`
  and `Kreait\Firebase\Auth::deleteCustomUserAttributes()`
* `Kreait\Firebase\Auth\UserRecord::$customClaims` as a replacement for 
  `Kreait\Firebase\Auth\UserRecord::$customAttributes`
### Changed
* The default branch of the GitHub repository has been renamed from `master` to `main` - if you're using `dev-master`
  as a version constraint in your `composer.json`, please update it to `dev-main`.
### Deprecated
* `Kreait\Firebase\Auth::setCustomUserAttributes()`
* `Kreait\Firebase\Auth\UserRecord::$customAttributes`
### Fixed
* Exceptions thrown by the Messaging component did not include the previous ``RequestException`` 
  ([#428](https://github.com/kreait/firebase-php/issues/428))

## [5.3.0] - 2020-05-27
### Changed
* In addition to with `getenv()`, the SDK now looks for environment variables in `$_SERVER` and `$_ENV` as well. 

## [5.2.0] - 2020-05-03
### Added
* It is now possible to retrieve the Firebase User ID directly from a `SignInResult` after a successful user sign-in 
  with `SignInResult::firebaseUserId()`

## [5.1.1] - 2020-04-16
### Fixed
* Custom Token Generation was not possible with an auto-discovered Service Account 
  ([#412](https://github.com/kreait/firebase-php/issues/412)) 

## [5.1.0] - 2020-04-06
### Added
* Fetched authentication tokens (to authenticate requests to the Firebase API) are now cached in-memory by default
  ([#404](https://github.com/kreait/firebase-php/issues/404)) 

## [5.0.0] - 2020-04-01
**If you are not using any classes or methods marked as `@deprecated` or `@internal` you should be able 
to upgrade from a 4.x release to 5.0 without changes to your code.**
### Removed
* Support for PHP `<7.2`
* Deprecated methods and classes

[Unreleased]: https://github.com/kreait/firebase-php/compare/5.5.0...HEAD
[5.5.0]: https://github.com/kreait/firebase-php/compare/5.4.0...5.5.0
[5.4.0]: https://github.com/kreait/firebase-php/compare/5.3.0...5.4.0
[5.3.0]: https://github.com/kreait/firebase-php/compare/5.2.0...5.3.0
[5.2.0]: https://github.com/kreait/firebase-php/compare/5.1.1...5.2.0
[5.1.1]: https://github.com/kreait/firebase-php/compare/5.1.0...5.1.1
[5.1.0]: https://github.com/kreait/firebase-php/compare/5.0.0...5.1.0
[5.0.0]: https://github.com/kreait/firebase-php/compare/4.44.0...5.0.0
