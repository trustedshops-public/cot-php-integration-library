# Community of Trust PHP Integration Library

![License](https://img.shields.io/github/license/trustedshops-public/cot-php-integration-library)
[![CircleCI](https://dl.circleci.com/status-badge/img/null/trustedshops-public/cot-php-integration-library/tree/main.svg?style=svg)](https://dl.circleci.com/status-badge/redirect/null/trustedshops-public/cot-php-integration-library/tree/main)

This library provides a comprehensive PHP interface for integrating with the Community of Trust (COT) platform, facilitating seamless interactions with its services.

## Requirements

- PHP >= 7.4
- Composer for managing dependencies

## Installation

To install the library, run the following command in your project directory:

```sh
composer require trstd/cot-integration-library
```

## Usage

Here is a basic example of how to use the library:

On backend side:

```php
<?php

require_once 'vendor/autoload.php';

use TRSTD\COT\Client;

// Initialize the client
$client = new Client(
    <TSID>, // Trusted Shops ID - e.g. 'X1234567890123456789012345678901'
    <CLIENT_ID>, // Client ID - e.g. 'cot-switch-X1234567890123456789012345678901'
    <CLIENT_SECRET>, // Client Secret - e.g. '1234567890123456789012345678901234567890123456789012345678901234'
    <AUTH_STORAGE_INSTANCE> // It can be any storage option implementing AuthStorageInterface - e.g. new DatabaseAuthStorage()
);

// Invoke handleCallback function to handle code coming from the authentication server
$client->handleCallback();

// get anonymous consumer data for the current user
$client->getAnonymousConsumerData();
```

On frontend side:

```html
<trstd-switch tsId="X1234567890123456789012345678901"></trstd-switch>
<script type="module" src="https://widgets.trustedshops.com/switch/switch.js"></script>
```

For more detailed examples, please refer to the [`examples/`](./examples/) directory.

## Contributing

Contributions are welcome! Please refer to the [CONTRIBUTING.md](CONTRIBUTING.md) file for guidelines.

## License

This library is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Acknowledgments

- [firebase/php-jwt](https://github.com/firebase/php-jwt) for JWT handling
- [phpseclib/phpseclib](https://github.com/phpseclib/phpseclib) for security features
- [monolog/monolog](https://github.com/Seldaek/monolog) for logging
- [phpfastcache/phpfastcache](https://github.com/PHPSocialNetwork/phpfastcache) for caching solutions
- [symfony/http-client](https://github.com/symfony/http-client) for HTTP client features

## Versioning

This project adheres to [Semantic Versioning](https://semver.org/). For the versions available, see the [tags on this repository](
    https://github.com/trustedshops-public/cot-php-integration-library/tags
).
