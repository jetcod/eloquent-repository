<?php

namespace Jetcod\LaravelRepository\Test;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection as DbColection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Jetcod\LaravelRepository\Eloquent\BaseRepository;
use Jetcod\LaravelRepository\Exceptions\RepositoryException;
use Jetcod\LaravelRepository\Test\Fixtures\Models\InvalidModel;
use Jetcod\LaravelRepository\Test\Fixtures\Models\User;
use Jetcod\LaravelRepository\Test\Fixtures\Repositories\UserRepository;

class RepositoryTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function testNonExistantModelThrowsException()
    {
        $model = 'App\Models\InvalidModelNamespace';
        $this->expectException(RepositoryException::class);
        $this->expectExceptionMessage(sprintf('The class %s does not exist.', $model));

        new class extends BaseRepository {
            protected function getModelName()
            {
                return 'App\Models\InvalidModelNamespace';
            }
        };
    }

    public function testNonExistentModelInstanceThrowsException()
    {
        $model = 'InvalidModel';
        $this->expectException(RepositoryException::class);
        $this->expectExceptionMessage(sprintf('The class %s does not exist.', $model));

        new class extends BaseRepository {
            protected function getModelName()
            {
                return 'InvalidModel';
            }
        };
    }

    public function testInvalidModelInstanceThrowsException()
    {
        $model = InvalidModel::class;
        $this->expectException(RepositoryException::class);
        $this->expectExceptionMessage(sprintf('The class %s must be an instance of %s.', $model, Model::class));

        new class extends BaseRepository {
            protected function getModelName()
            {
                return InvalidModel::class;
            }
        };
    }

    public function testGetRepositoryInstance()
    {
        $this->assertInstanceOf(BaseRepository::class, $this->makeRepository(User::class));
    }

    public function testQueryFunctionReturnsBuilderInstance()
    {
        $userRepository = $this->makeRepository(User::class);

        $this->assertInstanceOf(Builder::class, $userRepository->query());
    }

    public function testCreateFunctionStoresNewRecordAndReturnsModelInstance()
    {
        $userRepository = $this->makeRepository(User::class);

        $data = [
            'first_name' => $this->faker->firstName,
            'last_name'  => $this->faker->lastName,
            'email'      => $this->faker->email,
            'password'   => $this->faker->password,
        ];
        $model = $userRepository->create($data);

        $this->assertInstanceOf(Model::class, $model);
        $this->assertDatabaseHas('users', $data);
    }

    public function testUpdateOrCreateFunctionCreatesNewRecordIfNotExistsAndReturnsModelInstance()
    {
        $userRepository = $this->makeRepository(User::class);

        $data = [
            'first_name' => $this->faker->firstName,
            'last_name'  => $this->faker->lastName,
            'email'      => $this->faker->email,
            'password'   => $this->faker->password,
        ];
        $model = $userRepository->updateOrCreate($data, []);

        $this->assertInstanceOf(Model::class, $model);
        $this->assertDatabaseHas('users', $data);
    }

    public function testUpdateorcreateFunctionUpdatesRecordIfExistsAndReturnsModelInstance()
    {
        $userRepository = $this->makeRepository(User::class);

        $model = User::factory()->userDomain('gmail.com')->create();

        $userRepository->updateOrCreate(
            ['email' => $model->email],
            ['first_name' => 'updated first name']
        );

        $this->assertDatabaseHas('users', ['first_name' => 'updated first name']);
        $this->assertDatabaseMissing('users', ['first_name' => $model->first_name]);
    }

    public function testInsertFunctionStoresASetOfRecordsAndRetunnsBool()
    {
        $userRepository = $this->makeRepository(User::class);

        for ($i = 0; $i < 5; ++$i) {
            $data[] = [
                'first_name' => $this->faker->firstName,
                'last_name'  => $this->faker->lastName,
                'email'      => $this->faker->email,
                'password'   => $this->faker->password,
            ];
        }

        $result = $userRepository->insert($data);

        $this->assertTrue($result);
        $this->assertDatabaseCount('users', 5);
        for ($i = 0; $i < 5; ++$i) {
            $this->assertDatabaseHas('users', $data[$i]);
        }
    }

    public function testCountbyconditionFunctionReturnsTotalRecordsByCondition()
    {
        User::factory()->userDOmain('hotmail.com')->count(3)->create();
        User::factory()->userDOmain('gmail.com')->count(8)->create();

        $userRepository = $this->app->make(UserRepository::class);

        $this->assertEquals(3, $userRepository->countBy([['email', 'like', '%hotmail%']]));
        $this->assertEquals(8, $userRepository->countBy([['email', 'like', '%gmail%']]));
    }

    public function testFindAModelByIdReturnsModelInstance()
    {
        $usersCollection = User::factory()->count(10)->create();
        $expectedModel   = $usersCollection->random();

        $userRepository = $this->makeRepository(User::class);
        $model          = $userRepository->find($expectedModel->id);

        $this->assertEqualsCanonicalizing($expectedModel->toArray(), $model->toArray());
    }

    public function testFindModelByIdAndRelationsReturnsModelWithRelations()
    {
        $userRepository = $this->makeRepository(User::class);
        $users          = User::factory()->count(10)->create();
        $expectedModel  = $users->random();

        $model = $userRepository->findWithRelations($expectedModel->id, ['posts']);

        $this->assertInstanceOf(Model::class, $model);
        $this->assertEquals($expectedModel->id, $model->id);
        $this->assertEquals($expectedModel->posts->toArray(), $model->posts->toArray());
    }

    public function testFinOneModeldWithRelationsReturnsNullIfModelDoesNotExist()
    {
        $userRepository = $this->makeRepository(User::class);
        User::factory()->count(10)->create();

        $model = $userRepository->findWithRelations(999999, ['posts']);

        $this->assertNull($model);
    }

    public function testFinOneModeldWithRelationsReturnsAModelWithRelations()
    {
        $userRepository = $this->makeRepository(User::class);
        $users          = User::factory()->count(10)->create();
        $expectedModel  = $users->random();

        $model = $userRepository->findOneBy(['email' => $expectedModel->email], ['posts']);

        $this->assertInstanceOf(Model::class, $model);
        $this->assertEquals($expectedModel->id, $model->id);
        $this->assertEquals($expectedModel->posts->toArray(), $model->posts->toArray());
    }

    public function testFindModelByInvalidIdReturnsNull()
    {
        User::factory()->count(10)->create();

        $userRepository = $this->makeRepository(User::class);
        $model          = $userRepository->find(9999999);

        $this->assertNull($model);
    }

    public function testUpdateARecordAndReturnUpdatedModel()
    {
        $userRepository = $this->makeRepository(User::class);
        $model          = User::factory()->create(['email' => 'test@example.com']);

        $updatedModel = $userRepository->update($model, ['email' => 'test@example.net']);

        $this->assertInstanceOf(Model::class, $updatedModel);
        $this->assertEquals('test@example.net', $updatedModel->email);
        $this->assertDatabaseHas('users', ['email' => 'test@example.net']);
    }

    public function testUpdateModelWithFillableColumnsOverridesFillableAttributes()
    {
        $userRepository = $this->makeRepository(User::class);
        $model          = User::factory()->create(['email' => 'test@example.com']);

        $updatedModel = $userRepository->update($model, ['email' => 'test@example.net'], ['first_name']);

        $this->assertInstanceOf(Model::class, $updatedModel);
        $this->assertEquals($model->email, $updatedModel->email);
        $this->assertDatabaseHas('users', ['email' => $model->email]);
    }

    public function testUpdateModelWillReturnNullIfItIsFailedToSave()
    {
        $user = User::factory()->create(['email' => 'test@example.com']);

        $mockedModel = $this->makeMock(Model::class);
        $mockedModel->makePartial()->shouldReceive('save')->andReturn(false);

        $userRepository = $this->makeRepository(User::class);

        $updatedModel = $userRepository->update($mockedModel, ['email' => 'test@example.net']);

        $this->assertNull($updatedModel);
        $this->assertDatabaseMissing('users', ['email' => 'test@example.net']);
        $this->assertDatabaseHas('users', ['email' => $user->email]);
    }

    public function testDeleteByIdRemovesRecordFromDatabase()
    {
        $userRepository       = $this->makeRepository(User::class);
        $userModelsCollection = User::factory()->count(10)->create();

        $expectedModel = $userModelsCollection->random();

        $userRepository->delete($expectedModel->id);
        $this->assertDatabaseMissing('users', ['id' => $expectedModel->id]);
    }

    public function testDeleteByModelRemovesRecordFromDatabase()
    {
        $userRepository       = $this->makeRepository(User::class);
        $userModelsCollection = User::factory()->count(10)->create();

        $expectedModel = $userModelsCollection->random();

        $userRepository->delete($expectedModel);
        $this->assertDatabaseMissing('users', ['id' => $expectedModel->id]);
    }

    public function testDeleteByInvalidModelIdThrowsException()
    {
        $userRepository = $this->makeRepository(User::class);
        $this->expectException(RepositoryException::class);
        $this->expectExceptionMessage('Model not found!');

        $userRepository->delete(9999999);
    }

    public function testDeleteAnArrayOfModelIdsRemovesRecordsFromDatabase()
    {
        $userRepository       = $this->makeRepository(User::class);
        $userModelsCollection = User::factory()->count(10)->create();

        $expectedModels = $userModelsCollection->random(5);
        $userIds        = $expectedModels->pluck('id')->toArray();

        $userRepository->destroy($userIds);

        $expectedModels->each(function ($model) {
            $this->assertDatabaseMissing('users', ['id' => $model->id]);
        });
        $this->assertDatabaseCount('users', 5);
    }

    public function testDeleteACollectionOfModelsRemovesRecordsFromDatabase()
    {
        $userRepository       = $this->makeRepository(User::class);
        $userModelsCollection = User::factory()->count(10)->create();

        $expectedModels = $userModelsCollection->random(5);

        $userRepository->destroy($expectedModels);

        $expectedModels->each(function ($model) {
            $this->assertDatabaseMissing('users', ['id' => $model->id]);
        });
        $this->assertDatabaseCount('users', 5);
    }

    public function testFindAllReturnsCollectionOfModels()
    {
        $userRepository = $this->makeRepository(User::class);
        User::factory()->count(10)->create();

        $models = $userRepository->findAll();

        $this->assertInstanceOf(DbColection::class, $models);
        $this->assertCount(10, $models);
    }

    public function testFindAllReturnsEmptyCollectionIfNoModelsExist()
    {
        $userRepository = $this->makeRepository(User::class);
        $models         = $userRepository->findAll();

        $this->assertInstanceOf(DbColection::class, $models);
        $this->assertCount(0, $models);
    }

    public function testFindAModelByItsPrimaryKeyReturnsModelObject()
    {
        $user = User::factory()->create();

        $userRepository = $this->app->make(UserRepository::class);

        $this->assertEquals($user->toArray(), $userRepository->find($user->id)->toArray());
    }

    public function testFindAModelByInvalidPrimaryKeyReurnsNull()
    {
        $user = User::factory()->create();

        $userRepository = $this->app->make(UserRepository::class);

        $this->assertNull($userRepository->find($user->id + 1));
    }

    public function testFindOneByReturnsStoredModel()
    {
        $usersCollection = User::factory()->count(5)->create();
        $expectedModel   = $usersCollection->random();

        $userRepository = $this->app->make(UserRepository::class);

        $result = $userRepository->findOneBy(['first_name' => $expectedModel->first_name]);

        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals($expectedModel->toArray(), $result->toArray());
    }

    public function testFindByConditionReturnsACollectionOfModels()
    {
        User::factory()->count(5)->userDomain('yahoo.com')->create();
        $gmailCollection = User::factory()->userDomain('gmail.com')->count(3)->create();

        $userRepository = $this->app->make(UserRepository::class);

        $result = $userRepository->findBy([['email', 'like', '%gmail%']], [], false);

        $this->assertInstanceOf(DbColection::class, $result);
        $this->assertEquals($gmailCollection->toArray(), $result->toArray());
        $this->assertCount(3, $result);
    }

    public function testFindByConditionReturnsPaginatedResult()
    {
        User::factory()->count(10)->userDomain('yahoo.com')->create();
        $gmailCollection = User::factory()->userDomain('gmail.com')->count(17)->create();

        $userRepository = $this->app->make(UserRepository::class);

        $result = $userRepository->findBy([['email', 'like', '%gmail%']]);

        $this->assertInstanceOf(Paginator::class, $result);
        $this->assertEquals($gmailCollection->count(), $result->total());
    }

    public function testFindModelWithRelationsUsingQueryBuilderReturnsModelsCollection()
    {
        $userRepository = $this->makeRepository(User::class);
        $users          = User::factory()->count(10)->create();

        $models = $userRepository->with(['posts'])->get();

        $this->assertInstanceOf(DbColection::class, $models);
        $this->assertCount(10, $models);
        $models->each(function ($model) {
            $this->assertArrayHasKey('posts', $model->toArray());
        });
    }
}
