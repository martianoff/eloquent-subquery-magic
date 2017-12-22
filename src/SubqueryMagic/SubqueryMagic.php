<?php

namespace MaksimM\SubqueryMagic;

use MaksimM\SubqueryMagic\Scopes\SubqueryMagicScope;

trait SubqueryMagic
{

    public static function bootSubqueryMagic()
    {
        static::addGlobalScope(new SubqueryMagicScope);
    }

}
