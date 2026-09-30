<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'direction' => $this->direction,
            'amount' => (int) $this->amount,
            'currency' => $this->currency,
            'reference' => $this->reference,
            'status' => $this->status,
            'description' => $this->description,
            'wallet' => [
                'id' => $this->wallet?->id,
                'balance' => $this->wallet?->balance,
            ],
            'user' => [
                'id' => $this->wallet?->user?->id,
                'name' => $this->wallet?->user?->name,
                'email' => $this->wallet?->user?->email,
            ],
            'created_at' => $this->created_at,
        ];
    }
}
