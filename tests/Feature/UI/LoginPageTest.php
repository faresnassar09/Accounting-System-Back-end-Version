<?php

namespace Tests\Feature\Ui;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;


class LoginPageTest extends TestCase{
    use RefreshDatabase;

    public function test_login_page_can_be_loaded(){

    $response = $this->get('http://tenant1.app.test:5173/login');

    $response->assertStatus(200);

    } 

}
