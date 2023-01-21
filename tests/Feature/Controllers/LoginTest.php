<?php

namespace Tests\Feature\Controllers;

use App\Http\Controllers\Login;
use App\Models\SocialAccount;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Tests\TestCase;

class LoginTest extends TestCase
{

    use RefreshDatabase;

    /**
     * setup
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /**
     * test for validation errors
     * @return void
     */
    public function testLoginThrowsProperValidationErrors(): void
    {
        $data = [
            "email" => "someting.com",
            "password" => "123456"
        ];

        //make the request to login route
        $response = $this->postJson(route("auth.login"), $data);

        //there must be validation errors for both fields
        $response->assertJsonValidationErrors([
                "email" => "The email must be a valid email address.",
                "password" => "The password must contain at least one uppercase and one lowercase letter.",
            ]);
        $response->assertJsonValidationErrors([
            "password" => "The password must contain at least one letter.",
        ]);
        $response->assertJsonValidationErrors([
            "password" => "The password must contain at least one symbol."
        ]);

        $data = [
            "email" => "null",
            "password" => "P@ssw0rd"
        ];

        //make the request to login route
        $response = $this->postJson(route("auth.login"), $data);
        $response->assertJsonValidationErrors(["email"]);

        $data = [
            "email" => "",
            "password" => ""
        ];

        //make the request to login route
        $response = $this->postJson(route("auth.login"), $data);
        $response->assertJsonValidationErrors(["email", "password"]);

        $data = [
            "email" => null,
            "password" => null
        ];

        //make the request to login route
        $response = $this->postJson(route("auth.login"), $data);
        $response->assertJsonValidationErrors(["email", "password"]);

        $data = [
            "email" => 0,
            "password" => 0
        ];

        //make the request to login route
        $response = $this->postJson(route("auth.login"), $data);
        $response->assertJsonValidationErrors(["email", "password"]);
    }

    /**
     * pass valid email and password
     * that does not exist in the db
     * @return void
     */
    public function testLoginWithInvalidUser(): void
    {
        //this user does not exist in the db
        $data = [
            "email" => "some_email@gmail.com",
            "password" => "P@ssw0rd"
        ];

        //make the request to login route
        $response = $this->postJson(route("auth.login"), $data);

        //look for validation errors for email
        $response->assertJsonValidationErrors([
            "email" => "The selected email is invalid."
        ]);
    }

    /**
     * test with correct email that exists
     * and wrong password
     * @return void
     */
    public function testLoginWithValidEmailAndWrongPassword(): void
    {
        $data = [
            "email" => $this->user->email,
            "password" => "P@ssw0rd"
        ];

        //make login request
        $response = $this->postJson(route("auth.login"), $data);

        //expected response => Invalid Credentials
        $response->assertJsonFragment([
            "message" => "Invalid Credentials"
        ]);
    }


    /**
     * test with correct credentials
     * @return void
     */
    public function testLoginWithValidEmailAndPassword(): void
    {
        $user = User::factory()->create(["password" => bcrypt($password = "P@ssw0rd")]);

        $data = [
            "email" => $user->email,
            "password" => $password //this password is not hashed
        ];

        //make login request
        $response = $this->postJson(route("auth.login"), $data);

        $response->assertStatus(200);

        //assert correct user is returned
        $response->assertJson([
            'message' => 'Logged in Successfully.',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email
            ]
        ]);

        //assert accessToken is set
        $this->assertNotEmpty($response->json()["user"]["accessToken"]);

        //assert balance is set
        $this->assertNotEmpty($response->json()["user"]["balance"]);

        //assert stripe intent is set
        $this->assertNotEmpty($response->json()["user"]["intent"]);

        //assert correct user is authenticated
        $this->assertAuthenticatedAs($user);
    }


    /**
     * test with valid email
     * @return void
     */
    public function testGetTemporaryUsernameWithValidEmail(): void
    {
        $email = 'myname@gmail.com';

        $username = (new Login())->getTemporaryUsername($email);

        $this->assertTrue(str_contains($username, 'myname'));
    }


    /**
     * test with empty email
     * @return void
     */
    public function testGetTemporaryUsernameWithEmptyEmail(): void
    {
        $email = '';
        $username = (new Login())->getTemporaryUsername($email);

        $this->assertEquals('Email is required', $username);
    }


    /**
     * test with github
     * mock the Socialite to obtain the user
     * @return void
     */
    public function testSocialLoginWithValidData(): void
    {
        $socialProviderId = 1234567890;
        $socialProviderName = "github";
        $email = "test@example.com";
        $name = "Test User";

        //(object) is equivalent to new \stdClass()
        $socialUser = (object) [
            'id' => $socialProviderId,
            'email' => $email,
            'name' => $name
        ];

        //mock the socialite facade to get the user
        Socialite::shouldReceive('driver')->with($socialProviderName)->andReturnSelf()
            ->shouldReceive('stateless')->andReturnSelf()
            ->shouldReceive('user')->andReturn($socialUser);

        //make the login request
        $response = $this->getJson(route("auth.social.callback", ["socialName" => $socialProviderName]));

        // Assert
        $response->assertJsonStructure([
            "user" => [
                "name", "email", "username", "accessToken", "balance", "intent"
            ]
        ]);
        $response->assertStatus(200);

        $this->assertDatabaseHas(User::class, [
            'email' => $email,
            'name' => $name,
        ]);

        $this->assertDatabaseHas(SocialAccount::class, [
            'user_id' => $response->json()["user"]["id"],
            'social_id' => $socialProviderId,
            'social_provider' => $socialProviderName,
        ]);
    }


    /**
     * when social user is null
     * when invalid code is provided by the auth provider
     * or when the code has expired
     * @return void
     */
    public function testSocialLoginReturnsErrorWhenSocialUserIsNull(): void
    {
        //mock this $socialUser = Socialite::driver($socialName)->stateless()->user()
        //to return null
        Socialite::shouldReceive('driver')->with('facebook')->andReturnSelf();
        Socialite::shouldReceive('stateless')->andReturnSelf();
        Socialite::shouldReceive('user')->andReturnNull();

        //make login request
        //expecting Invalid Code error
        $response = $this->get(route('auth.social.callback', ['socialName' => 'facebook']));

        //assert response is error message
        $response->assertJson([
            "message" => "Invalid code."
        ]);

        //assert status for bad request
        $response->assertStatus(400);
    }


    /**
     * test with invalid social name
     * @return void
     */
    public function testSocialLoginThrowsValidationErrorsWithProviderNameInvalid(): void
    {
        $socialProviderName = "something";

        $response = $this->getJson(route("auth.social.callback",  ["socialName" => $socialProviderName]));

        $response->assertJsonValidationErrors(["social" => "The selected social name is invalid."]);
    }

}
