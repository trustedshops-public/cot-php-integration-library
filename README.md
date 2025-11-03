# Community of Trust PHP Integration Library

![License](https://img.shields.io/github/license/trustedshops-public/cot-php-integration-library)
[![CircleCI](https://dl.circleci.com/status-badge/img/gh/trustedshops-public/cot-php-integration-library/tree/main.svg?style=shield)](https://dl.circleci.com/status-badge/redirect/gh/trustedshops-public/cot-php-integration-library/tree/main)

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

On the backend side:

```php
<?php

require_once 'vendor/autoload.php';

use TRSTD\COT\Client;

// Initialize the client
$client = new Client(
    <TSID>, // Trusted Shops ID - e.g. 'X1234567890123456789012345678901'
    <CLIENT_ID>, // Client ID - e.g. 'cot-switch-X1234567890123456789012345678901'
    <CLIENT_SECRET>, // Client Secret - e.g. '1234567890123456789012345678901234567890123456789012345678901234'
    <AUTH_STORAGE_INSTANCE>, // It can be any storage option implementing AuthStorageInterface - e.g. new DatabaseAuthStorage()
    <ENV> // Environment (optional) - dev, qa, or prod, defaults to prod
);

// Invoke handleCallback function to handle code coming from the authentication server
$client->handleCallback();

// Get consumer data for the current user
$consumerData = $client->getConsumerData();

// Access consumer information
if ($consumerData) {
    $firstName = $consumerData->getFirstName();
    $membershipStatus = $consumerData->getMembershipStatus();
    $membershipSince = $consumerData->getMembershipSince();
}
```

On the frontend side, place the following code in your HTML file where you want the widget to appear:

```html
<trstd-login tsId="X1234567890123456789012345678901"></trstd-login>
<script type="module" src="https://cdn.trstd-login.trstd.com/trstd-login/script.js"></script>
```

For more detailed examples, please refer to the [`examples/`](./examples/) directory.

## Development

### Prerequisites

- PHP >= 7.4
- Composer
- Docker (optional, for containerized development)

### Setup

1. **Install dependencies:**
   ```bash
   composer install
   ```

2. **Generate certificates (for HTTPS testing):**
   ```bash
   make certs
   ```

3. **Start development environment:**
   ```bash
   make dev
   ```

### Configuration

1. **Create configuration file:**
   ```bash
   cp test-environment/config.example.php test-environment/config.php
   ```

2. **Update configuration with your credentials:**
   ```php
   <?php
   return [
       'ts_id' => 'YOUR_ACTUAL_TS_ID',
       'client_id' => 'trstd-switch-YOUR_ACTUAL_TS_ID',
       'client_secret' => 'YOUR_ACTUAL_CLIENT_SECRET',
       'environment' => 'qa'
   ];
   ?>
   ```

### Development Commands

The project includes a comprehensive Makefile with the following commands:

#### 🐳 Docker Environment
- `make dev` - Start Docker with live file watching
- `make docker-stop` - Stop Docker environment

#### 🔐 Certificates
- `make certs` - Generate self-signed localhost TLS certs for HTTPS (8443)


### Testing

The project includes a test environment for development and testing:

1. **Start the test environment:**
   ```bash
   make dev
   ```

2. **Access the test page:**
   - **HTTPS (recommended)**: https://localhost:8443/oauth-integration-test.php
   - **HTTP (dev only)**: http://localhost:8081/oauth-integration-test.php

3. **Run automated tests:**
   ```bash
   make test
   ```

### Debugging

For debugging with Xdebug:

1. **Start Xdebug in your IDE first** (Cursor, VS Code, PhpStorm)
2. **Run development environment:**
   ```bash
   make dev
   ```
3. **Set breakpoints** in your PHP code
4. **Visit test page**: https://localhost:8443/oauth-integration-test.php
5. **Code will pause** at breakpoints for inspection

**Important:** Always start Xdebug in IDE before running `make dev`. The environment will attach to your IDE's debugger on `host.docker.internal:9003`.

## Contributing

Contributions are welcome! Please refer to the [CONTRIBUTING.md](CONTRIBUTING.md) file for guidelines.

## License

This library is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Acknowledgments

- [firebase/php-jwt](https://github.com/firebase/php-jwt) for JWT handling
- [phpseclib/phpseclib](https://github.com/phpseclib/phpseclib) for security features
- [monolog/monolog](https://github.com/Seldaek/monolog) for logging
- [symfony/http-client](https://github.com/symfony/http-client) for HTTP client features

## Versioning

This project adheres to [Semantic Versioning](https://semver.org/). For the versions available, see the [tags on this repository](
    https://github.com/trustedshops-public/cot-php-integration-library/tags
).

## PHP Package Repository

This library is available on [Packagist](https://packagist.org/packages/trstd/cot-integration-library).
