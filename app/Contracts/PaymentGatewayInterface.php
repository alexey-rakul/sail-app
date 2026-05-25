<?php

namespace App\Contracts;

interface PaymentGatewayInterface
{
    public function charge(int $amount): bool;
}
