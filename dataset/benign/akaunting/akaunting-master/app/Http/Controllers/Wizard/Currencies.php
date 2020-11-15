<?php

namespace App\Http\Controllers\Wizard;

use Akaunting\Money\Currency as MoneyCurrency;
use App\Abstracts\Http\Controller;
use App\Http\Requests\Setting\Currency as Request;
use App\Jobs\Setting\CreateCurrency;
use App\Jobs\Setting\DeleteCurrency;
use App\Jobs\Setting\UpdateCurrency;
use App\Models\Setting\Currency;

class Currencies extends Controller
{
    /**
     * Instantiate a new controller instance.
     */
    public function __construct()
    {
        // Add CRUD permission check
        $this->middleware('permission:create-settings-currencies')->only('create', 'store', 'duplicate', 'import');
        $this->middleware('permission:read-settings-currencies')->only('index', 'show', 'edit', 'export');
        $this->middleware('permission:update-settings-currencies')->only('update', 'enable', 'disable');
        $this->middleware('permission:delete-settings-currencies')->only('destroy');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return Response
     */
    public function index()
    {
        $currencies = Currency::collect();

        // Get current currencies
        $current = Currency::orderBy('code')->pluck('code')->toArray();

        // Prepare codes
        $codes = array();
        $money_currencies = MoneyCurrency::getCurrencies();
        foreach ($money_currencies as $key => $item) {
            $codes[$key] = $key;
        }

        return view('wizard.currencies.index', compact('currencies', 'codes'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     *
     * @return Response
     */
    public function store(Request $request)
    {
        $response = $this->ajaxDispatch(new CreateCurrency($request));

        $response['redirect'] = route('wizard.currencies.index');

        if ($response['success']) {

            $message = trans('messages.success.added', ['type' => trans_choice('general.currencies', 1)]);

            flash($message)->success();
        } else {
            $message = $response['message'];

            flash($message)->error();
        }

        return response()->json($response);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Currency  $currency
     * @param  Request  $request
     *
     * @return Response
     */
    public function update(Currency $currency, Request $request)
    {
        $response = $this->ajaxDispatch(new UpdateCurrency($currency, $request));

        $response['redirect'] = route('wizard.currencies.index');

        if ($response['success']) {
            $message = trans('messages.success.updated', ['type' => $currency->name]);

            flash($message)->success();
        } else {
            $message = $response['message'];

            flash($message)->error();
        }

        return response()->json($response);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Currency  $currency
     *
     * @return Response
     */
    public function destroy(Currency $currency)
    {
        $response = $this->ajaxDispatch(new DeleteCurrency($currency));

        $response['redirect'] = route('wizard.currencies.index');

        if ($response['success']) {
            $message = trans('messages.success.deleted', ['type' => $currency->name]);

            flash($message)->success();
        } else {
            $message = $response['message'];

            flash($message)->error();
        }

        return response()->json($response);
    }
}
