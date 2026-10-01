<?php

namespace Tests\Feature;

use Tests\TestCase;

class ManifestTest extends TestCase
{
    public function test_manifest_is_served_as_valid_json_with_required_fields(): void
    {
        $response = $this->get('/manifest.json');

        $response->assertOk();
        $manifest = json_decode($response->getContent(), true);

        $this->assertSame('Sword', $manifest['name']);
        $this->assertSame('standalone', $manifest['display']);
        $this->assertCount(2, $manifest['icons']);
        $this->assertSame('192x192', $manifest['icons'][0]['sizes']);
        $this->assertSame('512x512', $manifest['icons'][1]['sizes']);
    }

    public function test_login_page_links_the_manifest(): void
    {
        $response = $this->get(route('login'));

        $response->assertSee('<link rel="manifest" href="/manifest.json">', false);
    }
}
