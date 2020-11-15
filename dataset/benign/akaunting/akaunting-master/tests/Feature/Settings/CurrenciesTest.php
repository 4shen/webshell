<?php

namespace Tests\Feature\Settings;

use App\Jobs\Setting\CreateCurrency;
use App\Models\Setting\Currency;
use Tests\Feature\FeatureTestCase;

class CurrenciesTest extends FeatureTestCase
{
    public function testItShouldSeeCurrencyListPage()
    {
        $this->loginAs()
            ->get(route('currencies.index'))
            ->assertStatus(200)
            ->assertSeeText(trans_choice('general.currencies', 2));
    }

    public function testItShouldSeeCurrencyCreatePage()
    {
        $this->loginAs()
            ->get(route('currencies.create'))
            ->assertStatus(200)
            ->assertSeeText(trans('general.title.new', ['type' => trans_choice('general.currencies', 1)]));
    }

    public function testItShouldCreateCurrency()
    {
        $this->loginAs()
            ->post(route('currencies.store'), $this->getRequest())
            ->assertStatus(200);

        $this->assertFlashLevel('success');
    }

    public function testItShouldSeeCurrencyUpdatePage()
    {
        $currency = $this->dispatch(new CreateCurrency($this->getRequest()));

        $this->loginAs()
            ->get(route('currencies.edit', $currency->id))
            ->assertStatus(200)
            ->assertSee($currency->code);
    }

    public function testItShouldUpdateCurrency()
    {
        $request = $this->getRequest();

        $currency = $this->dispatch(new CreateCurrency($request));

        $request['name'] = $this->faker->text(15);

        $this->loginAs()
            ->patch(route('currencies.update', $currency->id), $request)
            ->assertStatus(200)
			->assertSee($request['name']);

        $this->assertFlashLevel('success');
    }

    public function testItShouldDeleteCurrency()
    {
        $currency = $this->dispatch(new CreateCurrency($this->getRequest()));

        $this->loginAs()
            ->delete(route('currencies.destroy', $currency->id))
            ->assertStatus(200);

        $this->assertFlashLevel('success');
    }

    public function getRequest()
    {
        return factory(Currency::class)->states('enabled')->raw();
    }
}
