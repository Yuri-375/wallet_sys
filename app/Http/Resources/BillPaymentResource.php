<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BillPaymentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reference' => $this->reference,
            'biller' => $this->biller,
            'account_number' => $this->account_number,
            'amount' => $this->amount,
            'currency' => $this->currency ?? 'MMK',
            'status' => $this->status,
            'description' => $this->description,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
