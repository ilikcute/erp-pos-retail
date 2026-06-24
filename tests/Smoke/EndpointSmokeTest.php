<?php

use App\Models\System\User;

test('smoke test all pages load without javascript errors', function () {
    $user = User::factory()->create();

    $pages = [
        '/dashboard',
        '/pos',
        '/inventory',
        '/pricing',
        '/products',
        '/suppliers',
        '/customers',
        '/purchasing',
        '/accounting',
        '/reporting',
        '/loyalty',
        '/promotions',
    ];

    foreach ($pages as $page) {
        $this->actingAs($user)
            ->get($page)
            ->assertSuccessful()
            ->assertNoJavaScriptErrors();
    }
})->skip('Requires all pages to exist');

test('api endpoints return valid json responses', function () {
    $user = User::factory()->create();

    $endpoints = [
        'GET' => [
            '/api/v1/system/settings',
            '/api/v1/master-data/suppliers',
            '/api/v1/master-data/customers',
            '/api/v1/products',
            '/api/v1/pricing/price-lists',
        ],
    ];

    foreach ($endpoints['GET'] as $endpoint) {
        $this->actingAs($user)
            ->getJson($endpoint)
            ->assertSuccessful()
            ->assertJsonStructure(['success', 'data']);
    }
});

test('authentication required for protected endpoints', function () {
    $this->getJson('/api/v1/products')
        ->assertUnauthorized();

    $this->postJson('/api/v1/products', [])
        ->assertUnauthorized();
});

test('permission validation on sensitive endpoints', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->deleteJson('/api/v1/system/users/999')
        ->assertForbidden();
});
