<?php

use App\Enums\HoldStatus;
use App\Models\Order;
use App\Models\Hold;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('processes webhook only once for the same idempotency key', function () {
    $product = Product::factory()->create(['available_quantity' => 10]);
    $hold = Hold::create([
       'product_id' => $product->id,
        'quantity' => 5,
        'expiry_at' => now()->addMinutes(2), 
        'status'=> HoldStatus::ACTIVE
        ]);

    $payload = [
        'hold_id' => $hold->id,
        'status' => 'success',
        'idempotency' => 'unique-key1',
    ];

    $payload = [
        'hold_id' => $hold->id,
        'status' => 'feild',
        'idempotency' => 'unique-key1',
    ];

    $response1 = $this->postJson('/api/payments/webhook', $payload);
    $response2 = $this->postJson('/api/payments/webhook', $payload);

    expect($response1->json('message'))->toBe('Your order has been paid successfully. It hasn’t arrived yet, but we will deliver it soon.');
    expect($response2->json('message'))->toBe('Payment already processed.');
});



it('handles early webhook before order is created', function () {
  $product = Product::factory()->create(['available_quantity' => 10]);
    $hold = Hold::create([
       'product_id' => $product->id,
        'quantity' => 5,
        'expiry_at' => now()->addMinutes(2), 
        'status'=> HoldStatus::ACTIVE
        ]);

    $payload = [
        'hold_id' => $hold->id,
        'status' => 'success',
        'idempotency' => 'early-key212',
    ];

    $response = $this->postJson('/api/payments/webhook', $payload);

    expect($response->json('message'))
        ->toBe("Your order has been paid successfully. It hasn’t arrived yet, but we will deliver it soon.");

    $orderResponse = $this->postJson('/api/orders', [
        'hold_id' => $hold->id,
        'idempotency' => 'order-key1212'
    ]);
    expect($orderResponse->json('data.payment'))->toBe('paid');
});


