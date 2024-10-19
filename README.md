# JSON API Base Package for Laravel

## Table of Contents
- [Description](#description)
- [Requirements](#requirements)
- [Installation](#installation)
- [Usage](#usage)
- [Auth](#auth)
- [Error Handling](#error-handling)
- [Advanced Configuration](#advanced-configuration)
- [Contributing](#contributing)
- [License](#license)

## Description
The API Base Package for Laravel is designed to streamline API development by automating the creation of essential components. It facilitates the rapid generation of models, migrations, controllers, requests, policies, seeders, factories, and tests, significantly reducing the amount of boilerplate code developers need to write.

This package ensures that all API responses are consistently returned in **JSON** format, providing a uniform approach to error handling and data interaction across your application. With built-in validation and error handling, the package improves developer efficiency while maintaining best practices in API development.

Key features include:
- Automated generation of CRUD components for any model.
- Consistent JSON responses for error handling.
- Customizable routes and validation rules tailored to your application's needs.

The package is perfect for developers looking to set up a robust API quickly and efficiently while maintaining flexibility for future enhancements.

---

## Requirements
To use the API Base Package for Laravel, ensure that your development environment meets the following requirements:

- **PHP**: ^8.3
- **Laravel**: ^11.0
- **Composer**: Ensure you have Composer installed and properly configured.

### Recommended Environment
- A local development environment such as Laravel Valet or Homestead is recommended for seamless integration and testing.
- Familiarity with Laravel and RESTful API development practices will help you make the most out of this package.

---
## Installation

To get started with the API Base Package for Laravel, follow these steps:

1. **Create a new Laravel project** (if you haven't already):
   ```bash
   composer create-project --prefer-dist laravel/laravel my-api-projec
   ```
2. **Navigate to your project directory**:

   ```bash
   cd my-api-project
   ```
3. **Install the API Base Package:** Use Composer to require the package:
   ```bash
   composer require fenox/laravel-api-json
   ```
4. **Publish the authentication resources:** After installing the package, publish the authentication-related resources:
   ```bash
   php artisan vendor:publish --tag=fenox-api-auth
   ```
   This will create necessary request classes and controllers to handle authentication, which you can customize as needed.

5. **Run the API routes installation command** (if applicable): To set up your API routes, run:
   ```bash
   php artisan install:api
   ```
---

## Usage

The API Base Package simplifies the process of creating and managing API endpoints. To create a new API model along with its associated components, use the `make:apimodel` command.

### Example: Creating a Category API

1. **Run the command**:
   To generate the **Category** API, execute the following command:
   ```bash
   php artisan make:apimodel Category
   ```
2. **Modify the Migration File:** After generating the model and migration, navigate to the migration file in ```database/migrations/``` to add the necessary fields for your database schema. For instance, if you're adding ```name``` and ```description``` fields, your migration might look like this:
   ```php
      public function up()
      {
         Schema::create('categories', function (Blueprint $table) {
         $table->id();
         $table->string('name');
         $table->text('description')->nullable();
         $table->timestamps();
         });
      }
   ```
3. **Add `````$fillable````` Fields to the Model**: In your model file located at ```app/Models/Category.php```, ensure you add the necessary fields to the ```$fillable``` property to allow mass assignment:
   ```php
   protected $fillable = ['name', 'description']; // Example fields
   ```
4. **Define Validation Rules in Requests:** In the generated ```StoreCategoryRequest``` and ```UpdateCategoryRequest``` files located in ```app/Http/Requests/Category/```, set your validation rules. If a field is optional, use ```nullable``` or ```sometimes```:
   ```php
   public function rules(): array
   {
      return [
         'name' => 'required|string|max:255',
         'description' => 'nullable|string',
      ];
   }
   ```
5. **Setup Routes**: 
Ensure your API routes are configured properly in ```routes/api.php```. The generated routes will typically look like this:

   ```php
   Route::apiResource('categories', CategoryController::class);
   ```

   By following these steps, you can quickly set up a functional CRUD API for the Category model, ready for further customization and use.

   Remember use:
      ```bash
         php artisan migrate 
      ```
   & run:

   ```bash
      php artisan serve
   ```

---

## Auth
his package integrates Laravel Sanctum to provide simple and robust API token authentication for your application.

### Steps to Setup Authentication

1. **Publish the Auth Requests**:
   After installing the package, you can publish the authentication-related requests by running:
   ```bash
   php artisan vendor:publish --tag=fenox-api-auth
   ```
2. **Modify the User Model:** Ensure that your ```User``` model (located at ```app/Models/User.php```) uses the ```HasApiTokens``` trait. This allows the model to generate API tokens for authentication:
   ```php
   use Laravel\Sanctum\HasApiTokens;
   
   class User extends Authenticatable
   {
       use HasApiTokens, Notifiable;
       // Other model properties and methods...
   }
   ```
3. **Setup Routes:**The package will generate the necessary authentication routes automatically. You should add the following routes to your ```routes/api.php``` file to enable login and logout functionality:
   ```php
   Route::post('register', [AuthController::class, 'register']);
   Route::post('login', [AuthController::class, 'login']);
   Route::post('logout', [AuthController::class, 'logout'])->middleware("auth:sanctum");
   Route::post('update', [AuthController::class, 'update'])->middleware("auth:sanctum");
   Route::post('me', [AuthController::class, 'me'])->middleware("auth:sanctum");
   ```
---

## Error Handling

This package includes built-in error handling to ensure that all exceptions and validation errors are consistently returned in **JSON** format. This approach improves the uniformity of API responses and simplifies client-side error management.

### Common Error Responses

The following are the common error responses that the package handles:

1. **Validation Error (422)**:
   When a request fails validation, a structured error response is returned. For example:
   ```json
   {
       "status": "error",
       "message": "Validation failed",
       "errors": {
           "field": ["Error description"]
       }
   }
   ```
2. Unauthenticated (401): Returned when a user attempts to access a protected resource without proper authentication.
   ```json
   {
       "message": "You do not have permission to perform this action."
   }
   ```
3. Forbidden (403): Returned when an authenticated user does not have permission to perform a specific action.
   ```json
   {
       "message": "You do not have permission to perform this action."
   }
   ```
4. Resource Not Found (404): Returned when a requested resource or route is not found.
   ```json
   {
       "message": "The requested resource was not found."
   }
   ```
5. Method Not Allowed (405): Returned when the HTTP method used for a request is not allowed for the specified route.
   ```json
   {
       "message": "The method is not allowed for this route."
   }
   ```
6. Server Error (500): Returned for any unexpected server errors.
   ```json
   {
       "message": "An unexpected error occurred. Please try again later."
   }
   ```
---

## Advanced Configuration
      
This package provides a base structure for API development, but it can also be customized or extended depending on your project’s needs.

1. **Customizing Controllers**
 
   By default, the generated controllers extend the ```BaseApiController``` provided by this package. You can customize these controllers by overriding methods like ```index```, ```store```, ```update```, and more to add additional logic or behavior.
   
   Example: Modifying the ```CategoryController```

   ```php
   use Fenox\ApiBase\Helpers\ResponseHelper; // Ensure you import the ResponseHelper from the package
   
   public function index(): JsonResponse
   {
       $query = $this->model::query();
   
       if (request()->has('filter')) {
           $query->where('name', 'like', '%' . request('filter') . '%');
       }
   
       $results = $query->orderBy($this->sortBy)
           ->paginate($this->paginate);
   
       return ResponseHelper::success($results, 'Filtered list retrieved successfully', 200);
   }
   ```
2. **Adding New Functionality**

   You are free to extend this package by adding new commands, controllers, or services specific to your API structure.
   
   **Example: Adding a Custom Base Controller**
   
   You can extend the ```BaseApiController``` to create new functionalities shared across your API controllers. For example, you could add new methods or modify existing ones.
   
   ```php
   namespace App\Http\Controllers;
   
   use Fenox\ApiBase\Controllers\BaseApiController;
   
   class CustomApiController extends BaseApiController
   {
       public function customMethod()
       {
           // Custom logic that applies to all your API controllers
       }
   }
   ```
---

## Contributing
We would be delighted to receive contributions to improve this package and its functionality. If you find any bugs or have suggestions for new features, please report them by opening an issue on our GitHub repository. Your feedback and contributions are invaluable to us!

---

## License
This package is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

You are free to use, modify, and distribute this package under the terms of the MIT license. For more details, refer to the LICENSE file included in this repository.

---






