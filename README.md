# Food order challenge

This project is a simple food order built with Laravel. It allows users to place orders, checks ingredient availability, and dispatches events for order placement and stock updates.

## Installation

To install and run this project, follow these steps:

1. Clone the repository to your local machine:

    ```bash
    git clone git@github.com:ahmedelattar73/food-order-challenge.git
    ```

2. Navigate to the project directory:

    ```bash
    cd food-order-challenge
    ```

3. Install dependencies using [Composer](https://getcomposer.org/):

    ```bash
    composer install
    ```

4. Copy the `.env.example` file to `.env` and configure your environment variables:

    ```bash
    cp .env.example .env
    ```
5. Update `.env` with database credentials:

    ```bash
    DB_CONNECTION=mysql
    DB_HOST=mysql
    DB_PORT=3306
    DB_DATABASE=laravel
    DB_USERNAME=sail
    DB_PASSWORD=password
    ```

6. Generate an application key:

    ```bash
    php artisan key:generate
    ```

7. Start Laravel Sail Docker environment:

    ```bash
    ./vendor/bin/sail up -d
    ```

8. Run database migrations:

    ```bash
    ./vendor/bin/sail artisan migrate
    ```

9. Seed the database with sample data:

    ```bash
    ./vendor/bin/sail artisan db:seed
    ```

10. Run queue worker:

    ```bash
    ./vendor/bin/sail artisan queue:work
    ```


## Running Tests

To run tests, use the following command:

```bash
./vendor/bin/sail test
```

### Request Details

To place an order, send a POST request to the `/api/placeorder` endpoint with the following JSON payload:

```json
{
    "products": [
        {
            "product_id": 1,
            "quantity": 2
        }
    ]
}
```
