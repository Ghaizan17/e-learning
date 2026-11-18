<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * The root URL redirects visitors to the login page.
     */
    public function test_the_root_url_redirects_to_login(): void
    {
        $response = $this->get('/');

        $response->assertRedirect(Route::has('login') ? route('login') : '/login');
    }
}
