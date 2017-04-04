<?php

use Tests\BrowserKitTestCase;
use App\Fruit;
use Illuminate\Foundation\Auth\User;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class AuthenticationTest extends BrowserKitTestCase
{
    use DatabaseMigrations;

    /**
     * @test
     *
     * Test: GET /api/authenticate.
     */
    public function it_authenticate_a_user()
    {
        $user = factory(App\User::class)->create(['password' => bcrypt('foo')]);

        $this->post('/api/authenticate', ['email' => $user->email, 'password' => 'foo'])
            ->seeJsonStructure(['token']);
    }
}
