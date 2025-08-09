<?php

namespace Tests\Feature;

use Tests\TestCase;

class HomeTest extends TestCase
{
    public function test_guest_can_see_home_page()
    {
        $response = $this->get(route('home'));
        $response->assertStatus(200);
    }
}
