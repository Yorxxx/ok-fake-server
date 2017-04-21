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
     * @POST('/api/login')
     * Authenticating without specifying the document value, throws error
     */
    public function given_missingdocument_when_authenticate_Then_Returns400() {

        $this->post('/api/login', ['password' => 'foo', 'doctype' => 'P'])
            ->seeStatusCode(400)
            ->seeText("The document field is required");
    }

    /**
     * @test
     *
     * @POST('/api/login')
     * Authenticating without doctype throws error
     */
    public function given_missingdoctype_when_authenticate_Then_Returns400() {

        $this->post('/api/login', ['document' => 'document', 'password' => 'foo'])
            ->seeStatusCode(400)
            ->seeText("The doctype field is required");
    }

    /**
     * @test
     *
     * @POST('/api/login')
     * Authenticating without password throws error
     */
    public function given_missingpassword_when_authenticate_Then_Returns400() {

        $this->post('/api/login', ['document' => 'document', 'doctype' => 'P'])
            ->seeStatusCode(400)
            ->seeText("The password field is required");
    }

    /**
     * @test
     * @POST('/api/login')
     * Authenticating with an invalid doctype, throws an Error
     */
    public function given_unsupportedDocType_When_authenticate_Then_Returns400() {
        $this->post('/api/login', ['document' => "foo", 'password' => 'foo', 'doctype' => 'A'])
            ->seeStatusCode(400)
            ->seeText("The selected doctype is invalid.");
    }


    /**
     * @test
     * @POST ('/api/login')
     * Authenticating with non existing user throws an error
     */
    public function given_nonExistingUser_when_authenticate_Then_Returns401() {
        $this->post('/api/login', ['document' => "foo", 'password' => '5780', 'doctype' => 'P'])
            ->seeStatusCode(401)
            ->seeText("Invalid credentials");
    }

    /**
     * @test
     * @POST ('/api/login')
     * Authenticating with an invalid password length, should throw an error
     */
    public function given_invalidPassword_when_authenticate_Then_Returns400() {
        $this->post('/api/login', ['document' => "foo", 'password' => 'foo', 'doctype' => 'P'])
            ->seeStatusCode(400)
            ->seeText("The password must be 4 characters.");
    }

    /**
     * @test
     *
     * @POST('/api/login')
     * Authenticating with valid data should return authorization token.
     */
    public function given_existingUser_when_authenticate_Then_ReturnsToken()
    {
        factory(App\User::class)->create([
            'document' => '123456789',
            'doctype' => 'N',
            'password' => bcrypt('1111')]
        );

        $this->post('/api/login', [
            'document' => '123456789',
            'password' => '1111',
            'doctype' => 'N'])
            ->seeJsonStructure(['token']);
    }

    /**
     * @test
     * Test: GET /api/users/me
     * Requesting user detail without authorization, throws error
     */
    public function given_unauthorizedUser_when_getMe_Then_Returns401() {
        $this->get('/api/users/me')
            ->seeStatusCode(401);
    }

    /**
     * @test
     * Test: GET /api/users/me
     * Requesting user detail, returns user info
     */
    public function given_authorizedUser_when_getAuthenticatedUser_Then_ReturnsUser() {
        $user = factory(App\User::class)->create([
            'document' => '123456789',
            'doctype' => 'N',
            'name' => 'Foo Bar',
            'email' => 'foo@bar.com',
            'phone' => '+34-646547055',
            'password' => bcrypt('foo')]);

        $headers = $this->headers($user);
        self::assertArrayHasKey("Accept", $headers);
        self::assertArrayHasKey("Authorization", $headers);
        $this->get('/api/users/me', $headers)
            ->seeJson([
                'username'    => $user->document,
                'documentType' => $user->doctype,
                'id' => $user->id,
                'first_name' => 'Foo',
                'email' => $user->email,
                'phone' => [
                    'prefix' => '+34',
                    'phone' => '646547055'
                ]
            ])
            ->seeStatusCode(200);
    }
}
