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
<trstd-switch tsId="X1234567890123456789012345678901"></trstd-switch>
<script type="module" src="https://widgets.trustedshops.com/switch/switch.js"></script>
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

2. **Start development environment:**
   ```bash
   # Option 1: Local PHP server
   make start
   
   # Option 2: Docker environment
   make dev
   ```

### Development Commands

The project includes a comprehensive Makefile with the following commands:

#### 🚀 Quick Start
- `make start` - Start local PHP server
- `make stop` - Stop local PHP server
- `make restart` - Restart local server

#### 🐳 Docker Environment
- `make dev` - Start Docker with live file watching
- `make docker` - Start Docker environment (simple)
- `make docker-stop` - Stop Docker environment
- `make docker-logs` - View Docker logs

#### 🔧 Development
- `make logs` - View container logs
- `make status` - Show container status
- `make open` - Open test page in browser

#### 📚 Documentation
- `make docs` - Open documentation

### Testing

The project includes a test environment for development and testing:

1. **Start the test environment:**
   ```bash
   make start
   ```

2. **Access the test page:**
   Open `http://localhost:8081/oauth-integration-test.php` in your browser

3. **Run automated tests:**
   ```bash
   make test
   ```


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
