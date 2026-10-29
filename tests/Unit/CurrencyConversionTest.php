<?php

namespace Tests\Unit;

use App\Helpers\CurrencyHelper;
use App\Models\Currency;
use App\Traits\HasCurrencyConversion;
use Illuminate\Config\Repository;
use Illuminate\Container\Container;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Mockery;
use PHPUnit\Framework\TestCase;

class CurrencyConversionTest extends TestCase
{
    private Container $previousContainer;

    private $previousResolver;

    private $previousDispatcher;

    private Capsule $database;

    protected function setUp(): void
    {
        parent::setUp();

        $this->previousContainer = Container::getInstance();
        $this->previousResolver = Model::getConnectionResolver();
        $this->previousDispatcher = Model::getEventDispatcher();

        $container = new Container;
        $container->instance('config', new Repository);
        Container::setInstance($container);

        // Only an isolated currencies table is needed. Never boot the Laravel
        // application or use the connection configured by its environment.
        $this->database = new Capsule($container);
        $this->database->addConnection([
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
        $this->database->bootEloquent();
        Model::unsetEventDispatcher();

        $schema = $this->database->getConnection()->getSchemaBuilder();
        $schema->create('currencies', function (Blueprint $table): void {
            $table->id();
            $table->string('code');
            $table->string('symbol');
            $table->decimal('exchange_rate', 15, 6);
            $table->boolean('is_default');
            $table->boolean('is_active');
        });

        $this->database->getConnection()->table('currencies')->insert([
            // The USD rate is intentionally nonunit: stored prices are always
            // USD, even when USD is not the preferred display currency.
            ['id' => 1, 'code' => 'USD', 'symbol' => '$', 'exchange_rate' => 7, 'is_default' => false, 'is_active' => true],
            ['id' => 2, 'code' => 'SYP', 'symbol' => 'ل.س', 'exchange_rate' => 13000, 'is_default' => true, 'is_active' => true],
        ]);

        $guard = Mockery::mock(Guard::class);
        $guard->shouldReceive('user')->andReturnNull();
        $auth = Mockery::mock(AuthFactory::class);
        $auth->shouldReceive('guard')->with('user')->andReturn($guard);
        $container->instance(AuthFactory::class, $auth);
    }

    protected function tearDown(): void
    {
        try {
            $this->database->getDatabaseManager()->disconnect();
            if ($this->previousResolver) {
                Model::setConnectionResolver($this->previousResolver);
            } else {
                Model::unsetConnectionResolver();
            }
            if ($this->previousDispatcher) {
                Model::setEventDispatcher($this->previousDispatcher);
            } else {
                Model::unsetEventDispatcher();
            }
            Container::setInstance($this->previousContainer);
            Mockery::close();
        } finally {
            parent::tearDown();
        }
    }

    public function test_default_syp_still_converts_from_and_back_to_stored_usd(): void
    {
        $syp = Currency::findOrFail(2);

        $this->assertTrue($syp->is_default);
        $this->assertSame(152100.0, $syp->convertFromBase(11.7));
        $this->assertSame(11.7, $syp->convertToBase(152100));
        $this->assertSame(152100.0, $syp->convertFromUSD(11.7));
        $this->assertSame(11.7, $syp->convertToUSD(152100));
        $this->assertNull($syp->convertFromBase(null));
        $this->assertNull($syp->convertToBase(null));
    }

    public function test_nondefault_usd_remains_the_base_regardless_of_its_exchange_rate(): void
    {
        $usd = Currency::findOrFail(1);

        $this->assertFalse($usd->is_default);
        $this->assertSame(11.7, $usd->convertFromBase(11.7));
        $this->assertSame(11.7, $usd->convertToBase(11.7));
        $this->assertSame(11.7, $usd->convertFromUSD(11.7));
        $this->assertSame(11.7, $usd->convertToUSD(11.7));
    }

    public function test_helper_converts_preferred_syp_and_preserves_explicit_usd(): void
    {
        $preferred = CurrencyHelper::convertFromBase(11.7);
        $this->assertSame('SYP', $preferred['currency']);
        $this->assertSame(152100.0, $preferred['amount']);
        $this->assertSame('ل.س 152,100', $preferred['formatted']);
        $this->assertSame(11.7, CurrencyHelper::convertToBase(152100, 2));
        $this->assertSame(11.7, CurrencyHelper::convertSypToUsd(152100));

        $usd = CurrencyHelper::convertFromBase(11.7, 1);
        $this->assertSame('USD', $usd['currency']);
        $this->assertSame(11.7, $usd['amount']);
        $this->assertSame('$ 11.7', $usd['formatted']);
        $this->assertSame(11.7, CurrencyHelper::convertToBase(11.7, 1));
    }

    public function test_resource_trait_returns_converted_syp_and_unchanged_usd_maps(): void
    {
        $resource = new CurrencyConversionResourceProbe;
        $preferred = $resource->convertPrice(11.7);
        $this->assertSame('SYP', $preferred['currency']);
        $this->assertSame(152100.0, $preferred['amount']);

        $explicitUsd = $resource->convertPrice(11.7, 1);
        $this->assertSame('USD', $explicitUsd['currency']);
        $this->assertSame(11.7, $explicitUsd['amount']);

        $prices = $resource->prices(13, 11.7);
        $this->assertSame(169000.0, $prices['price']);
        $this->assertSame(152100.0, $prices['price_after_discount']);
        $this->assertSame(13.0, $prices['price_currencies']['USD']['amount']);
        $this->assertSame(169000.0, $prices['price_currencies']['SYP']['amount']);
        $this->assertSame(11.7, $prices['price_after_discount_currencies']['USD']['amount']);
        $this->assertSame(152100.0, $prices['price_after_discount_currencies']['SYP']['amount']);
        $this->assertSame('ل.س 152,100', $prices['price_after_discount_currencies']['SYP']['formatted']);
    }
}

class CurrencyConversionResourceProbe
{
    use HasCurrencyConversion;

    public function prices(float $list, float $sale): array
    {
        return [
            ...$this->withCurrency($list, 'price'),
            ...$this->withCurrency($sale, 'price_after_discount'),
        ];
    }
}
