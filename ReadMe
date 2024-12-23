# Laravel Predator API Utils

This package provides a collection of utility classes and traits to streamline common Laravel development tasks, such as:

* **Error Handling:** Easily handle and format API errors.
* **Paginated Responses:** Create paginated responses with consistent structure.
* **Data Handling:** Simplify data manipulation and extraction.
* **Middleware:** Implement custom middleware for authentication, authorization, and other cross-cutting concerns.
* **Repository Pattern:** Provides a base repository class for common database operations.

## ⚠️⚠️ Disclaimer ⚠️⚠️

> This package aims to reduce boilerplate code often encountered in my Laravel projects by providing reusable components. 
> It was created based on my personal preferences and may not be suitable for all projects or development styles. 
> It is not intended to be a comprehensive solution for all Laravel development needs and may require customization for specific requirements.

## Installation

1. **Install via Composer:**

   ```bash
   composer require jerrydepredator/laravel-predator-api-utils
   ```

2. **Publish Configuration (Optional):**

   - If you want to customize the default property names for the `DecryptedJWToken` service, publish the configuration file:

     ```bash
     php artisan vendor:publish --provider="LaravelPredatorApiUtils\ServiceProvider" --tag="config" 
     ```

   - This will create a `config/decrypted_jwt_token.php` file in your project where you can modify the property names.

## Usage

### 1. DecryptedJWTToken Service:

- **Location:** `src/Services/DecryptedJWToken.php`
- **Purpose:** Provides methods to access data from a decrypted JWT token.
- **Usage:**

   ```php
   use LaravelPredatorApiUtils\Services\DecryptedJWToken;

   // In your controller
   public function showProfile(Request $request, DecryptedJWToken $decryptedJWToken)
   {
       $userId = $decryptedJWToken->getUserId(); 
       $userName = $decryptedJWToken->getUserName(); 
       $userData = $decryptedJWToken->getData(); // Get all user data

       // ... your logic using user data
   }
   ```

### 2. Middlewares:

- **DecryptJWToken:**
    - **Location:** `src/Middlewares/DecryptJWToken.php`
    - **Purpose:** Decrypts the JWT token from the `Authorization` header and adds the decrypted data to the request object.
    - **Usage:**
        - Register the middleware in `app/Http/Kernel.php`:

          ```php
          protected $routeMiddleware = [
              // ... other middleware
              'decrypt_jwt' => \App\Http\Middleware\DecryptJWToken::class,
          ];
          ```
        - Apply the middleware to routes:

          ```php
          Route::get('/protected-route', [MyController::class, 'showData'])->middleware('decrypt_jwt');
          ```

- **RoleMiddleware:**
    - **Location:** `src/Middlewares/RoleMiddleware.php`
    - **Purpose:** Restricts access to routes based on user roles.
    - **Usage:**
        - Register the middleware in `app/Http/Kernel.php`:

          ```php
          protected $routeMiddleware = [
              // ... other middleware
              'role' => \App\Http\Middleware\RoleMiddleware::class,
          ];
          ```
        - Apply the middleware to routes:

          ```php
          Route::get('/admin', [AdminController::class, 'index'])->middleware('role:admin');
          ```

### 3. Traits:

- **ApiResponse:**
    - **Location:** `src/Traits/ApiResponse.php`
    - **Purpose:** Provides helper methods for creating API responses with standardized structures.

- **HandlesErrors:**
    - **Location:** `src/Traits/HandlesErrors.php`
    - **Purpose:** Provides helper methods for handling and formatting API errors.

- **PaginationRules:**
    - **Location:** `src/Traits/PaginationRules.php`
    - **Purpose:** Provides rules for validating pagination parameters.

### 4. Repositories:

- **BaseRepository:**
    - **Location:** `src/Repositories/BaseRepository.php`
    - **Purpose:** Provides a base class for implementing repositories with common database operations.

## Configuration:

- **`decrypted_jwt_token.php`:**
    - Located in the `config` directory.
    - Defines default property names for the `DecryptedJWToken` service.
    - Can be customized to match your specific JWT token structure.

## Contributing

Contributions are welcome! Please submit pull requests to the project's GitHub repository.

## License

This package is licensed under the MIT License.
