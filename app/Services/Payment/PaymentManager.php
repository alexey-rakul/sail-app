<?php

namespace App\Services\Payment;

use Illuminate\Support\Manager;
use App\Contracts\PaymentGatewayInterface;

class PaymentManager extends Manager
{
    /**
     * Драйвер по умолчанию (берется из конфига config/payment.php)
     */
    public function getDefaultDriver(): string
    {
        return $this->config->get('payment.default', 'stripe');
    }

    /**
     * Создание драйвера StripeGateway
     */
    protected function createStripeDriver(): PaymentGatewayInterface
    {
        return new StripeGateway();
    }

    /**
     * Создание драйвера PayPalGateway
     */
    protected function createPayPalDriver(): PaymentGatewayInterface
    {
        return new PayPalGateway();
    }
}
