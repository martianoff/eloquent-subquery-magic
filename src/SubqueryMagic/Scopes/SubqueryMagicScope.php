<?php

namespace MaksimM\SubqueryMagic\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
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
        'FromSubquery',
    ];

    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param \Illuminate\Database\Eloquent\Builder|Relation $builder
     * @param \Illuminate\Database\Eloquent\Model            $model
     *
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
    }

    /**
     * Extend the query builder with the needed functions.
     *
     * @param \Illuminate\Database\Eloquent\Builder|Relation $builder
     *
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
     * @param \Illuminate\Database\Eloquent\Builder|Relation $builder
     *
     * @return void
     */
    protected function addLeftJoinSubquery($builder)
    {
        $builder->macro('leftJoinSubquery', function ($builder, $subquery, $alias, \Closure $on) {
            /*
             * @var $builder \Illuminate\Database\Eloquent\Builder|Relation
             * @var $subquery \Illuminate\Database\Eloquent\Builder|Relation
             */
            $builder->leftJoin(DB::raw('('.$subquery->toSql().') '.$builder->getQuery()->getGrammar()->wrap($alias)), $on);
            //merge bindings from subquery
            $this->addBindings($builder, $subquery->getBindings());

            return $builder;
        });
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder|Relation $builder
     * @param array                                          $bindings
     * @param string                                         $type
     */
    private function addBindings($builder, $bindings, $type = 'where')
    {
        $builder->addBinding($bindings, $type);
    }

    /**
     * Add extension to the builder.
     *
     * @param \Illuminate\Database\Eloquent\Builder|Relation $builder
     *
     * @return void
     */
    protected function addRightJoinSubquery($builder)
    {
        $builder->macro('rightJoinSubquery', function ($builder, $subquery, $alias, \Closure $on) {
            /*
             * @var $builder \Illuminate\Database\Eloquent\Builder|Relation
             * @var $subquery \Illuminate\Database\Eloquent\Builder|Relation
             */
            $builder->rightJoin(DB::raw('('.$subquery->toSql().') '.$builder->getQuery()->getGrammar()->wrap($alias)), $on);
            //merge bindings from subquery
            $this->addBindings($builder, $subquery->getBindings());

            return $builder;
        });
    }

    /**
     * Add extension to the builder.
     *
     * @param \Illuminate\Database\Eloquent\Builder|Relation $builder
     *
     * @return void
     */
    protected function addJoinSubquery($builder)
    {
        $builder->macro('joinSubquery', function ($builder, $subquery, $alias, \Closure $on) {
            /*
             * @var $builder \Illuminate\Database\Eloquent\Builder|Relation
             * @var $subquery \Illuminate\Database\Eloquent\Builder|Relation
             */
            $builder->join(DB::raw('('.$subquery->toSql().') '.$builder->getQuery()->getGrammar()->wrap($alias)), $on);
            //merge bindings from subquery
            $this->addBindings($builder, $subquery->getBindings());

            return $builder;
        });
    }

    /**
     * Add extension to the builder.
     *
     * @param \Illuminate\Database\Eloquent\Builder|Relation $builder
     *
     * @return void
     */
    protected function addWhereInSubquery($builder)
    {
        $builder->macro('whereInSubquery', function ($builder, $field, $subquery) {
            /*
             * @var $builder \Illuminate\Database\Eloquent\Builder|Relation
             * @var $subquery \Illuminate\Database\Eloquent\Builder|Relation
             */
            $builder->whereRaw($builder->getQuery()->getGrammar()->wrap($field).' IN ('.$subquery->toSql().')');
            //merge bindings from subquery
            $this->addBindings($builder, $subquery->getBindings());

            return $builder;
        });
    }

    /**
     * Add extension to the builder.
     *
     * @param \Illuminate\Database\Eloquent\Builder|Relation $builder
     *
     * @return void
     */
    protected function addWhereNotInSubquery($builder)
    {
        $builder->macro('whereNotInSubquery', function ($builder, $field, $subquery) {
            /*
             * @var $builder \Illuminate\Database\Eloquent\Builder|Relation
             * @var $subquery \Illuminate\Database\Eloquent\Builder|Relation
             */
            $builder->whereRaw($builder->getQuery()->getGrammar()->wrap($field).' NOT IN ('.$subquery->toSql().')');
            //merge bindings from subquery
            $this->addBindings($builder, $subquery->getBindings());

            return $builder;
        });
    }

    /**
     * Add extension to the builder.
     *
     * @param \Illuminate\Database\Eloquent\Builder|Relation $builder
     *
     * @return void
     */
    protected function addOrWhereInSubquery($builder)
    {
        $builder->macro('orWhereInSubquery', function ($builder, $field, $subquery) {
            /*
             * @var $builder \Illuminate\Database\Eloquent\Builder|Relation
             * @var $subquery \Illuminate\Database\Eloquent\Builder|Relation
             */
            $builder->orWhereRaw($builder->getQuery()->getGrammar()->wrap($field).' IN ('.$subquery->toSql().')');
            //merge bindings from subquery
            $this->addBindings($builder, $subquery->getBindings());

            return $builder;
        });
    }

    /**
     * Add extension to the builder.
     *
     * @param \Illuminate\Database\Eloquent\Builder|Relation $builder
     *
     * @return void
     */
    protected function addOrWhereNotInSubquery($builder)
    {
        $builder->macro('orWhereNotInSubquery', function ($builder, $field, $subquery) {
            /*
             * @var $builder \Illuminate\Database\Eloquent\Builder|Relation
             * @var $subquery \Illuminate\Database\Eloquent\Builder|Relation
             */
            $builder->orWhereRaw($builder->getQuery()->getGrammar()->wrap($field).' NOT IN ('.$subquery->toSql().')');
            //merge bindings from subquery
            $this->addBindings($builder, $subquery->getBindings());

            return $builder;
        });
    }

    /**
     * Add extension to the builder.
     *
     * @param \Illuminate\Database\Eloquent\Builder|Relation $builder
     *
     * @return void
     */
    protected function addFromSubquery($builder)
    {
        $builder->macro('fromSubquery', function ($builder, $subquery, $alias) {
            /*
             * @var $builder \Illuminate\Database\Eloquent\Builder|Relation
             * @var $subquery \Illuminate\Database\Eloquent\Builder|Relation
             */
            $builder->from(DB::raw('('.$subquery->toSql().') '.$builder->getQuery()->getGrammar()->wrap($alias)));
            //merge bindings from subquery
            $builder->setBindings(array_merge($subquery->getBindings(), $builder->getBindings()));

            return $builder;
        });
    }
}
