<?php

use MaksimM\SubqueryMagic\SubqueryMagic;

/**
 * Created by PhpStorm.
 * User: Maksim
 * Date: 6/21/2017
 * Time: 16:02.
 */
class InitTest extends SubqueryMagicTestCase
{
    public function testIsInitiallyEmpty()
    {
        $traitObj = new TraitImplementationClass();
        $booted_global_scopes = $this->accessProtected($traitObj, 'globalScopes');
        $this->assertArrayHasKey('MaksimM\SubqueryMagic\Scopes\SubqueryMagicScope', $booted_global_scopes['TraitImplementationClass']);
        $booted_methods = $this->accessProtected($booted_global_scopes['TraitImplementationClass']['MaksimM\SubqueryMagic\Scopes\SubqueryMagicScope'], 'extensions');
        $this->assertContains('LeftJoinSubquery', $booted_methods);
    }

    public function accessProtected($obj, $prop)
    {
        $reflection = new ReflectionClass($obj);
        $property = $reflection->getProperty($prop);
        $property->setAccessible(true);

        return $property->getValue($obj);
    }
}

class TraitImplementationClass extends \Illuminate\Database\Eloquent\Model
{
    use SubqueryMagic;
}
