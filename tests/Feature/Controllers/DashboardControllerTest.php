<?php

declare(strict_types=1);

use App\Models\User;
use App\Http\Controllers\DashboardController;

covers(DashboardController::class);

beforeEach(function (): void {
    $this->user = User::factory()->create();
});

test('guests cannot access dashboard', function (): void {
    $response = $this->get(route('dashboard'));

    $response->assertRedirect(route('login'));
});

test('authenticated users are redirected from dashboard to dopa', function (): void {
    $response = $this->actingAs($this->user)
        ->get(route('dashboard'));

    $response->assertRedirect(route('dopa.dashboard'));
});

test('dashboard redirect returns correct status code', function (): void {
    $response = $this->actingAs($this->user)
        ->get(route('dashboard'));

    $response->assertStatus(302);
});
