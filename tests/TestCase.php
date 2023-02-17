<?php

namespace Jetcod\LaravelRepository\Test;

use Jetcod\LaravelRepository\Test\Stubs\TestModel;
use Jetcod\LaravelRepository\Test\Stubs\TestRepository;
use Mockery as m;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;

/**
 * @internal
 *
 * @coversNothing
 */
class TestCase extends PHPUnitTestCase
{
    public static $functions;

    public $builderMock;

    protected $sampleData = [
        'id'    => 123,
        'email' => 'something@example.com',
        'name'  => 'Bill',
    ];

    public function tearDown(): void
    {
        m::close();
    }

    public function makeMock($className)
    {
        return m::mock($className);
    }

    protected function makeRepository(): TestRepository
    {
        return new TestRepository();
    }

    protected function makeModel(array $data = []): TestModel
    {
        return new TestModel($data);
    }
}
