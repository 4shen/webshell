<?php

declare(strict_types=1);

/*
 * +----------------------------------------------------------------------+
 * |                          ThinkSNS Plus                               |
 * +----------------------------------------------------------------------+
 * | Copyright (c) 2016-Present ZhiYiChuangXiang Technology Co., Ltd.     |
 * +----------------------------------------------------------------------+
 * | This source file is subject to enterprise private license, that is   |
 * | bundled with this package in the file LICENSE, and is available      |
 * | through the world-wide-web at the following url:                     |
 * | https://github.com/slimkit/plus/blob/master/LICENSE                  |
 * +----------------------------------------------------------------------+
 * | Author: Slim Kit Group <master@zhiyicx.com>                          |
 * | Homepage: www.thinksns.com                                           |
 * +----------------------------------------------------------------------+
 */

namespace SlimKit\PlusFeed\Tests\Feature\API2;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Zhiyi\Plus\Models\Ability as AbilityModel;
use Zhiyi\Plus\Models\Role as RoleModel;
use Zhiyi\Plus\Models\User as UserModel;
use Zhiyi\Plus\Tests\TestCase;

class SendImageAndTextFeedTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Create the test need user.
     *
     * @return \Zhiyi\Plus\Models\User
     */
    protected function createUser(): UserModel
    {
        $user = factory(UserModel::class)->create();
        $ability = AbilityModel::where('name', 'feed-post')->firstOr(function () {
            return factory(AbilityModel::class)->create([
                'name' => 'feed-post',
            ]);
        });
        $role = factory(RoleModel::class)
            ->create([
                'name' => 'test',
            ]);
        $role
            ->abilities()
            ->sync($ability);
        $user->roles()->sync($role);

        return $user;
    }

    /**
     * 获取图片id.
     *
     * @param $user
     * @return array
     */
    private function getFileId($user)
    {
        $file = $this
            ->actingAs($user, 'api')
            ->json('POST', '/api/v2/files', [
                'file' => UploadedFile::fake()->image('test.jpg')->size(0.00),
            ]
            )
            ->decodeResponseJson();

        return $file['id'];
    }

    /**
     * Test send a image feed.
     *
     * @return void
     */
    public function testSendPublic()
    {
        $user = $this->createUser();
        $response = $this
            ->actingAs($user, 'api')
            ->json('POST', '/api/v2/feeds', [
                'feed_content' => 'test',
                'feed_from' => 5,
                'feed_mark' => intval(time().rand(1000, 9999)),
                'images' => [
                    ['id' => $this->getFileId($user)],
                ],
            ]);
        $response
            ->assertStatus(201)
            ->assertJsonStructure(['id', 'message']);
    }

    /**
     * Test not send ability user send feed.
     *
     * @return void
     */
    public function testSendNotSendAbility()
    {
        $user = factory(UserModel::class)->create();

        $response = $this
            ->actingAs($user, 'api')
            ->json('POST', '/api/v2/feeds', []);

        $response->assertStatus(403);
    }
}
