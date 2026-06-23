<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ProductStockUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $productId,
        public int $newStock
    ) {
        \Log::info('Evento construido', [
            'product_id' => $this->productId,
            'new_stock'  => $this->newStock,
        ]);
    }

    public function broadcastOn(): Channel
    {
        return new Channel('inventory');
    }

    public function broadcastAs(): string
    {
        return 'ProductStockUpdated';
    }

    public function broadcastWith(): array
    {
        return [
            'product_id' => $this->productId,
            'new_stock'  => $this->newStock,
        ];
    }
}
