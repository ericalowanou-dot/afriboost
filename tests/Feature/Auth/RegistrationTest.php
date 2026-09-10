<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/inscription');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register_step_one(): void
    {
        $response = $this->post('/inscription', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '+22890000001',
            'password' => 'abc',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('register.step2', absolute: false));
    }
}
