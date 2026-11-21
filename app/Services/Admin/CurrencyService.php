<?php

namespace App\Services\Admin;

use App\Http\Resources\Admin\Currency\AllResource;
use App\Http\Resources\Admin\Currency\OneResource;
use App\Models\Currency;
use App\Services\BaseService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CurrencyService extends BaseService
{
    public function __construct(Currency $model)
    {
        $this->model      = $model;
        $this->resource   = OneResource::class;
        $this->collection = AllResource::class;
        $this->pagination = true;
        $this->searchableFields = ['code', 'name'];
    }

    public function create($data)
    {
        return new OneResource($this->store($data));
    }

    public function store(array $data)
    {
        return DB::transaction(function () use ($data) {
            $this->lockCurrencies();
            $data['is_active'] = $data['is_active'] ?? true;
            $data['is_default'] = $data['is_default'] ?? false;
            if (!Currency::active()->default()->exists() && $data['is_active']) {
                $data['is_default'] = true;
            }

            $this->validateDefault($data['is_default'], $data['is_active']);
            if ($data['is_default']) {
                Currency::where('is_default', true)->update(['is_default' => false]);
            }

            $currency = Currency::create($data);
            $this->ensureDefaultExists();

            return $currency;
        }, 3);
    }

    public function update($id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $this->lockCurrencies();
            $currency = Currency::findOrFail($id);
            $isDefault = (bool) ($data['is_default'] ?? $currency->is_default);
            $isActive = (bool) ($data['is_active'] ?? $currency->is_active);

            $this->validateDefault($isDefault, $isActive);
            if ($currency->is_default && (!$isDefault || !$isActive)) {
                $this->rejectDefaultRemoval();
            }
            if ($isDefault) {
                Currency::whereKeyNot($id)->where('is_default', true)->update(['is_default' => false]);
            }

            $currency->update($data);
            $this->ensureDefaultExists();

            return new OneResource($currency->refresh());
        }, 3);
    }

    public function delete($id): bool
    {
        return DB::transaction(function () use ($id) {
            $this->lockCurrencies();
            $currency = Currency::findOrFail($id);
            if ($currency->is_default) {
                $this->rejectDefaultRemoval();
            }

            $currency->delete();
            $this->ensureDefaultExists();

            return true;
        }, 3);
    }

    public function toggleStatus($id)
    {
        return DB::transaction(function () use ($id) {
            $this->lockCurrencies();
            $currency = Currency::findOrFail($id);

            return $this->update($id, ['is_active' => !$currency->is_active]);
        }, 3);
    }

    private function lockCurrencies(): void
    {
        // Serialize dashboard mutations using the same rows and lock order.
        Currency::orderBy('id')->lockForUpdate()->get();
    }

    private function validateDefault(bool $isDefault, bool $isActive): void
    {
        if ($isDefault && !$isActive) {
            throw ValidationException::withMessages([
                'is_active' => __('custom.cannot_deactivate_default_currency'),
            ]);
        }
    }

    private function ensureDefaultExists(): void
    {
        if (!Currency::active()->default()->exists()) {
            $this->rejectDefaultRemoval();
        }
    }

    private function rejectDefaultRemoval(): never
    {
        throw ValidationException::withMessages([
            'is_default' => __('custom.currency_default_required'),
        ]);
    }
}
