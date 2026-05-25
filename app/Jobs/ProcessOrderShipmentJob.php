<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use App\Models\Order;
use Throwable;
use Illuminate\Support\Facades\Log;

class ProcessOrderShipmentJob implements ShouldQueue, ShouldBeUnique
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public Order $order)
    {
        //
    }

    public function uniqueId(): string
    {
        return (string) $this->order->id;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //
    }

    public function tries(): int
    {
        return 5;
    }

    public function backoff(): int
    {
        return 60;
    }

    public function failed(?Throwable $exception): void
    {
        Log::critical("Shipment failed for order: {$this->order->id}");
    }
}
