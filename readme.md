# BileMo API Documentation

This is the documentation for the BileMo API. The API provides endpoints for managing products, partners (companies), and their associated customers.

## Authentication

To access the endpoints of this API, you need to authenticate by providing your email and password via the `/api/login` endpoint. Upon successful authentication, you will receive a JWT token which should be included in the headers of subsequent requests.

## Endpoints

### Products

- `/api/products`: Retrieves all products.
- `/api/products/{id}`: Retrieves a product by its ID.

### Partners

- `/api/partners`: Retrieves all partners.
- `/api/partners/{id}`: Retrieves details of a partner by its ID.
- `/api/partners/{id}/customers`: Retrieves all customers associated with a partner.
- `/api/partners/{id}/customers/{id}`: Retrieves details of a customer associated with a partner.

### Managing Customers

- `/api/partners/{id}/customers`: Adds a customer to a partner. (Requires admin privileges)
- `/api/partners/{id}/customers/{id}`: Retrieves details of a customer associated with a partner.
- `/api/partners/{id}/customers/{id}`: Deletes a customer associated with a partner. (Requires admin privileges)

## Getting Started

1. Clone the repository: `git clone https://github.com/your-repository.git`
2. Install dependencies: `composer install`
3. Set up your environment variables by copying `.env.example` to `.env` and configuring it according to your environment.
4. Run migrations to set up your database: `php bin/console doctrine:migrations:migrate`
5. Start the Symfony server: `symfony server:start`

## Testing

You can test the API endpoints using tools like Postman or curl. Ensure that you include the JWT token received during authentication in the headers of your requests to authenticated endpoints.

