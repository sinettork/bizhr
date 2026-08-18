<?php

use App\Models\User;

it('stores each users visible-table preference on their account', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->putJson(route('preferences.table-columns.update'), ['table' => ':employees:0', 'hidden_columns' => [2, 4]])
        ->assertOk()
        ->assertJsonPath('saved', true);

    expect($user->fresh()->table_preferences)->toMatchArray([':employees:0' => [2, 4]]);
});
