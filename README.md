[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/maksimru/eloquent-subquery-magic/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/maksimru/eloquent-subquery-magic/?branch=master)
[![codecov](https://codecov.io/gh/maksimru/eloquent-subquery-magic/branch/master/graph/badge.svg)](https://codecov.io/gh/maksimru/eloquent-subquery-magic)
[![CircleCI](https://circleci.com/gh/maksimru/eloquent-subquery-magic.svg?style=svg)](https://circleci.com/gh/maksimru/eloquent-subquery-magic)

## About

Library extends Laravel's Eloquent ORM with various helpful sub query operations such as leftJoinSubquery or fromSubquery and provide clean methods to use Eloquent without raw statements

## Installation

```bash
composer require maksimru/eloquent-subquery-magic
```

### Supported operations (with examples)

1) leftJoinSubquery
    ```php
    User::selectRaw('user_id,comments_by_user.total_count')->leftJoinSubquery(
      //subquery
      Comment::selectRaw('user_id,count(*) total_count')
          ->groupBy('user_id'),
      //alias
      'comments_by_user', 
      //closure for "on" statement
      function ($join) {
          $join->on('users.id', '=', 'comments_by_user.user_id');
      }
    )->get();
    ```
2) joinSubquery
    ```php
    User::selectRaw('user_id,comments_by_user.total_count')->joinSubquery(
      //subquery
      Comment::selectRaw('user_id,count(*) total_count')
          ->groupBy('user_id'),
      //alias
      'comments_by_user', 
      //closure for "on" statement
      function ($join) {
          $join->on('users.id', '=', 'comments_by_user.user_id');
      }
    )->get();
    ```
3) rightJoinSubquery
    ```php
    User::selectRaw('user_id,comments_by_user.total_count')->rightJoinSubquery(
        //subquery
        Comment::selectRaw('user_id,count(*) total_count')
           ->groupBy('user_id'),
        //alias
        'comments_by_user', 
        //closure for "on" statement
        function ($join) {
           $join->on('users.id', '=', 'comments_by_user.user_id');
        }
    )->get();
    ```
4) whereInSubquery
    ```php
    User::whereInSubquery('id', Comment::selectRaw('distinct(user_id)'))->get();
    ```
5) whereNotInSubquery
    ```php
    User::whereNotInSubquery('id', Comment::selectRaw('distinct(user_id)'))->get();
    ```
6) orWhereInSubquery
    ```php
    User::where('is_enabled','=',true)->orWhereInSubquery('id', Comment::selectRaw('distinct(user_id)'))->get();
    ```
7) orWhereNotInSubquery
    ```php
    User::where('is_enabled','=',true)->orWhereNotInSubquery('id', Comment::selectRaw('distinct(user_id)'))->get();
    ```
8) fromSubquery
    ```php
    User::selectRaw('info.min_id,info.max_id,info.total_count')->fromSubquery(
        //subquery
        User::selectRaw('min(id) min_id,max(id) max_id,count(*) total_count'),
        //alias
        'info'
    )->get()
    ```
    
### Nested queries

It is possible to use it in nested queries, but you need to boot scope manually in each closure

```php
User::where(function ($nested_query) {
    (new SubqueryMagicScope())->extend($nested_query);
    $nested_query->where('id', '<', 10);
    $nested_query->orWhereNotInSubquery('id', Comment::selectRaw('distinct(user_id)'));
})
```

### Complex example

```php
User::selectRaw('users.name,filtered_members_with_stats.total_count')
    ->where(function ($nested_query) {
        (new SubqueryMagicScope())->extend($nested_query);
        $nested_query->where('id', '<', 10);
        $nested_query->orWhereNotInSubquery('id', Comment::selectRaw('distinct(user_id)'));
    })->rightJoinSubquery(
        User::selectRaw('user_id,comments_by_user.total_count')->leftJoinSubquery(
            Comment::selectRaw('user_id,count(*) total_count')
                ->groupBy('user_id'),
            'comments_by_user', function ($join) {
                $join->on('users.id', '=', 'comments_by_user.user_id');
            }
        )->where('id','<',20),
        'filtered_members_with_stats', function ($join) {
            $join->on('users.id', '=', 'filtered_members_with_stats.user_id');
        }
    )
    ->get();
```

It will be executed as:

```sql

SELECT users.name,
       filtered_members_with_stats.total_count
FROM `users`
RIGHT JOIN
  (SELECT name,
          comments_by_user.total_count
   FROM `users`
   LEFT JOIN
     (SELECT user_id,
             count(*) total_count
      FROM `comments`
      GROUP BY `user_id`) `comments_by_user` ON `users`.`id` = `comments_by_user`.`user_id`
   WHERE `id` < 10) `filtered_members_with_stats` ON `users`.`id` = `filtered_members_with_stats`.`user_id`
WHERE (`id` < 20
       OR `id` NOT IN
         (SELECT distinct(user_id)
          FROM `comments`))
          
```