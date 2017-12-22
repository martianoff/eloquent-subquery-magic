<?php

namespace MaksimM\SubqueryMagic\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\DB;

class SubqueryMagicScope implements Scope
{

    /**
     * All of the extensions to be added to the builder.
     *
     * @var array
     */
    protected $extensions = [
        'LeftJoinSubquery',
        'JoinSubquery',
        'RightJoinSubquery',
        'WhereInSubquery',
        'WhereNotInSubquery',
        'OrWhereInSubquery',
        'OrWhereNotInSubquery',
        'FromSubquery'
    ];

    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $builder
     * @param  \Illuminate\Database\Eloquent\Model $model
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
    }

    /**
     * Extend the query builder with the needed functions.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $builder
     * @return void
     */
    public function extend(Builder $builder)
    {
        foreach ($this->extensions as $extension) {
            $this->{"add{$extension}"}($builder);
        }
    }

    /**
     * Add extension to the builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $builder
     * @return void
     */
    protected function addLeftJoinSubquery(Builder $builder)
    {
        $builder->macro('leftJoinSubquery', function (Builder $builder, Builder $subquery, $alias, \Closure $on) {
            $builder->leftJoin(DB::raw('(' . $subquery->toSql() . ') ' . $builder->getQuery()->getGrammar()->wrap($alias)), $on);
            //merge bindings from subquery
            $this->addBindings($builder, $subquery->getBindings());
            return $builder;
        });
    }

    private function addBindings($builder, $bindings, $type = 'where')
    {
        $builder->addBinding($bindings, $type);
    }

    /**
     * Add extension to the builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $builder
     * @return void
     */
    protected function addRightJoinSubquery(Builder $builder)
    {
        $builder->macro('rightJoinSubquery', function (Builder $builder, Builder $subquery, $alias, \Closure $on) {
            $builder->rightJoin(DB::raw('(' . $subquery->toSql() . ') ' . $builder->getQuery()->getGrammar()->wrap($alias)), $on);
            //merge bindings from subquery
            $this->addBindings($builder, $subquery->getBindings());
            return $builder;
        });
    }

    /**
     * Add extension to the builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $builder
     * @return void
     */
    protected function addJoinSubquery(Builder $builder)
    {
        $builder->macro('joinSubquery', function (Builder $builder, Builder $subquery, $alias, \Closure $on) {
            $builder->join(DB::raw('(' . $subquery->toSql() . ') ' . $builder->getQuery()->getGrammar()->wrap($alias)), $on);
            //merge bindings from subquery
            $this->addBindings($builder, $subquery->getBindings());
            return $builder;
        });
    }

    /**
     * Add extension to the builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $builder
     * @return void
     */
    protected function addWhereInSubquery(Builder $builder)
    {
        $builder->macro('whereInSubquery', function (Builder $builder, $field, Builder $subquery) {
            $builder->whereRaw($builder->getQuery()->getGrammar()->wrap($field) . ' IN (' . $subquery->toSql() . ')');
            //merge bindings from subquery
            $this->addBindings($builder, $subquery->getBindings());
            return $builder;
        });
    }

    /**
     * Add extension to the builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $builder
     * @return void
     */
    protected function addWhereNotInSubquery(Builder $builder)
    {
        $builder->macro('whereNotInSubquery', function (Builder $builder, $field, Builder $subquery) {
            $builder->whereRaw($builder->getQuery()->getGrammar()->wrap($field) . ' NOT IN (' . $subquery->toSql() . ')');
            //merge bindings from subquery
            $this->addBindings($builder, $subquery->getBindings());
            return $builder;
        });
    }

    /**
     * Add extension to the builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $builder
     * @return void
     */
    protected function addOrWhereInSubquery(Builder $builder)
    {
        $builder->macro('orWhereInSubquery', function (Builder $builder, $field, Builder $subquery) {
            $builder->orWhereRaw($builder->getQuery()->getGrammar()->wrap($field) . ' IN (' . $subquery->toSql() . ')');
            //merge bindings from subquery
            $this->addBindings($builder, $subquery->getBindings());
            return $builder;
        });
    }

    /**
     * Add extension to the builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $builder
     * @return void
     */
    protected function addOrWhereNotInSubquery(Builder $builder)
    {
        $builder->macro('orWhereNotInSubquery', function (Builder $builder, $field, Builder $subquery) {
            $builder->orWhereRaw($builder->getQuery()->getGrammar()->wrap($field) . ' NOT IN (' . $subquery->toSql() . ')');
            //merge bindings from subquery
            $this->addBindings($builder, $subquery->getBindings());
            return $builder;
        });
    }

    /**
     * Add extension to the builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $builder
     * @return void
     */
    protected function addFromSubquery(Builder $builder)
    {
        $builder->macro('fromSubquery', function (Builder $builder, Builder $subquery, $alias) {
            $builder->from(DB::raw('(' . $subquery->toSql() . ') ' . $builder->getQuery()->getGrammar()->wrap($alias)));
            //merge bindings from subquery
            $builder->setBindings(array_merge($subquery->getBindings(), $builder->getBindings()));
            return $builder;
        });
    }

}
