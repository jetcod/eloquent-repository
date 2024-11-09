<?php

namespace Jetcod\LaravelRepository\Test\Fixtures\Repositories;

use Jetcod\LaravelRepository\Eloquent\BaseRepository;
use Jetcod\LaravelRepository\Test\Fixtures\Models\User;

class UserRepository extends BaseRepository
{
    protected function getModelName()
    {
        return User::class;
    }
}
