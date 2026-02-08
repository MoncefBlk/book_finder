# Book Finder API

This is a Laravel REST API for managing books and favorites, with Google Books API integration.

## ⚙️ Requirements

- PHP 8.2+
- Composer
- MySQL

## 🚀 Installation

1.  **Clone the repository**
    ```bash
    git clone https://github.com/MoncefBlk/book_finder.git
    cd book_finder
    ```

2.  **Install dependencies**
    ```bash
    composer install
    ```

3.  **Environment Setup**
    Copy the example env file and configure your database credentials.
    ```bash
    cp .env.example .env
    ```
    Edit `.env` and set your `DB_*` credentials.

4.  **Generate App Key**
    ```bash
    php artisan key:generate
    ```

5.  **Run Migrations**
    ```bash
    php artisan migrate
    ```

6.  **Create Admin User**
    Use the custom command to create an admin user (required for importing books).
    ```bash
    php artisan app:create-admin
    ```
    Follow the prompts to set name, email, and password.

7.  **Run the Server**
    ```bash
    php artisan serve
    ```
    The API will be available at `http://localhost:8000`.

## 📚 API Documentation

### Authentication

**Register**
```bash
curl -X POST http://localhost:8000/api/v1/register \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"name": "John Doe", "email": "john@example.com", "password": "password", "password_confirmation": "password"}'
```

**Login**
```bash
curl -X POST http://localhost:8000/api/v1/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"email": "john@example.com", "password": "password"}'
```
*Response includes the `access_token`. Use this token in the `Authorization` header for subsequent requests.*

### Books (Authenticated)

**List Books**
```bash
curl -X GET http://localhost:8000/api/v1/books \
  -H "Authorization: Bearer <your_token>" \
  -H "Accept: application/json"
```

**Search Google Books (Admin Only)**
```bash
curl -X GET "http://localhost:8000/api/v1/search?query=harry+potter" \
  -H "Authorization: Bearer <your_token>" \
  -H "Accept: application/json"
```

**Import Book (Admin Only)**
```bash
curl -X POST http://localhost:8000/api/v1/books/import \
  -H "Authorization: Bearer <your_token>" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "title": "Harry Potter",
    "author": "J.K. Rowling",
    "isbn": "9780747532743",
    "cover_url": "http://example.com/cover.jpg"
  }'
```

### Favorites (Authenticated)

**List Favorites**
```bash
curl -X GET http://localhost:8000/api/v1/favorites \
  -H "Authorization: Bearer <your_token>" \
  -H "Accept: application/json"
```

**Add to Favorites**
```bash
curl -X POST http://localhost:8000/api/v1/favorites/{book} \
  -H "Authorization: Bearer <your_token>" \
  -H "Accept: application/json"
```

**Remove from Favorites**
```bash
curl -X DELETE http://localhost:8000/api/v1/favorites/{book} \
  -H "Authorization: Bearer <your_token>" \
  -H "Accept: application/json"
```

## 🧪 Running Tests

To run the feature tests:

```bash
php artisan test
```

## 📝 Postman Collection

A Postman collection is included in the root directory: `book_finder_postman_collection.json`. You can import this file into Postman to test the API endpoints easily.
