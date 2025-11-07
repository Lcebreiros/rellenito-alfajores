<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'status' => $this->status?->value,
            'payment_status' => $this->payment_status?->value,
            'payment_method' => $this->payment_method?->value,
            'subtotal' => (float) $this->subtotal,
            'discount' => (float) $this->discount,
            'tax_amount' => (float) $this->tax_amount,
            'total' => (float) $this->total,
            'notes' => $this->notes,
            'is_scheduled' => $this->is_scheduled,
            'scheduled_for' => $this->scheduled_for?->toISOString(),
            'sold_at' => $this->sold_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),

            // Relaciones opcionales
            'client' => $this->whenLoaded('client', function () {
                return [
                    'id' => $this->client->id,
                    'name' => $this->client->name,
                    'phone' => $this->client->phone,
                    'email' => $this->client->email,
                    'address' => $this->client->address,
                ];
            }),
            'user' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                ];
            }),
            'items' => $this->whenLoaded('items', function () {
                return OrderItemResource::collection($this->items);
            }),
            'payment_methods' => $this->whenLoaded('paymentMethods', function () {
                return $this->paymentMethods->map(function ($method) {
                    return [
                        'id' => $method->id,
                        'name' => $method->name,
                        'amount' => (float) $method->pivot->amount,
                        'reference' => $method->pivot->reference,
                    ];
                });
            }),
        ];
    }
}
