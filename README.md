# Community of Trust PHP Integration Library

This library provides a comprehensive PHP interface for integrating with the Community of Trust (COT) platform, facilitating seamless interactions with its services.

## Features

- Authentication and token management
- Handling of anonymous consumer data
- Encryption and security utilities
- Extensive logging capabilities

## Requirements

- PHP >= 7.2
- Composer for managing dependencies

## Installation

To install the library, run the following command in your project directory:

```sh
composer require trstd/cot-integration-library
```

## Usage

Here is a basic example of how to use the library:

```php
<?php

require_once 'vendor/autoload.php';

use TRSTD\COT\Client;

// Initialize the client
$client = new Client(
    <TSID>,
    <CLIENT_ID>,
    <CLIENT_SECRET>,
    <AUTH_STORAGE_INSTANCE> // It can be any storage option using AuthStorageInterface
);

// Example usage
// Invoke handleCallback function to handle code coming from the authentication server
$client->handleCallback();
// get anonymous consumer data for the current user
$client->getAnonymousConsumerData();
```

For more detailed examples, please refer to the `examples/` directory.

## Contributing

Contributions are welcome! Please refer to the [CONTRIBUTING.md](CONTRIBUTING.md) file for guidelines.

## License

This library is licensed under the Apache 2.0 License - see the [LICENSE](LICENSE) file for details.

## Acknowledgments

- [firebase/php-jwt](https://github.com/firebase/php-jwt) for JWT handling
- [phpseclib/phpseclib](https://github.com/phpseclib/phpseclib) for security features
- [monolog/monolog](https://github.com/Seldaek/monolog) for logging
- [phpfastcache/phpfastcache](https://github.com/PHPSocialNetwork/phpfastcache) for caching solutions
- [symfony/http-client](https://github.com/symfony/http-client) for HTTP client features

For more information, visit the [project homepage](https://github.com/trustedshops-public/cot-php-integration-library).

## Versioning

This project adheres to [Semantic Versioning](https://semver.org/). For the versions available, see the [tags on this repository](
    https://github.com/trustedshops-public/cot-php-integration-library/tags
).
