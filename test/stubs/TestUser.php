<?php

class TestUser extends SubqueryMagicStub
{
    protected $table = 'users';

    protected $fillable = [
        'name'
    ];

    public function comments()
    {
        return $this->hasMany('TestComment');
    }
}