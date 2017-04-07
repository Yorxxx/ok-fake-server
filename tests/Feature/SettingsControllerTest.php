<?php

use App\User;

use Tests\BrowserKitTestCase;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class SettingsControllerTest extends BrowserKitTestCase
{
    use DatabaseMigrations;

    /**
     * @test
     * Test: GET: /api/settings
     */
    public function given_noAuthorization_When_getSettings_Then_Returns401()
    {
        $this->get('/api/settings')->seeStatusCode(401);
    }

    /**
     * @test
     * Test: GET /api/settings
     */
    public function given_authorizedUser_When_GetSettings_Then_ReturnsUserSettings() {

        $this->seed('UsersTableSeeder');
        $this->seed('SettingsTableSeeder');

        $user = factory(App\User::class)->create([
            'document' => '123456789',
            'doctype' => 'N',
            'password' => bcrypt('foo')]);

        factory(App\Setting::class)->create([
            'user_id' => $user->id
        ]);

        $this->get('/api/settings', $this->headers($user))
            ->seeJsonStructure([
                'data' => [
                    '*' => [
                        'id', 'language', 'email_notifications', 'sms_notifications', 'app_notifications'
                    ]
                ]
            ]);
    }
}
