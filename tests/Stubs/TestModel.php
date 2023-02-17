<?php

namespace Jetcod\LaravelRepository\Test\Stubs;

use Illuminate\Database\Eloquent\Model;

class TestModel extends Model
{
    protected $fillable = [
        'id', 'email', 'name',
    ];
}
