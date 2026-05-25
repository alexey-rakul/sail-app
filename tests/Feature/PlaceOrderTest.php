<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Actions\PlaceOrderAction;
use App\Events\OrderPlaced;
use App\Exceptions\InsufficientStockException;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PlaceOrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_created_success(): void
    {
        // 1. Включаем фейк для ивентов, чтобы они не улетали по-настоящему
        Event::fake();

        // 2. Создаем тестовые данные через фабрики (Убедись, что у тебя есть фабрики для User и Product)
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock' => 10, 'price' => 100]);

        $itemsData = [
            ['id' => $product->id, 'qty' => 3]
        ];

        // 3. Вызываем наш экшен
        $action = app(PlaceOrderAction::class);
        $order = $action->execute($user, $itemsData);

        // 4. Пишем утверждения (Assertions)
        // Проверяем, что заказ появился в БД
        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'total' => 300,
        ]);

        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 3,
            'unit_price' => 100,
        ]);

        // ТВОЁ ЗАДАНИЕ А: Допиши проверку, что остаток продукта в базе стал равен 7
        // Подсказка: $product->refresh(); и проверка его stock
        $product->refresh();
        $this->assertEquals($product->stock, 7);

        // ТВОЁ ЗАДАНИЕ Б: Допиши проверку, что ивент OrderPlaced был отправлен
        // Подсказка: Event::assertDispatched(OrderPlaced::class);
        Event::assertDispatched(OrderPlaced::class);
    }

    public function test_order_created_failure(): void
    {
        Event::fake();
        $this->expectException(InsufficientStockException::class);

        $user = User::factory()->create();
        $product = Product::factory()->create(['stock' => 2, 'price' => 100]);

        $itemsData = [
            ['id' => $product->id, 'qty' => 5] // Просим больше, чем есть
        ];

        $action = app(PlaceOrderAction::class);
        $order = $action->execute($user, $itemsData);

        // ТВОЁ ЗАДАНИЕ В: Напиши проверку, что код выбросит исключение InsufficientStockException
        // и при этом в базе данных НЕ появится новый заказ (assertDatabaseCount('orders', 0)).

        $this->assertDatabaseEmpty('orders');
        $this->assertDatabaseEmpty('order_items');
        $product->refresh();
        $this->assertEquals($product->stock, 2);

        Event::assertNotDispatched(OrderPlaced::class);
    }
}