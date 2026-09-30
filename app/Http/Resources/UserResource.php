<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'status' => $this->status,
            'kyc_status' => $this->kyc_status,
            'kyc_verified_at' => $this->when(
                $this->hasRole('user'),
                $this->kyc_verified_at
            ),
            'verified_by' => $this->when(
                $this->hasRole('user'),
                $this->verified_by
            ),
        ];
    }
}
