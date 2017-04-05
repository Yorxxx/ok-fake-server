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
     * Test: POST /api/authenticate.
     */
    public function given_existingUser_when_authenticate_Then_ReturnsToken()
    {
        $user = factory(App\User::class)->create(['password' => bcrypt('foo')]);

        $this->post('/api/authenticate', ['email' => $user->email, 'password' => 'foo'])
            ->seeJsonStructure(['token']);
    }

    /**
     * @test
     *
     * Test: POST /api/authenticate
     */
    public function given_nonExistingUser_when_authenticate_Then_Returns401() {
        $user = ["email" => "foo@bar.com", "password" => "pass"];

        $this->post('/api/authenticate', ['email' => "foo@bar.com", 'password' => 'foo'])
            ->seeStatusCode(401)
            ->seeText("invalid_credentials");
    }
}
