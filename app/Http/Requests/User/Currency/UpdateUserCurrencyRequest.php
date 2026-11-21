<?php

namespace App\Http\Requests\User\Currency;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class UpdateUserCurrencyRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'currency_id' => ['required', 'integer', Rule::exists('currencies', 'id')->where('is_active', true)],
        ];
    }
}
