# Course Management REST API

A RESTful API built with **Laravel 12 and PHP** for managing course records, featuring CRUD operations, bulk processing, request validation, caching, and automated testing.

This project explores backend application architecture, API design, database operations, and maintainable server-side development.

## Features

- **RESTful API:** Versioned `/api/v1` endpoints for creating, retrieving, updating, and deleting courses.
- **Bulk Operations:** Update or soft-delete multiple courses in a single request, with partial-success reporting.
- **Request Validation:** Laravel Form Requests validate input and return appropriate HTTP error responses.
- **Database Integration:** Eloquent ORM, database migrations, and soft deletes.
- **Caching:** Cache-aside strategy using Laravel's cache facade, with version-based invalidation.
- **Automated Tests:** PHPUnit unit and feature tests covering CRUD operations, validation, bulk processing, and cache behavior.
- **CI:** GitHub Actions workflows for automated testing and PHP code formatting checks.
- **API Documentation:** OpenAPI 3.0 specification.

## Technology Stack

- PHP 8.2+ / Laravel 12
- Eloquent ORM
- MySQL (default local configuration)
- PHPUnit
- Laravel Pint
- GitHub Actions
- OpenAPI 3.0

## Architecture

The application separates HTTP handling from business logic.

- **CourseController:** Processes HTTP requests and builds API responses.
- **CourseService:** Implements course operations, database queries, search, and caching.
- **Course Model:** Represents course records using Eloquent, including soft deletion.
- **Form Requests:** Validate incoming data before passing it to the service layer.
- **CourseObserver:** Invalidates cached course data following database changes.

The API uses versioned cache keys to invalidate previously cached results following create, update, and delete operations.

## API Endpoints

All endpoints use the prefix `/api/v1`.

| Method | Endpoint | Description |
|---|---|---|
| GET | `/courses` | List courses, optionally filtered using `?search=` |
| GET | `/courses/{id}` | Retrieve a course |
| POST | `/courses` | Create a course |
| PUT | `/courses/{id}` | Update a course |
| DELETE | `/courses/{id}` | Soft-delete a course |
| PUT | `/courses/bulk-update?ids=1,2` | Update multiple courses |
| DELETE | `/courses/bulk-delete?ids=1,2` | Soft-delete multiple courses |

See [openapi.yaml](openapi.yaml) for the complete API specification.

## Running Locally

### Requirements

PHP, Composer, MySQL support, and a compatible local development environment. Node.js and npm are needed when building frontend assets.

### Setup

```bash
git clone https://github.com/sdsouza42/Article-Management-System---Laravel-REST-API.git
cd Article-Management-System---Laravel-REST-API

composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve
```

If the MySQL database file is absent, create `database/database.MySQL` before running migrations.

The project includes Laravel Livewire/Flux starter-kit dependencies. Installing these may require appropriate Flux credentials.

### Example Request

Create a course:

```bash
curl -X POST http://localhost:8000/api/v1/courses \
  -H "Content-Type: application/json" \
  -d '{"name":"PHP Fundamentals","description":"Introduction to PHP","price_cents":4999}'
```

Retrieve courses:

```bash
curl http://localhost:8000/api/v1/courses
```

## Testing

Run the PHPUnit suite with:

```bash
php artisan test
```

Run PHP formatting checks with:

```bash
composer test:lint
```

GitHub Actions is configured to run automated tests and formatting checks.

## Current Limitations

- The API is intended as a learning and portfolio project.
- API routes currently do not enforce user authentication or authorization.
- The project has not been hardened for public production deployment.

## Future Improvements

- Add API authentication and access controls.
- Introduce pagination for large course collections.
- Improve deployment and environment configuration.
- Expand documentation and integration testing.
