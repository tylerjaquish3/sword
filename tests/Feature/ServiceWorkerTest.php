<?php

namespace Tests\Feature;

use Tests\TestCase;

class ServiceWorkerTest extends TestCase
{
    public function test_service_worker_file_is_served_as_javascript(): void
    {
        $response = $this->get('/sw.js');

        $response->assertOk();
        $this->assertStringContainsString('javascript', $response->headers->get('Content-Type'));
    }

    public function test_offline_reader_route_exists_for_the_service_worker_to_precache(): void
    {
        $response = $this->get(route('offline-reader.index'));

        // Guests get redirected to login (it's behind auth), not a 404 — confirms the route is registered.
        $response->assertRedirect(route('login'));
    }
}
