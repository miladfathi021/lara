<?php

namespace Tests\Feature;

use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ManageUserTest extends TestCase
{
    use RefreshDatabase;

    /** @test **/
    public function after_registering_a_new_user_it_should_get_a_unique_hashed_username()
    {
        $this->withoutExceptionHandling();

        $password = bcrypt('password');

        $user = [
            'name' => 'john',
            'email' => 'john@doe.com',
            'password' => $password,
            'password_confirmation' => $password
        ];


        $this->post('/register', $user);

        $this->assertDatabaseHas('users', [
            'name' => $user['name'],
            'email' => $user['email']
        ]);

        $user = User::first();

        $this->assertNotNull($user->username);
    }

    /** @test **/
    public function users_can_update_their_username()
    {
        $this->withoutExceptionHandling();

        $this->signIn();

        $user = create(User::class);

        $this->patch($user->path() . '/username', [
            'username' =>  'foobar'
        ]);

        $this->assertEquals('foobar', $user->fresh()->username);
    }

    /** @test **/
    public function username_is_required_for_update()
    {
        $this->signIn();

        $user = create(User::class);

        $this->patch($user->path() . '/username', [
            'username' =>  null
        ])->assertSessionHasErrors(['username']);
    }

    /** @test **/
    public function username_should_be_unique()
    {
        $this->signIn();

        $john = create(User::class);
        $david = create(User::class);

        $this->patch('/users/' . $john->id . '/username', [
            'username' =>  $david->username
        ])->assertSessionHasErrors(['username']);
    }

    /** @test **/
    public function username_can_get_updated_to_current_username_for_a_user()
    {
        $john = $this->signIn();

        $this->patch('/users/' . $john->id . '/username', [
            'username' =>  $john->username
        ])->assertSessionDoesntHaveErrors(['username']);
    }

    /** @test **/
    public function a_username_can_only_contains_meaningful_words_and_numbers_and_underline()
    {
        $john = $this->signIn();

        $this->patch($john->path() . '/username', [
            'username' => 'hello.'
        ])->assertSessionHasErrors(['username']);
    }
    //TODO
    // Update password needs an email confirmation.
//    /** @test **/
//    public function users_can_update_their_password()
//    {
//       $john = create(User::class);
//    }
}
