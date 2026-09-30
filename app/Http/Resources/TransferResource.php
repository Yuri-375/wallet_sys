<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransferResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reference' => $this->reference,

            'sender' => [
                'id' => $this->sender?->id,
                'name' => $this->sender?->name,
            ],

            'receiver' => [
                'id' => $this->receiver?->id,
                'name' => $this->receiver?->name,
            ],

            'amount' => (int) $this->amount,
            'status' => $this->status,
            'description' => $this->description,
            'idempotency_key' => $this->idempotency_key,

            'otp' => [
                'required' => $this->status === 'otp_required',
                'attempts' => (int) $this->otp_attempts,
                'expires_at' => $this->status === 'otp_required'
                    ? $this->otp_expires_at
                    : null,
            ],

            'completed_at' => $this->completed_at,
            'created_at' => $this->created_at,

            'mock_otp' => $this->when(
                $this->status === 'otp_required',
                fn() => $this->development_otp
            ),
        ];
    }
}
