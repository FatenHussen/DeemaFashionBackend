<?php

namespace App\Services\User;

use App\Http\Resources\Currency\AllResource;
use App\Http\Resources\Currency\OneResource;
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
        $this->pagination = false;
    }

    public function getAllActive()
    {
        return $this->model->active()->get();
    }

    public function queryBuilder($query, $filters = [], $config = [])
    {
        return parent::queryBuilder($query->active(), $filters, $config);
    }

    public function getDefault()
    {
        return $this->model->active()->default()->first();
    }

    public function getUserCurrency($user)
    {
        if ($user?->currency_preference_source === 'user' && $user->currency_id) {
            $currency = $this->model->active()->find($user->currency_id);
            if ($currency) {
                return $currency;
            }
        }
        return $this->getDefault();
    }

    public function getPreferenceSource($user, ?Currency $currency): string
    {
        return $user?->currency_preference_source === 'user'
            && $currency && $currency->id === (int) $user->currency_id
            ? 'user'
            : 'default';
    }

    public function updateUserCurrency($user, $currencyId)
    {
        return DB::transaction(function () use ($user, $currencyId) {
            // Serialize with dashboard changes so an inactive choice cannot be saved.
            $currency = $this->model->lockForUpdate()->find($currencyId);
            if (!$currency || !$currency->is_active) {
                throw ValidationException::withMessages([
                    'currency_id' => __('validation.exists', ['attribute' => 'currency_id']),
                ]);
            }

            $user->forceFill([
                'currency_id' => $currency->id,
                'currency_preference_source' => 'user',
            ])->save();

            return $currency;
        });
    }
}