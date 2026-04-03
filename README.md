## Project Description

Invoice processing API - project is used to generate invoice, can list, find invoice by invoice id and can take the summary.

# API Endpoints

Name        | Method | Endpoint
List        - GET    - api/invoices
Create      - POST   - api/invoices
Find by ID  - GET    - api/invoice/find
Summary     - GET    - api/invoices/summary

# Project Architecture 

Request -> middlewares (ratelimiting and client key) 
        -> controller -> requestFrom -> Service -> Response

Used BaseApiController and ApiResponse for set a common structure for all response.


# Project setup

1. Generate project key using artisan command   - php artisan generate:key
2. To create the database run the migration     - php artisan migration
3. Then run the project                         - php artisan serve

# Note

When calling api, have to add X-CLIENT-KEY in the header and also create a secret key and set it in the .env variable CLIENT_API_KEY.

I created the key using "openssl rand - hex 32" 
