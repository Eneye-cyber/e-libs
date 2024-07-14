## Laravel API for Books and Authors

This Laravel project provides a well-structured and secure RESTful API for managing books and authors. It implements JWT-based authentication, robust validation, informative error handling, and a user-friendly search functionality.

**Features:**

- **API Endpoints:**
    - **Books:**
        - Create a new book
        - Retrieve a list of books (paginated)
        - Retrieve details of a specific book
        - Update a book
        - Delete a book
    - **Authors:**
        - Create a new author
        - Retrieve a list of authors (paginated)
        - Retrieve details of a specific author
        - Update an author
        - Delete an author
- **Authentication:**
    - JWT-based authentication with secure access tokens (jwt-auth)
    - API endpoints are protected, requiring authentication for CRUD operations
- **Validation and Error Handling:**
    - Rigorous input validation for book and author creation/updates
    - Detailed error messages to guide users
- **Database:**
    - MySQL database schema for storing books and authors
    - Laravel's Eloquent ORM for efficient database interaction
- **Search Functionality:**
    - Search for books by title and authors by name (case-insensitive)
- **Unit and Integration Tests:**
    - Unit tests ensure API endpoints function correctly
    - Integration tests verify database interactions and overall API flow

**Setup Instructions:**

1. **Prerequisites:**
    - PHP 8+ ([https://www.php.net/downloads.php](https://www.php.net/downloads.php))
    - Composer package manager ([https://getcomposer.org/doc/faqs/how-to-install-composer-programmatically.md](https://getcomposer.org/doc/faqs/how-to-install-composer-programmatically.md))
    - MySQL database server ([https://dev.mysql.com/downloads/mysql/](https://dev.mysql.com/downloads/mysql/))

2. **Clone or Download the Repository:**
   ```bash
   git clone https://github.com/Eneye-cyber/e-libs.git
   cd e-libs
   ```

3. **Install Dependencies:**
   ```bash
   composer install
   ```

4. **Database Configuration:**
   - Copy `.env.example` to `.env` and update database credentials:
     ```
     DB_CONNECTION=mysql
     DB_HOST=your_database_host
     DB_PORT=3306
     DB_DATABASE=your_database_name
     DB_USERNAME=your_database_user
     DB_PASSWORD=your_database_password
     ```

5. **Database Migrations:**
   ```bash
   php artisan migrate
   ```

6. **Generate App Key:**
   - Enhances security:
     ```bash
     php artisan key:generate
     ```

7. **Generate JWT Key:**
     ```bash
     php artisan jwt:secret
     ```

8. **Run Seeders for Books and Authors (Optional):**
   - Seeders to populate your database with initial test data:
     ```bash
     php artisan db:seed
     ```

**Usage:**

1. **Start the Development Server:**
   ```bash
   php artisan serve
   ```
   or

   ```bash
   php -S localhost:8000 -t public/
   ```


2. **API Documentation:** 
The API documentation (Swagger/OpenAPI) is accessible via /api/documentation for detailed endpoint usage

**Testing:**

- Unit and integration tests can be run using:
  ```bash
  php artisan test
  ```
  To avoid untracked modification to data, use a different database for testing purpose **See Below for testing Configuration**

**Deployment:**

- Refer to Laravel deployment documentation for production environments ([https://laravel.com/docs/5.2/cache](https://laravel.com/docs/5.2/cache)).

**Additional Notes:**
All Routes relating to Books, Author and search are protected routes that require authentication


**Testing Configuration**
To configure a different database for unit testing in a Laravel application and ensure that the database is reset after each test run, follow these steps:

### 1. Set Up a Separate Testing Database

First, make sure you have a separate database for testing. You can create this database using your preferred database management tool or command-line interface.

### 2. Configure Testing Database in `phpunit.xml`

Edit the `phpunit.xml` file at the root of your Laravel project:

```xml
<phpunit>
    <!-- ... other configurations ... -->
    <php>
        <env name="DB_CONNECTION" value="mysql"/>
        <env name="DB_DATABASE" value="your_testing_database_name"/>
        <env name="DB_USERNAME" value="your_testing_database_user"/>
        <env name="DB_PASSWORD" value="your_testing_database_password"/>
    </php>
</phpunit>
```

Replace `your_testing_database_name`, `your_testing_database_user`, and `your_testing_database_password` with your actual testing database credentials.


### 3. Run Your Tests


To run your tests, use the following command:

```bash
php artisan test
```
