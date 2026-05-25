<?php

namespace App\Services\Payment;

use App\Contracts\PaymentGatewayInterface;
use Illuminate\Support\Facades\Log;

class StripeGateway implements PaymentGatewayInterface
{
    public function charge(int $amount): bool
    {
        Log::info('Payment via Stripe');
        return true;
    }
}
