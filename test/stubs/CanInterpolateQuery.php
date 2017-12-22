<?php
/**
 * Created by PhpStorm.
 * User: Maksim
 * Date: 12/22/2017
 * Time: 15:05
 */

trait CanInterpolateQuery
{
    public static function interpolateEloquent($eloquent)
    {
        return self::interpolateQuery($eloquent->toSql(), $eloquent->getBindings());
    }

    public static function interpolateQuery($query, $params)
    {
        $keys = array();

        # build a regular expression for each parameter
        foreach ($params as $key => $value) {
            if (is_string($key)) {
                $keys[] = '/:' . $key . '/';
            } else {
                $keys[] = '/[?]/';
            }
            if (is_bool($value))
                $params[$key] = ($value === true ? 1 : 0);
        }

        $query = preg_replace($keys, $params, $query, 1, $count);

        return $query;
    }
}