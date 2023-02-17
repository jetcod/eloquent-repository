<?php

namespace Jetcod\LaravelRepository\Test\Stubs;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Jetcod\LaravelRepository\Eloquent\BaseRepository;
use Mockery as m;

class TestRepository extends BaseRepository
{
    protected function loadModel(): Model
    {
        $paginator = m::mock(LengthAwarePaginator::class);

        $this->query = m::mock(Builder::class);
        $this->query->shouldReceive('paginate')->andReturn($paginator);

        $this->model = m::mock($this->getModelName())->makePartial();
        $this->model->shouldReceive('newQuery')->andReturn($this->query);

        return $this->model;
    }

    protected function getModelName()
    {
        return TestModel::class;
    }
}
