<?php
namespace App\Actions;

use App\Events\OrderPlaced;
use App\Exceptions\InsufficientStockException;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PlaceOrderAction
{
    public function execute(User $user, array $itemsData): Order
    {
        // Возвращаем заказ из транзакции
        $order = DB::transaction(function () use ($user, $itemsData) {
            $productIds = array_column($itemsData, 'id');
// dd($itemsData);
            // 1. Блокируем строки продуктов для апдейта (Защита от Race Condition)
            $products = Product::query()
                ->whereIn('id', $productIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $total = 0;
            $orderItemsData = [];

            // Первый проход: валидация остатков и подсчет тотала
            foreach ($itemsData as $item) {
                $product = $products->get($item['id']);

                if (! $product || $product->stock < $item['qty']) {
                    throw new InsufficientStockException(
                        $item['id'], 
                        $item['qty'], 
                        $product?->stock ?? 0
                    );
                }

                $total += $product->price * $item['qty'];

                // Формируем массив для массовой вставки позиций заказа
                $orderItemsData[] = [
                    'product_id' => $product->id,
                    'quantity' => $item['qty'],
                    'unit_price' => $product->price,
                ];

                $newStock = $product->stock - $item['qty'];
                $productsStockData[] = "WHEN id = {$product->id} THEN {$newStock}";
            }

            // 2. Создаем заказ
            /** @var Order $order */
            $order = Order::query()->create([
                'user_id' => $user->id,
                'total' => $total,
                'status' => 'pending',
            ]);

            // 3. Массовое сохранение позиций (1 запрос вместо N)
            $order->items()->createMany($orderItemsData);

            // 4. Списание остатков. Раз уж мы залочили модели, уменьшаем локально и сохраняем
            $cases = implode(' ', $productsStockData);
            DB::statement("
                UPDATE products 
                SET stock = CASE 
                    {$cases}
                    ELSE stock 
                END
                WHERE id IN (" . implode(',', $productIds) . ")
            ");

            return $order;
        });

        // Подгружаем связи один раз перед отдачей
        $order->load(['items.product', 'user']);

        OrderPlaced::dispatch($order);

        return $order;
    }
}