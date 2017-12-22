<?php
/**
 * Created by PhpStorm.
 * User: Maksim
 * Date: 12/22/2017
 * Time: 14:29
 */

use Illuminate\Support\Facades\DB;

abstract class SubqueryMagicTestCase extends \PHPUnit\Framework\TestCase
{

    /**
     * Is SQL allowed to be issued?
     *
     * @var bool
     */
    protected static $sql = true;

    public static function setUpBeforeClass()
    {
        DB::listen(function ($query) {
            if (static::$sql === false) {
                throw new Exception($query->sql);
            }
        });
    }

    public function setUp()
    {
        DB::beginTransaction();
    }

    public function tearDown()
    {
        DB::rollBack();
        static::$sql = true;
    }
}