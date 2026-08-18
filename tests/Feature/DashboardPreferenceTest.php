<?php

use App\Models\User;

it('stores and resets dashboard widgets for each signed in user', function () {
    $user = User::factory()->create();

    $layout = [
        'order' => ['my_work', 'open_tasks', 'active_employees'],
        'hidden' => ['active_employees'],
    ];

    $this->actingAs($user)
        ->putJson(route('preferences.dashboard.update'), $layout)
        ->assertOk()
        ->assertJson(['saved' => true]);

    expect($user->fresh()->dashboard_preferences)->toBe($layout);

    $this->actingAs($user)
        ->deleteJson(route('preferences.dashboard.destroy'))
        ->assertOk()
        ->assertJson(['reset' => true]);

    expect($user->fresh()->dashboard_preferences)->toBeNull();
});

it('rejects unknown dashboard widget keys', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->putJson(route('preferences.dashboard.update'), [
        'order' => ['unknown_widget'],
        'hidden' => [],
    ])->assertUnprocessable()->assertJsonValidationErrors('order.0');
});
