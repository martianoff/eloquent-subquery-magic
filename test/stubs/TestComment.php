<?php

class TestComment extends SubqueryMagicStub
{
    protected $table = 'comments';

    protected $fillable = [
        'user_id', 'text'
    ];

    public function user()
    {
        return $this->belongsTo('TestUser');
    }
}