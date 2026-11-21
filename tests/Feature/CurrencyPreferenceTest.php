<?php

namespace Tests\Feature;

use App\Helpers\CurrencyHelper;
use App\Models\Currency;
use App\Models\User;
use App\Services\Admin\CurrencyService;
use App\Services\Admin\ToggleStatusService;
use Database\Seeders\CurrencySeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class CurrencyPreferenceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createCurrency('USD');
    }

    public function test_guest_currency_list_contains_only_active_rows_and_boolean_defaults(): void
    {
        $default = Currency::active()->default()->firstOrFail();
        $syp = $this->createCurrency('syp');
        $inactive = $this->createCurrency('EUR', false);

        $response = $this->getJson('/api/user/currencies?page=1');

        $response->assertOk()->assertJsonPath('data.pagination', null);
        $items = collect($response->json('data.items'));
        $this->assertTrue($items->contains('id', $default->id));
        $this->assertTrue($items->contains('id', $syp->id));
        $this->assertFalse($items->contains('id', $inactive->id));
        $this->assertCount(1, $items->where('is_default', true));
        foreach ($items as $item) {
            $this->assertIsBool($item['is_default']);
            $this->assertSame(strtoupper($item['code']), $item['code']);
        }
    }

    public function test_unset_account_currency_follows_current_default_without_becoming_a_preference(): void
    {
        $user = User::factory()->create();
        $this->assertNull($user->fresh()->currency_id);
        $this->assertNull($user->fresh()->currency_preference_source);
        $usd = Currency::active()->default()->firstOrFail();
        $syp = $this->createCurrency('SYP');
        $this->actingAs($user, 'user');

        $this->getJson('/api/user/currencies/my-currency')
            ->assertOk()->assertJsonPath('data.id', $usd->id)
            ->assertJsonPath('data.preference_source', 'default');

        app(CurrencyService::class)->update($syp->id, ['is_default' => true]);

        $this->getJson('/api/user/currencies/my-currency')
            ->assertOk()->assertJsonPath('data.id', $syp->id)
            ->assertJsonPath('data.preference_source', 'default');
        $this->assertNull($user->fresh()->currency_id);
        $this->assertSame('SYP', CurrencyHelper::convertFromBase(10)['currency']);
    }

    public function test_database_user_default_is_unset_even_without_model_events(): void
    {
        $id = DB::table('users')->insertGetId([
            'name' => 'Unset preference',
            'email' => 'unset@example.test',
            'password' => 'unused',
        ]);

        $this->assertNull(DB::table('users')->where('id', $id)->value('currency_id'));
    }

    public function test_legacy_stored_currency_is_retained_but_does_not_claim_explicit_preference(): void
    {
        $default = Currency::active()->default()->firstOrFail();
        $legacy = $this->createCurrency('SYP');
        $user = User::factory()->create(['currency_id' => $legacy->id]);
        $this->actingAs($user, 'user');

        $this->getJson('/api/user/currencies/my-currency')
            ->assertOk()->assertJsonPath('data.id', $default->id)
            ->assertJsonPath('data.preference_source', 'default');
        $this->assertSame($legacy->id, $user->fresh()->currency_id);
        $this->assertSame($default->code, CurrencyHelper::getUserCurrency()->code);
    }

    public function test_saved_choice_is_marked_explicit_and_survives_global_default_changes(): void
    {
        $user = User::factory()->create();
        $syp = $this->createCurrency('SYP');
        $eur = $this->createCurrency('EUR');
        $this->actingAs($user, 'user');

        $this->postJson('/api/user/currencies/update-currency', ['currency_id' => $syp->id])
            ->assertOk()->assertJsonPath('data.id', $syp->id)
            ->assertJsonPath('data.preference_source', 'user');

        app(CurrencyService::class)->update($eur->id, ['is_default' => true]);

        $this->getJson('/api/user/currencies/my-currency')
            ->assertOk()->assertJsonPath('data.id', $syp->id)
            ->assertJsonPath('data.preference_source', 'user');
        $this->assertSame('user', $user->fresh()->currency_preference_source);
        $this->assertSame($syp->id, $user->fresh()->currency_id);
    }

    public function test_currency_update_rejects_missing_unknown_noninteger_and_inactive_choices(): void
    {
        $user = User::factory()->create();
        $inactive = $this->createCurrency('EUR', false);
        $this->actingAs($user, 'user');

        foreach ([[], ['currency_id' => 999999], ['currency_id' => 1.5], ['currency_id' => $inactive->id]] as $payload) {
            $this->postJson('/api/user/currencies/update-currency', $payload)->assertUnprocessable();
        }

        $this->assertNull($user->fresh()->currency_id);
        $this->assertNull($user->fresh()->currency_preference_source);
    }

    public function test_inactive_explicit_preference_resolves_to_active_default_without_erasing_choice(): void
    {
        $default = Currency::active()->default()->firstOrFail();
        $syp = $this->createCurrency('SYP');
        $user = User::factory()->create();
        $this->actingAs($user, 'user');
        $this->postJson('/api/user/currencies/update-currency', ['currency_id' => $syp->id])->assertOk();
        app(ToggleStatusService::class)->toggleStatus('currency', $syp->id, false);

        $this->getJson('/api/user/currencies/my-currency')
            ->assertOk()->assertJsonPath('data.id', $default->id)
            ->assertJsonPath('data.preference_source', 'default');
        $this->assertSame($syp->id, $user->fresh()->currency_id);
        $this->assertSame('user', $user->fresh()->currency_preference_source);
    }

    public function test_account_currency_routes_require_authentication(): void
    {
        $this->getJson('/api/user/currencies/my-currency')->assertUnauthorized();
        $this->postJson('/api/user/currencies/update-currency', ['currency_id' => 1])->assertUnauthorized();
    }

    public function test_dashboard_create_path_changes_default_and_fresh_guest_reads_see_it(): void
    {
        $oldDefault = Currency::active()->default()->firstOrFail();
        $this->withoutMiddleware();

        $this->postJson('/api/admin/currencies', [
            ...$this->currencyData('syp'),
            'is_default' => true,
        ])->assertOk()->assertJsonPath('data.code', 'SYP');

        $this->assertFalse($oldDefault->fresh()->is_default);
        $this->assertSame(1, Currency::active()->default()->count());
        $items = collect($this->getJson('/api/user/currencies')->assertOk()->json('data.items'));
        $this->assertSame('SYP', $items->firstWhere('is_default', true)['code']);
    }

    public function test_dashboard_cannot_unmark_deactivate_or_delete_the_current_default(): void
    {
        $default = Currency::active()->default()->firstOrFail();
        $service = app(CurrencyService::class);

        foreach ([
            fn () => $service->update($default->id, ['is_default' => false]),
            fn () => $service->update($default->id, ['is_active' => false]),
            fn () => $service->delete($default->id),
            fn () => app(ToggleStatusService::class)->toggleStatus('currency', $default->id, false),
        ] as $operation) {
            try {
                $operation();
                $this->fail('Removing the active default should fail.');
            } catch (ValidationException $exception) {
                $this->assertNotEmpty($exception->errors());
            }
            $this->assertTrue($default->fresh()->is_default);
            $this->assertTrue($default->fresh()->is_active);
            $this->assertSame(1, Currency::active()->default()->count());
        }
    }

    public function test_inactive_currency_cannot_become_default(): void
    {
        $inactive = $this->createCurrency('EUR', false);

        $this->expectException(ValidationException::class);
        app(CurrencyService::class)->update($inactive->id, ['is_default' => true]);
    }

    public function test_failed_default_creation_rolls_back_the_previous_default(): void
    {
        $default = Currency::active()->default()->firstOrFail();
        try {
            app(CurrencyService::class)->store([
                ...$this->currencyData($default->code),
                'is_default' => true,
            ]);
            $this->fail('Duplicate code should fail.');
        } catch (QueryException) {
            $this->assertTrue($default->fresh()->is_default);
            $this->assertSame(1, Currency::active()->default()->count());
        }
    }

    public function test_database_rejects_a_second_active_default(): void
    {
        $syp = $this->createCurrency('SYP');

        $this->expectException(QueryException::class);
        DB::table('currencies')->where('id', $syp->id)->update(['is_default' => true]);
    }

    public function test_deployment_seeder_preserves_dashboard_default_rates_and_inactive_status(): void
    {
        $syp = $this->createCurrency('SYP');
        app(CurrencyService::class)->update($syp->id, ['is_default' => true, 'exchange_rate' => 12500]);
        $eur = $this->createCurrency('EUR', false);

        $this->seed(CurrencySeeder::class);

        $this->assertTrue($syp->fresh()->is_default);
        $this->assertSame('12500.000000', $syp->fresh()->exchange_rate);
        $this->assertFalse($eur->fresh()->is_active);
        $this->assertSame(1, Currency::active()->default()->count());
    }

    private function createCurrency(string $code, bool $active = true): Currency
    {
        return app(CurrencyService::class)->store([
            ...$this->currencyData($code),
            'is_active' => $active,
        ]);
    }

    private function currencyData(string $code): array
    {
        return [
            'code' => $code,
            'name' => ['en' => $code, 'ar' => $code],
            'symbol' => $code,
            'exchange_rate' => strtoupper($code) === 'SYP' ? 13000 : 1,
        ];
    }
}
