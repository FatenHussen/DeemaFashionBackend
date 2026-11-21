<?php

namespace App\Http\Resources\Currency;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OneResource extends JsonResource
{
    protected ?string $preferenceSource = null;

    public function withPreferenceSource(string $source): static
    {
        $this->preferenceSource = $source;

        return $this;
    }

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->localized_name,
            'symbol' => $this->symbol,
            'exchange_rate' => $this->exchange_rate,
            'is_default' => (bool) $this->is_default,
            'is_active' => (bool) $this->is_active,
            'preference_source' => $this->when($this->preferenceSource !== null, $this->preferenceSource),
        ];
    }
}
