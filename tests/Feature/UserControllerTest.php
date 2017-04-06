<?php

use Tests\BrowserKitTestCase;
use App\Fruit;
use Illuminate\Foundation\Auth\User;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class UserControllerTest extends BrowserKitTestCase
{
    use DatabaseMigrations;

    /**
     * @test
     *
     * Test: POST /api/authenticate
     */
    public function given_missingdocument_when_authenticate_Then_Returns400() {

        $this->post('/api/authenticate', ['password' => 'foo', 'doctype' => 'P'])
            ->seeStatusCode(400);
    }

    /**
     * @test
     *
     * Test: POST /api/authenticate
     */
    public function given_missingdoctype_when_authenticate_Then_Returns400() {

        $this->post('/api/authenticate', ['document' => 'document', 'password' => 'foo'])
            ->seeStatusCode(400);
    }

    /**
     * @test
     *
     * Test: POST /api/authenticate.
     */
    public function given_existingUser_when_authenticate_Then_ReturnsToken()
    {
        $user = factory(App\User::class)->create([
            'first_name' => 'Foo',
            'phone_preffix' => "+34",
            'phone' => 123456789,
            'email' => 'foo@bar.com',
            'name' => 'Foo Bar',
            'document' => '123456789',
            'doctype' => 'N',
            'password' => bcrypt('foo')]);

        $this->post('/api/authenticate', ['document' => $user->document, 'password' => 'foo', 'doctype' => $user->doctype])
            ->seeJsonStructure(['token']);
    }

    /**
     * @test
     * Test: POST /api/authenticate
     */
    public function given_nonExistingUser_when_authenticate_Then_Returns401() {
        $this->post('/api/authenticate', ['document' => "foo", 'password' => 'foo', 'doctype' => 'P'])
            ->seeStatusCode(401)
            ->seeText("invalid_credentials");
    }

    /**
     * @test
     * Test: POST /api/authenticate
     */
    public function given_unsupportedDocType_When_authenticate_Then_Returns400() {
        $this->post('/api/authenticate', ['document' => "foo", 'password' => 'foo', 'doctype' => 'A'])
            ->seeStatusCode(400)
            ->seeText("Unsupported doctype");
    }

    /**
     * @test
     * Test: GET /api/users/me
     */
    public function given_unauthorizedUser_when_getMe_Then_Returns401() {
        $this->get('/api/users/me')
            ->seeStatusCode(401);
    }

    /**
     * @test
     * Test: GET /api/users/me
     */
    public function given_authorizedUser_when_getAuthenticatedUser_Then_ReturnsUser() {
        /*$user = factory(App\User::class)->create([
            'first_name' => 'Foo',
            'phone_preffix' => "+34",
            'phone' => 123456789,
            'email' => 'foo2@bar.com',
            'name' => 'Foo Bar',
            'document' => '123456789',
            'doctype' => 'N',
            'password' => bcrypt('foo')]);*/
        $user = factory(App\User::class)->create(['password' => bcrypt('foo')]);

        $headers = $this->headers($user);
        self::assertArrayHasKey("Accept", $headers);
        self::assertArrayHasKey("Authorization", $headers);
        $this->get('/api/users/me', $this->headers($user))
            ->seeStatusCode(202);
    }

    /**
     * Return request headers needed to interact with the API.
     *
     * @return Array array of headers.
     */
    protected function headers($user = null)
    {
        $headers = ['Accept' => 'application/json'];

        if (!is_null($user)) {
            $token = JWTAuth::fromUser($user);
            JWTAuth::setToken($token);
            $headers['Authorization'] = 'Bearer '.$token;
        }

        return $headers;
    }
}
