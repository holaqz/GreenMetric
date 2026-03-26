<?php

use App\Models\User;
use App\Models\Water;

test('example', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
});

test('PATCH water record updates single field', function () {
    $user = User::factory()->create();
    $water = Water::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->patchJson(route('water.update', $water->id), [
        'wr_3' => 75,
    ]);

    $response->assertStatus(200);
    $this->assertDatabaseHas('waters', [
        'id' => $water->id,
        'wr_3' => 75,
    ]);
});

test('PATCH water record updates multiple fields', function () {
    $user = User::factory()->create();
    $water = Water::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->patchJson(route('water.update', $water->id), [
        'wr_1' => 'В разработке',
        'wr_3' => 50,
        'wr_5' => 'тестовое значение',
    ]);

    $response->assertStatus(200);
    $this->assertDatabaseHas('waters', [
        'id' => $water->id,
        'wr_1' => 'В разработке',
        'wr_3' => 50,
        'wr_5' => 'тестовое значение',
    ]);
});

test('PATCH water record validates wr_3 range', function () {
    $user = User::factory()->create();
    $water = Water::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->patchJson(route('water.update', $water->id), [
        'wr_3' => 150,
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('wr_3');
});

test('PATCH water record validates wr_4 range', function () {
    $user = User::factory()->create();
    $water = Water::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->patchJson(route('water.update', $water->id), [
        'wr_4' => -5,
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('wr_4');
});

test('bulk update updates multiple records', function () {
    $user = User::factory()->create();
    $water1 = Water::factory()->create(['user_id' => $user->id, 'wr_3' => 10]);
    $water2 = Water::factory()->create(['user_id' => $user->id, 'wr_3' => 20]);

    $response = $this->actingAs($user)->postJson(route('water.bulk-update'), [
        'updates' => [
            ['id' => $water1->id, 'wr_3' => 75],
            ['id' => $water2->id, 'wr_1' => 'В разработке', 'wr_5' => 'новое'],
        ],
    ]);

    $response->assertStatus(200);
    $response->assertJson(['success' => true, 'message' => 'Записи обновлены']);

    $this->assertDatabaseHas('waters', ['id' => $water1->id, 'wr_3' => 75]);
    $this->assertDatabaseHas('waters', [
        'id' => $water2->id,
        'wr_1' => 'В разработке',
        'wr_5' => 'новое',
    ]);
});

test('bulk update validates required updates array', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson(route('water.bulk-update'), []);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('updates');
});

test('bulk update validates water id exists', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson(route('water.bulk-update'), [
        'updates' => [
            ['id' => 99999, 'wr_3' => 50],
        ],
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('updates.0.id');
});
