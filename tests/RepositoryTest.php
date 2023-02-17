<?php

namespace Jetcod\LaravelRepository\Test;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Jetcod\LaravelRepository\Eloquent\BaseRepository;
use Jetcod\LaravelRepository\Exceptions\RepositoryException;
use Jetcod\LaravelRepository\Test\Stubs\InvalidModel;
use Jetcod\LaravelRepository\Test\Stubs\TestModel;

/**
 * @internal
 *
 * @coversNothing
 */
class RepositoryTest extends TestCase
{
    public function testNonExistantModel()
    {
        $model = 'App\Models\InvalidModelNamespace';
        $this->expectException(RepositoryException::class);
        $this->expectExceptionMessage(sprintf('The class %s does not exist.', $model));

        new class('repository') extends BaseRepository {
            protected function getModelName()
            {
                return 'App\Models\InvalidModelNamespace';
            }
        };
    }

    public function testInvalidModelInstance()
    {
        $model = InvalidModel::class;
        $this->expectException(RepositoryException::class);
        $this->expectExceptionMessage(sprintf('The class %s must be an instance of %s.', $model, Model::class));

        new class('repository') extends BaseRepository {
            protected function getModelName()
            {
                return InvalidModel::class;
            }
        };
    }

    public function testGetRepositoryInstance()
    {
        $this->assertInstanceOf(BaseRepository::class, $this->makeRepository());
    }

    public function testCreate()
    {
        $expectedModel = $this->makeModel($this->sampleData);
        $repo          = $this->makeRepository();

        $repo->query()
            ->shouldReceive('create')->once()
            ->with($this->sampleData)
            ->andReturn($expectedModel)
        ;

        $this->assertEquals($expectedModel, $repo->create($this->sampleData));
    }

    public function testInsert()
    {
        $rows = [$this->sampleData];
        $repo = $this->makeRepository();

        $repo->query()
            ->shouldReceive('insert')->once()
            ->with($rows)
            ->andReturn(true)
        ;

        $this->assertIsBool($repo->insert($rows));
    }

    public function testUpdate()
    {
        $mockModel = $this->makeMock(TestModel::class);
        $repo      = $this->makeRepository();

        $mockModel
            ->shouldReceive('getFillable')->once()
            ->andReturn($fillable = array_keys($this->sampleData))
        ;

        $mockModel
            ->shouldReceive('fillable')->once()
            ->with($fillable)
            ->andReturnSelf()
        ;

        $mockModel
            ->shouldReceive('fill')->once()
            ->andReturnSelf()
        ;

        $mockModel
            ->shouldReceive('save')->once()
            ->andReturn($mockModel)
        ;

        $this->assertEquals($mockModel, $repo->update($mockModel, $this->sampleData));
    }

    public function testUpdateFailed()
    {
        $mockModel = $this->makeMock(TestModel::class);
        $repo      = $this->makeRepository();

        $mockModel
            ->shouldReceive('getFillable')->once()
            ->andReturn($fillable = array_keys($this->sampleData))
        ;

        $mockModel
            ->shouldReceive('fillable')->once()
            ->with($fillable)
            ->andReturnSelf()
        ;

        $mockModel
            ->shouldReceive('fill')->once()
            ->andReturnSelf()
        ;

        $mockModel
            ->shouldReceive('save')->once()
            ->andReturnNull()
        ;

        $this->assertNull($repo->update($mockModel, $this->sampleData));
    }

    public function testUpdateOrCreate()
    {
        $mockModel  = $this->makeMock(TestModel::class);
        $repo       = $this->makeRepository();
        $conditions = [
            ['email', '=', $this->sampleData['email']],
        ];

        $repo->query()
            ->shouldReceive('updateOrCreate')->once()
            ->with($this->sampleData, $conditions)
            ->andReturn($mockModel)
        ;

        $this->assertEquals($mockModel, $repo->updateOrCreate($this->sampleData, $conditions));
    }

    public function testCountBy()
    {
        $conditions = [
            ['email', '=', 'something@example.com'],
        ];

        $repo = $this->makeRepository();

        $repo->query()
            ->shouldReceive('where')->once()
            ->with($conditions)
            ->andReturnSelf()
        ;

        $repo->query()
            ->shouldReceive('count')->once()
            ->andReturn(5)
        ;

        $this->assertEquals(5, $repo->countBy($conditions));
    }

    public function testFind()
    {
        $expectedModel = $this->makeModel($this->sampleData);

        $repo = $this->makeRepository();

        $repo->query()
            ->shouldReceive('find')->once()
            ->andReturn($expectedModel)
        ;

        $this->assertEquals($expectedModel, $repo->find($expectedModel->id));
    }

    public function testFindAll()
    {
        $expectedCollection = new Collection([
            $this->makeModel(['id' => 1, 'name' => 'John']),
            $this->makeModel(['id' => 2, 'name' => 'Mike']),
        ]);

        $repo = $this->makeRepository();

        $repo->query()
            ->shouldReceive('get')->once()
            ->andReturn($expectedCollection)
        ;

        $result = $repo->findAll();

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertEquals($expectedCollection, $result);
    }

    public function testFindWithRelations()
    {
        $expectedModel = $this->makeModel(['id' => 1, 'name' => 'John']);
        $repo          = $this->makeRepository();

        $repo->query()
            ->shouldReceive('with')->twice()
            ->with([])
            ->andReturnSelf()
        ;

        $repo->query()
            ->shouldReceive('find')->once()
            ->with($expectedModel->id)
            ->andReturn($expectedModel)
        ;

        $repo->query()
            ->shouldReceive('find')->once()
            ->with($expectedModel->id + 1)
            ->andReturnNull()
        ;

        $this->assertEquals($expectedModel, $repo->findWithRelations($expectedModel->id, []));
        $this->assertNull($repo->findWithRelations($expectedModel->id + 1, []));
    }

    public function testFindBy()
    {
        $expectedModel = $this->makeModel($this->sampleData);
        $conditions    = [
            ['email', '=', $expectedModel->email],
        ];

        $repo = $this->makeRepository();

        $repo->query()
            ->shouldReceive('with')->once()
            ->with([])
            ->andReturnSelf()
        ;

        $repo->query()
            ->shouldReceive('where')->once()
            ->with($conditions)
            ->andReturnSelf()
        ;

        $repo->query()
            ->shouldReceive('get')->once()
            ->andReturn($this->makeMock(Collection::class))
        ;

        $this->assertInstanceOf(Collection::class, $repo->findBy($conditions, [], false));
    }

    public function testFindByAndPaginateResult()
    {
        $conditions = [
            ['id', '>', 5],
        ];

        $repo = $this->makeRepository();

        $repo->query()
            ->shouldReceive('with')->once()
            ->with([])
            ->andReturnSelf()
        ;

        $repo->query()
            ->shouldReceive('where')->once()
            ->with($conditions)
            ->andReturnSelf()
        ;

        $this->assertInstanceOf(LengthAwarePaginator::class, $repo->findBy($conditions, [], true, 5));
    }

    public function testFindOneBy()
    {
        $expectedModel = $this->makeModel($this->sampleData);
        $conditions    = [
            ['email', '=', $expectedModel->email],
        ];

        $repo = $this->makeRepository();

        $repo->query()
            ->shouldReceive('with')->once()
            ->with([])
            ->andReturnSelf()
        ;

        $repo->query()
            ->shouldReceive('where')->once()
            ->with($conditions)
            ->andReturnSelf()
        ;

        $repo->query()
            ->shouldReceive('first')->once()
            ->andReturn($expectedModel)
        ;

        $this->assertEquals($expectedModel, $repo->findOneBy($conditions, [], false));
    }

    public function testDeleteNoneExistentModel()
    {
        $nodeExistentId = 10;
        $repo           = $this->makeRepository();

        $repo->query()
            ->shouldReceive('find')->once()
            ->with(10)
            ->andReturnNull()
        ;

        $this->expectException(RepositoryException::class);
        $this->expectExceptionMessage('Model not found!');

        $repo->delete($nodeExistentId);
    }

    public function testDeleteById()
    {
        $repo = $this->makeRepository();

        $repo->query()
            ->shouldReceive('find')->once()
            ->andReturn($model = $this->makeMock(TestModel::class))
        ;

        $model->shouldReceive('delete')
            ->once()
            ->andReturnNull()
        ;

        $this->assertNull($repo->delete(1));
    }

    public function testDeleteByModel()
    {
        $mockModel = $this->makeMock(TestModel::class);
        $repo      = $this->makeRepository();

        $mockModel->shouldReceive('delete')
            ->once()
            ->andReturnNull()
        ;

        $this->assertNull($repo->delete($mockModel));
    }
}
