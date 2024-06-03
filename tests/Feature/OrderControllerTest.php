<?php

namespace Tests\Feature;

use App\Events\OrderPlaced;
use App\Events\StockUpdated;
use App\Listeners\LowStockNotification;
use App\Listeners\UpdateStock;
use App\Mail\LowStockAlert;
use App\Models\Ingredient;
use Database\Seeders\ProductSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;
class OrderControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(ProductSeeder::class);
    }

    /**
     * Test placing an order successfully.
     */
    public function test_places_order_successfully(): void
    {
        $response = $this->postJson('/api/placeorder', [
            'products' => [
                ['product_id' => 1, 'quantity' => 2],
            ]
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'products' => [
                        '*' => [
                            'id',
                            'name',
                            'quantity',
                        ]
                    ]
                ],
            ]);

        $this->assertDatabaseHas('orders', ['id' => $response->json('data.id')]);
        $this->assertDatabaseHas('order_items', [
            'order_id' => $response->json('data.id'),
            'product_id' => $response->json('data.products.0.id'),
            'quantity' => $response->json('data.products.0.quantity'),
        ]);
        $this->assertDatabaseHas('ingredients', ['name' => 'Beef', 'available' => 19700]);
        $this->assertDatabaseHas('ingredients', ['name' => 'Cheese', 'available' => 4940]);
        $this->assertDatabaseHas('ingredients', ['name' => 'Onion', 'available' => 960]);
    }

    /**
     * Test that the OrderPlaced event is dispatched.
     */
    public function test_dispatches_order_placed_event(): void
    {
        Event::fake(OrderPlaced::class);

        $this->postJson('/api/placeorder', [
            'products' => [
                ['product_id' => 1, 'quantity' => 2],
            ]
        ]);

        Event::assertDispatched(OrderPlaced::class);
        Event::assertListening(
            OrderPlaced::class,
            UpdateStock::class
        );
    }

    /**
     * Test that the StockUpdated event is dispatched.
     */
    public function test_dispatches_stock_updated_event(): void
    {
        Event::fake(StockUpdated::class);

        $this->postJson('/api/placeorder', [
            'products' => [
                ['product_id' => 1, 'quantity' => 2],
            ]
        ]);

        Event::assertDispatched(StockUpdated::class);
    }

    /**
     * Test that the LowStockNotification listener is attached.
     */
    public function test_attaches_low_stock_notification_listener(): void
    {
        Event::fake(StockUpdated::class);

        $ingredient = Ingredient::whereName('Beef')->first();
        $ingredient->update(['available' => $ingredient->stock / 2]);

        $this->postJson('/api/placeorder', [
            'products' => [
                ['product_id' => 1, 'quantity' => 2],
            ]
        ]);

        Event::assertListening(
            StockUpdated::class,
            LowStockNotification::class
        );
    }

    /**
     * Test the low stock notification functionality.
     */
    public function test_sends_low_stock_notification(): void
    {
        Mail::fake();

        $ingredient = Ingredient::whereName('Beef')->first();
        $ingredient->update(['available' => $ingredient->stock / 2]);

        $response = $this->postJson('/api/placeorder', [
            'products' => [
                ['product_id' => 1, 'quantity' => 2],
            ]
        ]);

        $this->assertDatabaseHas('ingredients', ['name' => 'Beef', 'low_stock' => 1]);

        Mail::assertQueued(LowStockAlert::class);

        $this->postJson('/api/placeorder', [
            'products' => [
                ['product_id' => 1, 'quantity' => 2],
            ]
        ]);

        Mail::assertQueuedCount(1);
    }

    /**
     * Test placing an order with insufficient stock.
     */
    public function test_fails_to_place_order_due_to_insufficient_stock(): void
    {
        $ingredient = Ingredient::whereName('Beef')->first();
        $ingredient->update(['available' => 100]); // Set available stock to less than required

        $response = $this->postJson('/api/placeorder', [
            'products' => [
                ['product_id' => 1, 'quantity' => 2],
            ]
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'message' => __('Ingredient :ingredient is out of stock.', ['ingredient' => $ingredient->name]),
            ]);

        $this->assertDatabaseMissing('orders', ['id' => $response->json('data.id')]);
    }

    /**
     * Test placing multiple orders and stock updates correctly.
     */
    public function test_places_multiple_orders_and_updates_stock_correctly(): void
    {
        $this->postJson('/api/placeorder', [
            'products' => [
                ['product_id' => 1, 'quantity' => 2],
            ]
        ]);

        $this->assertDatabaseHas('ingredients', ['name' => 'Beef', 'available' => 19700]);

        $this->postJson('/api/placeorder', [
            'products' => [
                ['product_id' => 1, 'quantity' => 1],
            ]
        ]);

        $this->assertDatabaseHas('ingredients', ['name' => 'Beef', 'available' => 19550]);
    }

    /**
     * Test that low stock notification is not sent repeatedly.
     */
    public function test_does_not_send_duplicate_low_stock_notifications(): void
    {
        Mail::fake();

        $ingredient = Ingredient::whereName('Cheese')->first();
        $ingredient->update(['available' => 2500]);

        $this->postJson('/api/placeorder', [
            'products' => [
                ['product_id' => 1, 'quantity' => 2], // Should trigger low stock notification
            ]
        ]);

        Mail::assertQueued(LowStockAlert::class);

        $this->postJson('/api/placeorder', [
            'products' => [
                ['product_id' => 1, 'quantity' => 1], // Should not trigger another low stock notification
            ]
        ]);

        Mail::assertQueuedCount(1);
    }

    /**
     * Test order placement with different product quantities.
     */
    public function test_places_order_with_varied_quantities(): void
    {
        $response = $this->postJson('/api/placeorder', [
            'products' => [
                ['product_id' => 1, 'quantity' => 3],
            ]
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'products' => [
                        '*' => [
                            'id',
                            'name',
                            'quantity',
                        ]
                    ]
                ],
            ]);

        $this->assertDatabaseHas('orders', ['id' => $response->json('data.id')]);
        $this->assertDatabaseHas('order_items', [
            'order_id' => $response->json('data.id'),
            'product_id' => $response->json('data.products.0.id'),
            'quantity' => 3,
        ]);

        // Adjust expectations based on actual ProductSeeder data
        $this->assertDatabaseHas('ingredients', ['name' => 'Beef', 'available' => 19550]);
        $this->assertDatabaseHas('ingredients', ['name' => 'Cheese', 'available' => 4910]);
        $this->assertDatabaseHas('ingredients', ['name' => 'Onion', 'available' => 940]);
    }

    /**
     * Test validation error when 'products' is missing.
     */
    public function test_validation_error_when_products_missing(): void
    {
        $response = $this->postJson('/api/placeorder', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['products']);
    }

    /**
     * Test validation error when 'products' is not an array.
     */
    public function test_validation_error_when_products_not_array(): void
    {
        $response = $this->postJson('/api/placeorder', [
            'products' => 'not-an-array',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['products']);
    }

    /**
     * Test validation error when 'product_id' is missing.
     */
    public function test_validation_error_when_product_id_or_quantity_missing(): void
    {
        $response = $this->postJson('/api/placeorder', [
            'products' => [
                ['quantity' => 1],
            ],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['products.0.product_id']);

        $response = $this->postJson('/api/placeorder', [
            'products' => [
                ['product_id' => 1],
            ],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['products.0.quantity']);
    }

    /**
     * Test validation error when 'product_id' does not exist.
     */
    public function test_validation_error_when_product_id_not_exists(): void
    {
        $response = $this->postJson('/api/placeorder', [
            'products' => [
                ['product_id' => 999, 'quantity' => 2], // Assuming 999 does not exist in the database
            ],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['products.0.product_id']);
    }

    /**
     * Test validation error when 'quantity' is not an integer.
     */
    public function test_validation_error_when_product_id_or_quantity_not_integer(): void
    {
        $response = $this->postJson('/api/placeorder', [
            'products' => [
                ['product_id' => 'not-an-integer', 'quantity' => 'not-an-integer'],
            ],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['products.0.product_id'])
            ->assertJsonValidationErrors(['products.0.quantity']);
    }

    /**
     * Test validation error when 'quantity' is less than 1.
     */
    public function test_validation_error_when_quantity_less_than_one(): void
    {
        $response = $this->postJson('/api/placeorder', [
            'products' => [
                ['product_id' => 1, 'quantity' => 0],
            ],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['products.0.quantity']);
    }
}
