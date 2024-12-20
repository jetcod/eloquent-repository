<?php

namespace Jetcod\LaravelRepository\Test;

use Illuminate\Database\Eloquent\Factories\Factory;
use Jetcod\LaravelRepository\Eloquent\EloquentRepositoryInterface;
use Jetcod\LaravelRepository\ServiceProvider;
use Jetcod\LaravelRepository\Test\Fixtures\Models\User;
use Mockery as m;
use Orchestra\Testbench\TestCase as TestBench;

class TestCase extends TestBench
{
    public function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(function (string $modelName) {
            return 'Jetcod\LaravelRepository\Test\Fixtures\Factories\\' . class_basename($modelName) . 'Factory';
        });
    }

    public function tearDown(): void
    {
        parent::tearDown();

        m::close();
    }

    protected function getPackageProviders($app)
    {
        return [ ServiceProvider::class ];
    }

    protected function defineDatabaseMigrations()
    {
        $this->loadMigrationsFrom(__DIR__ . '/Database/Migrations');
    }
    
    protected function makeMock($className)
    {
        return m::mock($className);
    }

    protected function makeRepository($model): EloquentRepositoryInterface
    {
        $repositoryClass = sprintf('Jetcod\LaravelRepository\Test\Fixtures\Repositories\%sRepository', class_basename($model));

        return $this->app->make($repositoryClass);
    }

    protected function makeModel(array $data = []): User
    {
        return new User($data);
    }
}
