<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

it('serves the v1 health endpoint', function () {
    $this->getJson('/api/v1/health')
        ->assertOk()
        ->assertExactJson(['status' => 'ok', 'version' => 'v1']);
});

it('does not wrap api routes in the response cache middleware', function () {
    $route = collect(app('router')->getRoutes()->getRoutes())
        ->first(fn ($r) => $r->uri() === 'api/v1/health');

    expect($route)->not->toBeNull();
    expect($route->gatherMiddleware())->not->toContain('cacheResponse');
});

it('names v1 routes under the api.v1 prefix', function () {
    expect(app('router')->getRoutes()->getByName('api.v1.health'))->not->toBeNull();
});

it('counts queries through the assertQueryCount helper', function () {
    $result = assertQueryCount(1, function () {
        return DB::table('users')->count();
    });

    expect($result)->toBe(0);
});
