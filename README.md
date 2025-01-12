# shop-crud

![PHPStan](https://img.shields.io/badge/PHPStan-Level%206-brightgreen)
[![codecov](https://codecov.io/gh/igornast/shop-crud/graph/badge.svg?token=1HZCNIWEF4)](https://codecov.io/gh/igornast/shop-crud)

---

> ## About This Project
> This repository was created as part of a recruitment process.
> The project is for demonstration purposes only and should not be used in a production environment.

---
## Setup ## 

Follow the steps below to set up the project:

#### Build and Start Docker Containers ####

Build the `nginx` and `php` images from the provided Dockerfiles.

Start the `nginx`, `php`, and `db` services.

```shell
docker-compose up --build
```

#### Generate JWT Keys ####

```shell
make jwt-keys
```

#### Database ####

To initialize or reset the database 
(drop, recreate, update schema, and load fixtures), use the following command:

```shell
make db-reset
```
This will:

* Drop the existing database if it exists.
* Create a new database.
* Update the schema to match the current entities.
* Load predefined data fixtures.

### URLs

The following URLs are available in the system:

- **API Documentation**:  
  [http://localhost/api](http://localhost/api)  
  Provides detailed API documentation, including available endpoints, request/response schemas, and authentication requirements.

- **Login Endpoint**:  
  [http://localhost/auth](http://localhost/auth)  
  Use this endpoint to obtain a JWT token for authentication.  (Available int the API documentation)
  Example request:
  ```bash
  curl -X 'POST' \
    'http://localhost/auth' \
    -H 'accept: application/json' \
    -H 'Content-Type: application/json' \
    -d '{
    "email": "admin@example.com",
    "password": "password"
  }'

## API Resources

### Customers
- **Base Path**: `/api/customers`
- Manage customer-related data, including fetching, and updating, and deleting customer records.

### Orders
- **Base Path**: `/api/orders`
- Handle order-related operations, such as creating orders, retrieving order details, updating, and deleting orders.

### Products
- **Base Path**: `/api/products`
- Manage product information, including retrieving product details, updating, and deleting product records.

## User Roles ##
The system supports two user roles:

1. Admin User (`ROLE_ADMIN`):

* Has access to all API resources and operations.
* Can perform administrative tasks such as creating, updating, and deleting customers, products, and orders.

Regular User (`ROLE_USER`):

* Has limited access to API resources.
* Can perform user-specific tasks like creating and viewing orders but cannot perform administrative operations.

## Default Customer Accounts ## 
When loading fixtures, the system creates two default customer accounts:

| Name           | Email               | Role         | Password  |
|----------------|---------------------|--------------|-----------|
| Admin User     | admin@example.com   | ROLE_ADMIN   | password  |
| Regular User   | user@example.com    | ROLE_USER    | password  |

- **Password for Both Accounts**: `password`
- These accounts can be used for testing and development purposes.

## API Documentation

The API documentation provides detailed information about all available endpoints, including:
- HTTP methods
- Request and response formats
- Authentication and authorization requirements

You can access the API documentation at:

[http://localhost/api](http://localhost/api)

## **JWT Setup**
This project uses **JWT (JSON Web Tokens)** for authentication, 
managed by the LexikJWTAuthenticationBundle. T
o generate the required private and public keys for JWT, simply run the following command:

```shell
make jwt-keys
```

This command will generate the keys in the config/jwt directory and set the appropriate permissions automatically.

## **Tests** ##
To run all tests (code style, static analysis, architecture, and PHPUnit), use the following Composer command:

```shell
composer test
```

This command executes the following tasks:

* **Code Style Check**: Ensures the codebase adheres to defined coding standards.
* **Static Analysis**: Analyzes the code for potential bugs and type errors.
* **Architecture Tests**: Validates architectural constraints.
* **Unit Tests**: Runs PHPUnit for unit tests.
* **Functional Tests**: Runs PHPUnit for functional tests.