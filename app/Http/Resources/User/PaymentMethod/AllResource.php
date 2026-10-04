<?php

namespace App\Http\Resources\User\PaymentMethod;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AllResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'   => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'icon' => $this->image_url,
            'is_default' => $this->isDefault(),
            'requires_online_payment' => $this->requiresOnlineConfirmation(),
        ];
    }
}
