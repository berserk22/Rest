# Rest Module

The Rest module provides RESTful API capabilities for the SkeletonApp, including OAuth2-like token authentication, API user management, and API documentation support.

## Overview

The module includes:
- **OAuth2 Authentication**: Support for token-based authentication (Bearer token).
- **API User Management**: Database-backed storage for API users and their tokens.
- **API Documentation**: Integration for Swagger-based API documentation.
- **CLI Utilities**: Commands for hashing passwords for API users.
- **Middleware Integration**: Custom authentication middleware for protecting API routes.

## Requirements

- **PHP**: >= 8.2
- **SkeletonApp Core**: Integration with the base `Provider`, DI container, and Middleware system.
- **Slim Framework**: Core routing and HTTP handling.
- **Illuminate Database**: Eloquent ORM for `api_user` and `api_token` tables.
- **Symfony Console**: Support for CLI commands.

## Project Structure

- `Api/`: Placeholder for API implementations.
- `ApiController/`: Controllers for API endpoints (e.g., `IndexController` for token management).
- `Auth/`: Authentication logic, including token validation and Bearer token handling.
- `Console/`: CLI commands.
  - `Hash.php`: Command to hash strings for API secrets.
- `Controller/`: Controllers for documentation and other non-API endpoints.
- `Db/`:
  - `Models/`: Eloquent models (`ApiUser`, `ApiToken`).
  - `Schema.php`: Database migration and schema definition for `api_token` and `api_user` tables.
- `Exceptions/`: Custom exception classes for REST-related errors (Unauthorized, NotFound, etc.).
- `Manager/`: Business logic for managing REST entities.
- `ApiRouter.php`: Defines API endpoints (e.g., `/v1/token`, `/v1/check_token`).
- `Router.php`: Defines documentation endpoints (e.g., `/api-docs`).
- `RestTrait.php`: Shared utilities for REST components.
- `ServiceProvider.php`: Module initialization, service registration, and integration.

## Setup & Run Commands

The module is integrated into the SkeletonApp ecosystem.

1.  **Installation**:
    ```bash
    composer require skeleton-app/rest
    ```

2.  **Registration**:
    The module's `ServiceProvider` is automatically registered or should be added to the application bootstrap.

3.  **Database Migration**:
    Run the application's migration command to create the necessary tables:
    ```bash
    php cli migration:run
    ```

## Usage

### Authentication
The module provides an `Auth` middleware that can be used to protect routes. It supports Bearer token authentication via the `Authorization` header.

### CLI Commands
The module provides a command to hash passwords/secrets:
```bash
php cli hash:apihash <password>
```

## Configuration (Env Vars / Config)

- **apiSecret**: The module uses a hardcoded secret `apiSecret` in some components. 
- TODO: Move hardcoded secrets to `config/config.ini` or environment variables.

## Scripts

The module integrates with the main application's CLI.

### Available Commands:
- `hash:apihash`: Hashes a given string using a custom SHA-512 based logic.

## Tests

TODO: Tests are not yet implemented for this module. When added, run them from the project root:
```bash
./vendor/bin/phpunit modules/Rest/tests
```

## License

This project is licensed under a proprietary license as specified in `composer.json`.
