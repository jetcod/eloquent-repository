<?php

namespace Jetcod\LaravelRepository\Eloquent;

use Jetcod\LaravelRepository\Exceptions\RepositoryException;
use Carbon\Carbon;
use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

abstract class BaseRepository implements EloquentRepositoryInterface
{
    protected $fillable = [];

    /**
     * @var Model
     */
    protected $model;

    /**
     * @var \Illuminate\Database\Eloquent\Builder
     */
    protected $query;

    private $container;

    /**
     * BaseRepository constructor.
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->model     = $this->loadModel();
    }

    /**
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     * @throws RepositoryException
     */
    public function query(): Builder
    {
        if ($this->query instanceof Builder) {
            return $this->query;
        }

        return $this->model->newQuery();
    }

    /**
     * Save a new model and return the instance.
     */
    public function create(array $attributes): Model
    {
        return $this->model->create($attributes);
    }

    public function updateOrCreate(array $data, array $conditions)
    {
        return $this->model->updateOrCreate($data, $conditions);
    }

    public function insert(array $rows): bool
    {
        return $this->query()->insert($rows);
    }

    public function countBy(array $conditions): int
    {
        return $this->query()->where($conditions)->count();
    }

    /**
     * @param $id
     */
    public function find($id): ?Model
    {
        return $this->model->find($id);
    }

    /**
     * Find all models.
     */
    public function findAll(): Collection
    {
        return $this->query()->get();
    }

    /**
     * Find a model with its relations.
     * 
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection|static[]|static|null
     */
    public function findWithRelations(int $id, array $relations): ?Model
    {
        return $this
            ->model
            ->with($relations)
            ->find($id)
        ;
    }

    /**
     * Find a collection of models by conditions.
     *
     * @param array $conditions Array of conditions
     * @param array $relations  An array of relations
     * @param bool  $paginate   Either paginated or not
     * @param int   $pageSize   Page size
     *
     * @return ?Collection|?LengthAwarePaginator A collection or paginated result
     */
    public function findBy(array $conditions, array $relations = [], bool $paginate = true, int $pageSize = 10)
    {
        $query = $this
            ->model
            ->with($relations)
            ->where($conditions)
        ;

        return $paginate ? $query->paginate($pageSize) : $query->get();
    }

    /**
     * Find a model by conditions.
     *
     * @param array $conditions Array of conditions
     * @param array $relations  An array of relations
     */
    public function findOneBy(array $conditions, array $relations = []): ?Model
    {
        return $this
            ->model
            ->with($relations)
            ->where($conditions)
            ->first()
        ;
    }

    /**
     * Delete a database entity.
     *
     * @param int|Model $model An entity object or its id
     *
     * @throws RepositoryException Exception if no associated model found
     */
    public function delete($model): bool
    {
        if (is_numeric($model)) {
            $model = $this->find($model)->first();
            if (!$model instanceof Model) {
                throw new RepositoryException('Model not found!');
            }
        }

        return $model->delete();
    }

    /**
     * Destroy an array or collection of ids.
     *
     * @param array|Collection $ids An array or collection of ids
     *
     * @return int Number of destroyed ids
     */
    public function destroy($ids): int
    {
        return $this->model->destroy($ids);
    }

    /**
     * Update a model by given data.
     *
     * @param Model $model    Model to be updated
     * @param array $data     Associative array of data
     * @param array $fillable Fillable columns
     *
     * @return null|Model Returns null if it fails to update the data.
     *                    Otherwise, updated model is returned
     */
    public function update(Model $model, array $data, array $fillable = []): ?Model
    {
        if (!$model instanceof Model) {
            $model = $this->find($model);
        }

        $data['updated_at'] = new Carbon('now');

        $model = $this->fill($model, $data, $fillable);

        return $model->save() ? $model : null;
    }

    /**
     * This method will fill the given model by the given array of data.
     * It will use the fillable values of the model class if the $fillable
     * parameter is not available.
     *
     * @param Model $model    The model entity
     * @param array $data     Array of data
     * @param array $fillable Fillable columns
     *
     * @return Model The model entity
     */
    public function fill(Model $model, array $data, array $fillable = []): Model
    {
        if (empty($fillable)) {
            $fillable = $this->model->getFillable();
        }

        if (!empty($fillable)) {
            // Just fill it if fillable array is not empty
            $model->fillable($fillable)->fill($data);
        }

        return $model;
    }

    public function with($relations, $callback = null): Builder
    {
        if (!empty($relations)) {
            $this->query = $this->query()->with($relations, $callback);
        }

        return $this->query();
    }

    /**
     * Returns a model namespace
     *
     * @return string Model namespace
     */
    abstract protected function getModelName();

    /**
     * Load model.
     *
     * @return mixed
     *
     * @throws RepositoryException
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    protected function loadModel(): Model
    {
        $model = $this->container->make($this->getModelName());

        if (!$model instanceof Model) {
            throw new RepositoryException(sprintf('The class %s must be an instance of %s.', get_class($this->model, Model::class)));
        }

        return $model;
    }
}
