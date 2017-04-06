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
            'phone' => "123456789",
            'name' => 'Foo Bar',
            'document' => '123456789',
            'doctype' => 'N',
            'password' => bcrypt('foo')]);

        $this->post('/api/authenticate', [
            'document' => '123456789',
            'password' => 'foo',
            'first_name' => 'Foo',
            'doctype' => 'N'])
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

    /**
     * @test
     * Test: GET /api/users/me
     */
    public function given_authorizedUserWithoutOptionalValues_When_getAuthenticatedUser_Then_ReturnsUserWithoutOptionalKeys()
    {
        $user = factory(App\User::class)->create([
            'document' => '123456789',
            'doctype' => 'N',
            'email' => 'foo@bar.com',
            'phone' => null,
            'password' => bcrypt('foo')]);

        $headers = $this->headers($user);
        self::assertArrayHasKey("Accept", $headers);
        self::assertArrayHasKey("Authorization", $headers);
        $this->get('/api/users/me', $headers)
            ->seeJson([
                'username'    => $user->document,
                'documentType' => $user->doctype,
                'id' => $user->id,
                'email' => $user->email,
            ])
            ->dontSeeText('prefix')
            ->dontSeeText('phone')
            ->seeStatusCode(200);
    }
}
