# 📦 OpenClassrooms' Project #7 - BileMo API Documentation

Welcome to the BileMo API documentation! This API provides endpoints for managing products, partners (companies), and their associated customers.

## 🔑 Authentication

To access the endpoints of this API, you need to authenticate by providing your email and password via the `/api/login` endpoint. Upon successful authentication, you will receive a JWT token which should be included in the headers of subsequent requests.

## 🚀 Getting Started

1. Clone the repository

`git clone https://github.com/ColineLopez/OpenClassrooms_Project_7_BileMo_Api.git`

`cd OpenClassrooms_Project_7_BileMo_Api`
  
2. Install Dependencies

`composer install`

3. Set up your environment variables configuring it according to your environment.

4. Run Migrations

`php bin/console doctrine:database:create`

`php bin/console doctrine:migrations:migrate`

5. Start the Symfony Server

`symfony server:start`

## 🛠️ Endpoints

### 📦 Products

- `/api/products`: Retrieves all products.
- `/api/products/{id}`: Retrieves a product by its ID.

### 🏢 Partners

- `/api/partners`: Retrieves all partners.
- `/api/partners/{id}`: Retrieves details of a partner by its ID.
- `/api/partners/{id}/customers`: Retrieves all customers associated with a partner.
- `/api/partners/{id}/customers/{id}`: Retrieves details of a customer associated with a partner.

### 👥 Managing Customers

- `/api/partners/{id}/customers`: Adds a customer to a partner. (Requires admin privileges)
- `/api/partners/{id}/customers/{id}`: Retrieves details of a customer associated with a partner.
- `/api/partners/{id}/customers/{id}`: Deletes a customer associated with a partner. (Requires admin privileges)

## 🧪 Testing

You can test the API endpoints using tools like Postman or curl. Remember to include the JWT token received during authentication in the headers of your requests to authenticated endpoints.

### Example `curl`Command

`curl -X GET "https://api.example.com/api/products" -H "Authorization: Bearer YOUR_JWT_TOKEN"`

## 🖼️ App Overview

## 🛠️ Workspace Environment

![PHP](https://img.shields.io/badge/PHP-8.1-blue)

![Symfony](https://img.shields.io/badge/Symfony-6.4-green)

