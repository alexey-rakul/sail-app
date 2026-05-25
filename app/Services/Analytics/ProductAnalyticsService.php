<?php

namespace App\Services\Analytics;

use Illuminate\Support\Facades\Redis;

class ProductAnalyticsService
{
    public function trackView(int $productId): int
    {
        $leaderboardKey = "products:views";
        return (int) Redis::zIncrBy($leaderboardKey, 1, "product_id:$productId");
    }

    public function getTopViewed(int $limit = 5): array
    {
        $leaderboardKey = "products:views";
        
        // Получаем данные из Redis [['product_id:12' => 3.0], ...]
        $topProducts = Redis::zRevRange($leaderboardKey, 0, $limit - 1, true);
        $result = [];
        
        foreach ($topProducts as $member => $score) {
            // Безопасно извлекаем ID, разбивая строку по разделителю ":"
            $parts = explode(':', $member);
            $productId = (int) end($parts); 
            
            // Формируем красивый плоский массив, где Ключ = ID товара, Значение = Просмотры
            $result[$productId] = (int) $score;
        }
        
        return $result;
    }
}
