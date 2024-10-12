# API Base Package for Laravel

## Description

The API Base Package for Laravel simplifies API development by automating the creation of essential components such as models, migrations, controllers, requests, policies, seeders, factories, and tests. The package ensures all API responses are consistently returned in **JSON** format, making error handling and data interaction uniform across the application.

---

## Requirements

- **PHP**: ^8.3
- **Laravel**: ^11.0
- **Composer**: Ensure you have Composer installed and properly configured.

---

## Installation

### 1. Create a new Laravel project

If you don't already have a Laravel project, you can create one using the following command:

```bash
composer create-project --prefer-dist laravel/laravel my-api-project
```
### 2. Install the package via Composer

After setting up your Laravel project, require this package with Composer:

```bash
composer require fenox/api-base
```

### 3. Generate the API routes

Remember to manually run the following command to generate the API routes:

```bash
php artisan install:api
```

---

## Usage

To quickly create a model, along with its migration, requests, controller, policy, seeder, factory, and test, use the `make:apimodel` command provided by the package.

### Example: Creating a Category API

1. Run the following command to generate the **Category** API:

   ```bash
   php artisan make:apimodel Category

---
### 2. Modify the migration file

After generating the model and migration, modify the migration file in `database/migrations/` to add the necessary fields for your database schema.

For example, if you are adding `name` and `description` fields, your migration might look like this:

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
### 3. Add `$fillable` fields to the model

In your model file (`app/Models/Category.php`), add the necessary fields to the `$fillable` property. This ensures that the model allows mass assignment for these fields:

```php
protected $fillable = ['name', 'description']; // Example fields
```
---

### 4. Define validation rules in requests

In the generated `StoreCategoryRequest` and `UpdateCategoryRequest` files located in `app/Http/Requests/Category/`, define your validation rules. If a field is optional, use `nullable` or `sometimes`:

```php
public function rules(): array
{
    return [
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
    ];
}
```

### 5. Define your routes

After creating the model, controller, and requests using the ```make:apimodel``` command, you need to define the routes in the ```routes/api.php``` file.

### Example: Defining Routes for the Category API
Add the following code to your ```routes/api.php``` file to set up the routes for the Category API:

```php
use App\Http\Controllers\Category\CategoryController;

// Define API routes for categories
Route::apiResource('categories', CategoryController::class);

```
### Explanation
- ```apiResource```: This method creates the standard API routes for the resource, including:
  - ```GET /api/categories```: Retrieve a list of categories.
  - ```GET /api/categories/{id}```: Retrieve a specific category by its ID.
  - ```POST /api/categories```: Create a new category.
  - ```PUT /api/categories/{id}```: Update an existing category.
  - ```DELETE /api/categories/{id}```: Delete a specific category.

---

## Controller Explanation

The ```CategoryController``` is automatically generated when you run the ```make:apimodel Category``` command. This controller is located in the ```app/Http/Controllers/Category/``` directory.


```php
<?php

namespace App\Http\Controllers\Category;

use App\Http\Requests\Service\StoreCategoryRequest;
use App\Http\Requests\Service\UpdateCategoryRequest;
use App\Models\Category;
use Fenox\ApiBase\Controllers\BaseApiController;

class CategoryController extends BaseApiController
{
    protected $model = Category::class;
    protected string $sortBy = 'name';
    protected int $paginate = 10; // set to 0 if you don't want paginate
    protected $storeRequest = StoreCategoryRequest::class;
    protected $updateRequest = UpdateCategoryRequest::class;
}
```
**Key Components**

1. Model:

   - The `````$model````` property is set to ```Category::class```, which indicates that this controller will manage instances of the ```Category``` model.

2. Sorting
    - The ```protected string $sortBy = 'name';``` line specifies that the default sorting for lists of categories will be by the ```name``` field. This can be changed to any other field you wish to sort by.

3. Pagination
    - The ```protected int $paginate = 10;``` line sets the number of results returned per page. If you prefer not to use pagination, set this value to ```0```.

4. Request Validation
    - The ```protected $storeRequest = StoreCategoryRequest::class;``` and ```protected $updateRequest = UpdateCategoryRequest::class;``` lines specify which request validation classes will be used for creating and updating categories, respectively. These can be modified to use different request classes if desired.

## Example Usage

To utilize the ```CategoryController```, ensure that the necessary routes are defined in your ```routes/api.php``` file:

```php
use App\Http\Controllers\Category\CategoryController;

// Define API routes for categories
Route::apiResource('categories', CategoryController::class);
```
## Customizing Pagination and Sorting

- **Changing the Sort Field**: To sort by a different field, simply change the value of `````$sortBy`````. For example, to sort by ```created_at```, update it as follows:

```php
protected string $sortBy = 'created_at'; // Sort by created_at field

```
- **Disabling Pagination**: If you want to return all results without pagination, set the `````$paginate````` property to ```0```:
```php
protected int $paginate = 0; // Disable pagination

```

---

## Testing

This package includes functionality to test your API endpoints and ensure that all routes work as expected. Generated test files are located in the `tests/Feature/` directory.

### Running the Tests

To run the tests, use the following Artisan command:

```bash
php artisan test
```

This command will execute all tests in your Laravel project, including the tests generated by this package.

**Example Test for the Category API**

When you create a new API model for ```Category``` using the command:

```bash 
php artisan make:apimodel Category
```

A test file named ```CategoryTest.php``` will be automatically created in the ```tests/Feature/``` directory.

You will need to modify the generated ```CategoryTest.php``` file to include tests for CRUD operations, validation, and any other business logic you want to validate. Below is an example of how you might implement the test file to include basic tests for creating, reading, updating, and deleting categories:

```php
namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Category;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_create_a_category()
    {
        $response = $this->postJson('/api/categories', [
            'name' => 'Test Category',
            'description' => 'A description for the test category.'
        ]);

        $response->assertStatus(201)
                 ->assertJson(['message' => 'Record created successfully']);

        $this->assertDatabaseHas('categories', [
            'name' => 'Test Category',
            'description' => 'A description for the test category.'
        ]);
    }

    /** @test */
    public function it_can_list_categories()
    {
        $category = Category::create([
            'name' => 'Test Category',
            'description' => 'A description for the test category.'
        ]);

        $response = $this->getJson('/api/categories');

        $response->assertStatus(200)
                 ->assertJsonFragment(['name' => 'Test Category']);
    }

    /** @test */
    public function it_can_update_a_category()
    {
        $category = Category::create([
            'name' => 'Old Category',
            'description' => 'Old description.'
        ]);

        $response = $this->putJson("/api/categories/{$category->id}", [
            'name' => 'Updated Category',
            'description' => 'Updated description.'
        ]);

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Record updated successfully']);

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Updated Category',
            'description' => 'Updated description.'
        ]);
    }

    /** @test */
    public function it_can_delete_a_category()
    {
        $category = Category::create([
            'name' => 'Category to Delete',
            'description' => 'This category will be deleted.'
        ]);

        $response = $this->deleteJson("/api/categories/{$category->id}");

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Record deleted successfully']);

        $this->assertDeleted($category);
    }

    /** @test */
    public function it_validates_name_field_when_creating()
    {
        $response = $this->postJson('/api/categories', [
            'description' => 'A description without a name.'
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['name']);
    }

    /** @test */
    public function it_validates_name_field_when_updating()
    {
        $category = Category::create([
            'name' => 'Valid Category',
            'description' => 'A valid description.'
        ]);

        $response = $this->putJson("/api/categories/{$category->id}", [
            'description' => 'Updated description without a name.'
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['name']);
    }
}
```
**Adding More Tests**

You can add more test cases as needed to verify that all routes function correctly, including handling various validation scenarios and edge cases. By running the tests, you ensure that your API behaves as expected and is ready for production use.
## Error Handling

This package includes built-in error handling to ensure that all exceptions and validation errors are returned in **JSON** format. Common errors like `404 Not Found`, `401 Unauthenticated`, `403 Forbidden`, and `422 Validation Error` are handled with the appropriate HTTP status codes.

### Example of error response for validation failure:

```json
{
  "status": "error",
  "message": "Validation failed",
  "errors": {
    "name": ["The name field is required."]
  }
}
```
### 1. 401 Unauthenticated

Returned when the user is not authenticated and tries to access a protected resource.

#### Example response:

```json
{
  "message": "Unauthenticated"
}
```
### 2. 403 Forbidden

Returned when the user is authenticated but does not have permission to perform an action.

#### Example response:

```json
{
  "message": "Forbidden"
}
```

### 3. 404 Not Found

Returned when a requested resource or route is not found.

#### Example response:

```json
{
  "message": "Route not found"
}
```

### 4. 422 Validation Error

Returned when the provided data does not pass validation rules.

#### Example response:

```json
{
  "status": "error",
  "message": "Validation failed",
  "errors": {
    "field": ["Error description"]
  }
}
```

## Advanced Configuration

This package provides a base structure for API development, but it can also be customized or extended depending on your project’s needs.

### 1. Customizing Controllers

By default, the generated controllers extend the `BaseApiController` provided by this package. You can customize these controllers by overriding methods like `index`, `store`, `update`, and more to add additional logic or behavior.

For example, you could modify the `CategoryController` to add custom query filtering:

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

### 2. Extending Validation Rules

You can customize validation rules in the `StoreRequest` or `UpdateRequest` files for each model. These files are generated in the `app/Http/Requests/Category/` directory (or the corresponding directory for the model).

In these request files, you can define specific validation rules for fields. For example:

```php
public function rules(): array
{
    return [
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
    ];
}
```
If more complex validation is required, you can inject additional logic or services into these request files, such as using the sometimes rule or custom validation classes.
### Example of adding more complex rules:
```php
public function rules(): array
{
    return [
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|string|min:8|confirmed',
        'age' => ['required', 'integer', new AgeValidationRule],
    ];
}
```

In this example, a custom validation rule (AgeValidationRule) is applied to the age field, and confirmed is used for password confirmation.

### 3. Adding New Functionality

You are free to extend this package by adding new commands, controllers, or services specific to the API structure provided by the package.

#### Example: Adding a Custom Base Controller

You can extend the `BaseApiController` to create new functionalities shared across your API controllers. For example, you could add new methods or modify the behavior of existing ones.

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

## Contributing

We welcome contributions to improve this package and its functionality. If you have ideas or improvements, feel free to submit a pull request or open an issue on the GitHub repository.

## License

This package is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

You are free to use, modify, and distribute this package under the terms of the MIT license. For more details, refer to the LICENSE file included in this repository.




