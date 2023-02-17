## Eloquent Repository Library

[![Latest Stable Version](http://poser.pugx.org/jetcod/eloquent-repository/v?style=for-the-badge)](https://packagist.org/packages/jetcod/eloquent-repository)
[![Total Downloads](http://poser.pugx.org/jetcod/eloquent-repository/downloads?style=for-the-badge)](https://packagist.org/packages/jetcod/eloquent-repository)
[![License](http://poser.pugx.org/jetcod/eloquent-repository/license?style=for-the-badge)](https://packagist.org/packages/jetcod/eloquent-repository)
### Requirements
* PHP ^7.3 or higher
* Laravel 5.6 or higher
* Eloquent 5.6 or higher

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