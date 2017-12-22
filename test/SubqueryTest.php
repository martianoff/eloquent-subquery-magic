<?php

use MaksimM\SubqueryMagic\Scopes\SubqueryMagicScope;

class SubqueryTest extends SubqueryMagicTestCase
{

    use CanInterpolateQuery;

    /**
     * @var \Illuminate\Database\Eloquent\Collection
     */
    private $available_users;

    public function __construct(string $name = null, array $data = [], string $dataName = '')
    {
        TestUser::truncate();
        TestComment::truncate();
        $this->seedFakeData();
        parent::__construct($name, $data, $dataName);
    }

    private function seedFakeData()
    {
        $faker = Faker\Factory::create();
        $users = [];
        for ($i = 0; $i < 10; $i++) {
            $users[] = ['name' => $faker->name];
        }
        TestUser::insert($users);
        $this->available_users = TestUser::all();
        $comments = [];
        for ($i = 0; $i < 100; $i++) {
            $comments[] = ['user_id' => $this->available_users->pluck('id')->random(), 'text' => $faker->text];
        }
        for ($i = 0; $i < 10; $i++) {
            $comments[] = ['user_id' => null, 'text' => $faker->text];
        }
        TestComment::insert($comments);
    }

    public function testResultFromSubquery()
    {
        $excluded_id = $this->available_users->pluck('id')->random();
        $user_info = TestUser::selectRaw('info.min_id,info.max_id,info.total_count')->fromSubquery(
            TestUser::selectRaw('min(id) min_id,max(id) max_id,count(*) total_count')
                ->where('id', '!=', $excluded_id),
            'info'
        )->first();
        $this->assertInstanceOf(TestUser::class, $user_info);
        $this->assertEquals($this->available_users->where('id', '!=', $excluded_id)->pluck('id')->min(), $user_info->min_id);
        $this->assertEquals($this->available_users->where('id', '!=', $excluded_id)->pluck('id')->max(), $user_info->max_id);
        $this->assertEquals($this->available_users->where('id', '!=', $excluded_id)->count(), $user_info->total_count);
    }

    public function testResultFromSql()
    {
        $excluded_id = $this->available_users->pluck('id')->random();
        $user_info_query = TestUser::selectRaw('info.min_id,info.max_id,info.total_count')->fromSubquery(
            TestUser::selectRaw('min(id) min_id,max(id) max_id,count(*) total_count')
                ->where('id', '!=', $excluded_id),
            'info'
        );
        $this->assertEquals("select info.min_id,info.max_id,info.total_count from (select min(id) min_id,max(id) max_id,count(*) total_count from `users` where `id` != $excluded_id) `info`", $this->interpolateEloquent($user_info_query));
    }

    public function testLeftJoinSubquerySql()
    {
        $users_with_comments_count = TestUser::selectRaw('name,comments_by_user.total_count')->leftJoinSubquery(
            TestComment::selectRaw('user_id,count(*) total_count')->groupBy('user_id'),
            'comments_by_user', function ($join) {
            $join->on('users.id', '=', 'comments_by_user.user_id');
        }
        );
        $example_user = $users_with_comments_count->first();
        $this->assertInstanceOf(TestUser::class, $example_user);
        $this->assertGreaterThanOrEqual(0, $example_user->total_count);
        $this->assertEquals("SELECT name,comments_by_user.total_count FROM `users` LEFT JOIN (SELECT user_id,count(*) total_count FROM `comments` GROUP BY `user_id`) `comments_by_user` ON `users`.`id` = `comments_by_user`.`user_id` LIMIT 1", $this->interpolateEloquent($users_with_comments_count));
    }

    public function testRightJoinSubquerySql()
    {
        $users_with_comments_count = TestUser::selectRaw('name,comments_by_user.total_count')->rightJoinSubquery(
            TestComment::selectRaw('user_id,count(*) total_count')->groupBy('user_id'),
            'comments_by_user', function ($join) {
            $join->on('users.id', '=', 'comments_by_user.user_id');
        }
        );
        $example_user = $users_with_comments_count->first();
        $this->assertInstanceOf(TestUser::class, $example_user);
        $this->assertGreaterThanOrEqual(0, $example_user->total_count);
        $this->assertEquals("SELECT name,comments_by_user.total_count FROM `users` RIGHT JOIN (SELECT user_id,count(*) total_count FROM `comments` GROUP BY `user_id`) `comments_by_user` ON `users`.`id` = `comments_by_user`.`user_id` LIMIT 1", $this->interpolateEloquent($users_with_comments_count));
    }

    public function testJoinSubquerySql()
    {
        $users_with_comments_count = TestUser::selectRaw('name,comments_by_user.total_count')->joinSubquery(
            TestComment::selectRaw('user_id,count(*) total_count')->groupBy('user_id'),
            'comments_by_user', function ($join) {
            $join->on('users.id', '=', 'comments_by_user.user_id');
        }
        );
        $example_user = $users_with_comments_count->first();
        $this->assertInstanceOf(TestUser::class, $example_user);
        $this->assertGreaterThanOrEqual(0, $example_user->total_count);
        $this->assertEquals("SELECT name,comments_by_user.total_count FROM `users` INNER JOIN (SELECT user_id,count(*) total_count FROM `comments` GROUP BY `user_id`) `comments_by_user` ON `users`.`id` = `comments_by_user`.`user_id` LIMIT 1", $this->interpolateEloquent($users_with_comments_count));
    }

    public function testWhereInSubquerySql()
    {
        $users_having_comments = TestUser::whereInSubquery('id', TestComment::selectRaw('distinct(user_id)'));
        $this->assertEquals(TestComment::selectRaw('count(distinct(user_id)) users_count')->first()->users_count, $users_having_comments->count());
        $this->assertEquals("SELECT * FROM `users` WHERE `id` IN (SELECT DISTINCT(user_id) FROM `comments`)", $this->interpolateEloquent($users_having_comments));
    }

    public function testWhereNotInSubquerySql()
    {
        $users_without_comments = TestUser::whereNotInSubquery('id', TestComment::selectRaw('distinct(user_id)'));
        $this->assertEquals("SELECT * FROM `users` WHERE `id` NOT IN (SELECT DISTINCT(user_id) FROM `comments`)", $this->interpolateEloquent($users_without_comments));
    }

    public function testOrWhereInSubquery()
    {
        $users_having_comments = TestUser::where(function ($nested_query) {
            (new SubqueryMagicScope())->extend($nested_query);
            $nested_query->where('id', '<', 10);
            $nested_query->orWhereInSubquery('id', TestComment::selectRaw('distinct(user_id)'));
        });
        $this->assertEquals("SELECT * FROM `users` WHERE (`id` < 10 OR `id` IN (SELECT DISTINCT(user_id) FROM `comments`))", $this->interpolateEloquent($users_having_comments));
    }

    public function testOrWhereNotInSubquery()
    {
        $users_without_comments = TestUser::where(function ($nested_query) {
            (new SubqueryMagicScope())->extend($nested_query);
            $nested_query->where('id', '<', 10);
            $nested_query->orWhereNotInSubquery('id', TestComment::selectRaw('distinct(user_id)'));
        });
        $this->assertEquals("SELECT * FROM `users` WHERE (`id` < 10 OR `id` NOT IN (SELECT DISTINCT(user_id) FROM `comments`))", $this->interpolateEloquent($users_without_comments));
    }

    public function testComplexSubquery()
    {
        $complex_subquery = TestUser::selectRaw('users.name,filtered_members_with_stats.total_count')
            ->where(function ($nested_query) {
                (new SubqueryMagicScope())->extend($nested_query);
                $nested_query->where('id', '<', 10);
                $nested_query->orWhereNotInSubquery('id', TestComment::selectRaw('distinct(user_id)'));
            })->rightJoinSubquery(
                TestUser::selectRaw('user_id,comments_by_user.total_count')->leftJoinSubquery(
                    TestComment::selectRaw('user_id,count(*) total_count')
                        ->groupBy('user_id'),
                    'comments_by_user', function ($join) {
                    $join->on('users.id', '=', 'comments_by_user.user_id');
                }
                )->where('id', '<', 20),
                'filtered_members_with_stats', function ($join) {
                $join->on('users.id', '=', 'filtered_members_with_stats.user_id');
            }
            );
        $this->assertInstanceOf(TestUser::class, $complex_subquery->first());
        $this->assertEquals("SELECT users.name,filtered_members_with_stats.total_count FROM `users` RIGHT JOIN (SELECT user_id,comments_by_user.total_count FROM `users` LEFT JOIN (SELECT user_id,count(*) total_count FROM `comments` GROUP BY `user_id`) `comments_by_user` ON `users`.`id` = `comments_by_user`.`user_id` WHERE `id` < 10) `filtered_members_with_stats` ON `users`.`id` = `filtered_members_with_stats`.`user_id` WHERE (`id` < 20 OR `id` NOT IN (SELECT DISTINCT(user_id) FROM `comments`)) LIMIT 1", $this->interpolateEloquent($complex_subquery));
    }
}
