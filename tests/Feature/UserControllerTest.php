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
        $user = factory(App\User::class)->create(['email' => 'foo@bar.com-P', 'password' => bcrypt('foo')]);

        $this->post('/api/authenticate', ['document' => 'foo@bar.com', 'password' => 'foo', 'doctype' => 'P'])
            ->seeJsonStructure(['token']);
    }

    /**
     * @test
     * Test: POST /api/authenticate
     */
    public function given_nonExistingUser_when_authenticate_Then_Returns401() {
        $this->post('/api/authenticate', ['document' => "foo@bar.com", 'password' => 'foo', 'doctype' => 'P'])
            ->seeStatusCode(401)
            ->seeText("invalid_credentials");
    }

    /**
     * @test
     * Test: POST /api/authenticate
     */
    public function given_unsupportedDocType_When_authenticate_Then_Returns400() {
        $this->post('/api/authenticate', ['document' => "foo@bar.com", 'password' => 'foo', 'doctype' => 'A'])
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
        $user = factory(App\User::class)->create(['password' => bcrypt('foo')]);

        $this->get('/api/users/me', $this->headers($user))
            ->seeStatusCode(202);
//            ->seeJson([
//                'data' => [
//                    'id'        => $user->id,
//                    'name'      => $user->name,
//                    'color'     => "Green",
//                    'weight'    => "150 grams",
//                    'delicious' => true
//                ]
//            ]);
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
