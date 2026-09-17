<?php

namespace Tests\Feature;

use Tests\TestCase;

class AuthPageTest extends TestCase
{
    public function test_login_page_can_be_accessed(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_register_page_can_be_accessed(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }
}
