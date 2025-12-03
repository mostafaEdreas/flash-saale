<?php

use App\Models\Product;
use App\Models\Hold;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('prevents overselling when multiple holds are created concurrently', function () {
    $product = Product::factory()->create([
        'available_quantity' => 5,
    ]);

    $responses = [];

    $responses[] = $this->postJson('/api/holds', [
        'product_id' => $product->id,
        'quantity' => 3,
        'idempotency' => 'key1',
    ]);

    $responses[] = $this->postJson('/api/holds', [
        'product_id' => $product->id,
        'quantity' => 3,
        'idempotency' => 'key2',
    ]);

    expect($responses[0]->status())->toBe(200);
    expect($responses[1]->status())->toBe(422);
});



it('restores product quantity when hold expires', function () {
    $product = Product::factory()->create(['available_quantity' => 10]);

    $hold = Hold::create([
        'product_id' => $product->id,
        'quantity' => 5,
        'expiry_at' => now()->subMinute(), 
        'status' => 'active',
    ]);
$product->decrement('available_quantity', 5);
    app(\App\Http\Controllers\OrderController::class)->expireHolds();

    $product->refresh();
    expect($product->available_quantity)->toBe(10);
});

