## [1.6.1](https://github.com/trustedshops-public/cot-php-integration-library/compare/1.6.0...1.6.1) (2025-12-03)

### Bug Fixes

* **SW-822:** php74 compatibility and tlciid migration ([#23](https://github.com/trustedshops-public/cot-php-integration-library/issues/23)) ([a6dc34f](https://github.com/trustedshops-public/cot-php-integration-library/commit/a6dc34f37902a54e61998e312eb8ba53e8c203cf))

## [1.6.0](https://github.com/trustedshops-public/cot-php-integration-library/compare/1.5.0...1.6.0) (2025-11-10)


### Features

* remove tsId as param from Client class ([#17](https://github.com/trustedshops-public/cot-php-integration-library/issues/17)) ([ea830cc](https://github.com/trustedshops-public/cot-php-integration-library/commit/ea830ccb72bef3c84426cafc06fd44bf3410f3a0))

## [1.5.0](https://github.com/trustedshops-public/cot-php-integration-library/compare/1.4.0...1.5.0) (2025-11-06)


### Features

* **SW-758:** Refactor `switch` references to `trstd login` across test, README, and scripts ([#19](https://github.com/trustedshops-public/cot-php-integration-library/issues/19)) ([e429978](https://github.com/trustedshops-public/cot-php-integration-library/commit/e42997891a4b4478fe002a3be0e5f05affac2a7e))


### Bug Fixes

* ensure semantic-release runs after all status checks complete ([a6bdc67](https://github.com/trustedshops-public/cot-php-integration-library/commit/a6bdc67ab4806489a73685a1c23c065707a921a5))

## [1.4.0](https://github.com/trustedshops-public/cot-php-integration-library/compare/1.3.0...1.4.0) (2025-09-11)


### Features

* do not cache consumer data to avoid security issues ([cd5ab53](https://github.com/trustedshops-public/cot-php-integration-library/commit/cd5ab531ab2be47b3392ffcd3b8fc961360f236f))

## [1.3.0](https://github.com/trustedshops-public/cot-php-integration-library/compare/1.2.2...1.3.0) (2025-09-11)


### Features

* introduce consumer data endpoint and remove deprecated anonymous data methods ([a268a7e](https://github.com/trustedshops-public/cot-php-integration-library/commit/a268a7e147eaf5987bd9c789c75d766b9c063f9e))

## [1.3.0](https://github.com/trustedshops-public/cot-php-integration-library/compare/1.2.2...1.3.0) (2025-01-XX)


### Features

* Replace anonymous endpoint with new consumer data endpoint ([#XX](https://github.com/trustedshops-public/cot-php-integration-library/issues/XX))
  * Add new `ConsumerData` class with firstName, membershipStatus, and membershipSince fields
  * Add new `getConsumerData()` method to replace `getAnonymousConsumerData()`
  * Update endpoint from `anonymous-data` to `consumer-data`
  * Update examples and documentation to use new consumer data endpoint

### Breaking Changes

* Remove deprecated `getAnonymousConsumerData()` method
* Remove deprecated `AnonymousConsumerData` class
* Remove deprecated anonymous endpoint constants

## [1.2.2](https://github.com/trustedshops-public/cot-php-integration-library/compare/1.2.1...1.2.2) (2025-07-02)


### Bug Fixes

* use sub in AuthStorageInterface and implementations as primary key ([643ac90](https://github.com/trustedshops-public/cot-php-integration-library/commit/643ac9057eb09ead27df6994979d36253912baca))

## [1.2.1](https://github.com/trustedshops-public/cot-php-integration-library/compare/1.2.0...1.2.1) (2025-06-05)


### Bug Fixes

* Ignore ENV for PHP CS fixer ([6e04808](https://github.com/trustedshops-public/cot-php-integration-library/commit/6e048080f0c67801f39e5582d3bf30b7fa240aca))
* PHPStan issues for PHP 8.4 ([edf99f4](https://github.com/trustedshops-public/cot-php-integration-library/commit/edf99f4e81211df0a5c0583eefc9b696b4b3defb))

## [1.2.0](https://github.com/trustedshops-public/cot-php-integration-library/compare/1.1.0...1.2.0) (2024-12-03)


### Features

* Add support for multiple environments ([#6](https://github.com/trustedshops-public/cot-php-integration-library/issues/6)) ([8b1f2d2](https://github.com/trustedshops-public/cot-php-integration-library/commit/8b1f2d22ece25f91a23574d5e6cb06e60180f799))

## [1.1.0](https://github.com/trustedshops-public/cot-php-integration-library/compare/1.0.2...1.1.0) (2024-08-23)


### Features

* Add new anonymous consumer data model ([7409d4a](https://github.com/trustedshops-public/cot-php-integration-library/commit/7409d4a191de4602b3e06f3b36a52207446e08d4))

## [1.0.2](https://github.com/trustedshops-public/cot-php-integration-library/compare/1.0.1...1.0.2) (2024-08-23)


### Bug Fixes

* Improve token refreshing logic to avoid null exceptions ([31a1da9](https://github.com/trustedshops-public/cot-php-integration-library/commit/31a1da974f43eedb1ba7674e739d66b14572e9f8))

## [1.0.1](https://github.com/trustedshops-public/cot-php-integration-library/compare/1.0.0...1.0.1) (2024-08-23)


### Bug Fixes

* Remove ID token if access token cannot be refreshed ([#4](https://github.com/trustedshops-public/cot-php-integration-library/issues/4)) ([53ad15d](https://github.com/trustedshops-public/cot-php-integration-library/commit/53ad15d8ed1e93bb3a26a5308bbb2fc0b1d6348f))

## 1.0.0 (2024-07-16)


### Features

* implement caching for jwks and user data ([a25afb2](https://github.com/trustedshops-public/cot-php-integration-library/commit/a25afb2ea41efe1bc2810e88f746db09d8e5414a))


### Bug Fixes

* Fix autoloading issue in Client.php ([4845a91](https://github.com/trustedshops-public/cot-php-integration-library/commit/4845a91d9860777bc562c97f9457bbc0d372bfbb))
* Fix autoloading issue in Client.php ([f4ee301](https://github.com/trustedshops-public/cot-php-integration-library/commit/f4ee301cf40848d3554024b73aa1271e8cf7a2be))
* incorrect interface ([d0ff5f3](https://github.com/trustedshops-public/cot-php-integration-library/commit/d0ff5f3f6f5869e18410518d7f7189892dceb7a1))
