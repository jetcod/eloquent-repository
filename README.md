## Eloquent Repository Library

[![Actions Status](https://github.com/jetcod/eloquent-repository/actions/workflows/php.yml/badge.svg?style=for-the-badge&label=%3Cb%3EBuild%3C/b%3E)](https://github.com/jetcod/eloquent-repository/actions)

[![Latest Stable Version](https://img.shields.io/packagist/v/jetcod/eloquent-repository)](https://packagist.org/packages/jetcod/eloquent-repository)
[![Total Downloads](https://img.shields.io/packagist/dt/jetcod/eloquent-repository)](https://packagist.org/packages/jetcod/eloquent-repository)
[![License](https://img.shields.io/github/license/jetcod/eloquent-repository)](https://github.com/jetcod/eloquent-repository/blob/main/LICENSE)

### Requirements
* PHP ^8.0 or higher
* Laravel 9.0 or higher
* Eloquent 8.0 or higher

### Installation
You can install the library using Composer:

```sh
composer require jetcod/eloquent-repository
```
### Usage
To use the library, you need to create a repository class for each of your Eloquent models. You can either extend the **Jetcod\LaravelRepository\Eloquent\BaseRepository** class to get started or use artisan command to generate the repository class:

```sh
php artisan make:repository UserRepository
```

Here's an example of a UserRepository class:

```php
<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Jetcod\LaravelRepository\Eloquent\BaseRepository

class UserRepository extends BaseRepository
{
    protected function getModelName()
    {
        return User::class;
    }
}
```

In this example, the *UserRepository* extends the *BaseRepository* class and connects the repository to User mode by implementing `getModelName()` method . You can then use the repository to perform CRUD operations on the User model, like this:

```php
<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Jetcod\LaravelRepository\Eloquent\BaseRepository

class UserService
{
    protected $repository;

    protected function __construct(UserRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getAdmins()
    {
        return $this->repository->findBy([
            ['role', '=', 'ADMIN']
        ]);
    }
}
```

### API
The following methods are available in the BaseRepository class:

* `find(int $id)`: Find a model by ID.
* `findAll()`: Get all models.
* `findBy(array $conditions, array $relations = [], bool $paginate = true, int $pageSize = 10)`: Find all models and their identified relations by conditions. The result can ne either paginated or return as a collection.
* `findOneBy(array $conditions, array $relations = [])`: Find a model and its identified relations by conditions.
* `with($relations, $callback = null)`: Set the relationships that should be eager loaded.
* `countBy(array $conditions)`: Count models by conditions.
* `delete($model)`: Delete a model
* `insert(array $rows)`: Bulk insert data
* `create(array $attributes)`: Create a new model.
* `updateOrCreate(array $data, array $conditions)`: Create or update a record matching the attributes, and fill it with values
* `update(Model $model, array $data, array $fillable = [])`: Update an existing model.
* `fill(Model $model, array $data, array $fillable = [])`: Fill the given model by the given array of data.
* `delete($model)`: Delete a model.
* `destroy($ids)`: Destroy an array or collection of ids.
* `query()`: Query builder instance.

You can also extend the BaseRepository * class to add custom methods for your specific use case.

### Contributing
If you find any bugs or have ideas for new features, please open an issue or submit a pull request on GitHub.

### License
My Eloquent Repository Library is open-sourced software licensed under the MIT license.