<?php

/**
 * Created by PhpStorm.
 * User: Maksim
 * Date: 6/21/2017
 * Time: 16:02
 */
class BasicTest extends SubqueryMagicTestCase
{

    public function __construct(string $name = null, array $data = [], string $dataName = '')
    {
        TestUser::truncate();
        TestComment::truncate();
        parent::__construct($name, $data, $dataName);
    }

    public function testNormalSelect()
    {
        TestUser::create(['name' => 'John']);
        $user = TestUser::where('name', '=', 'John')->first();
        $this->assertNotNull($user);
    }

}
