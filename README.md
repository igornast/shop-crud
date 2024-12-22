# shop-crud

## Setup ## 

Follow the steps below to set up the project:

### Build and Start Docker Containers ###

Build the `nginx` and `php` images from the provided Dockerfiles.

Start the `nginx`, `php`, and `db` services.

```shell
docker-compose up --build
```

### Generate JWT Keys ###

```shell
make jwt-keys
```

### Database ###

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