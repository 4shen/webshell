<?php

namespace Webkul\Core;

use Carbon\Carbon;
use Webkul\Core\Models\Channel;
use Webkul\Core\Repositories\CurrencyRepository;
use Webkul\Core\Repositories\ExchangeRateRepository;
use Webkul\Core\Repositories\CountryRepository;
use Webkul\Core\Repositories\CountryStateRepository;
use Webkul\Core\Repositories\ChannelRepository;
use Webkul\Core\Repositories\LocaleRepository;
use Webkul\Core\Repositories\CoreConfigRepository;
use Illuminate\Support\Facades\Config;
use Webkul\Customer\Repositories\CustomerGroupRepository;

class Core
{
    /**
     * ChannelRepository class
     *
     * @var \Webkul\Core\Repositories\ChannelRepository
     */
    protected $channelRepository;

    /**
     * CurrencyRepository class
     *
     * @var \Webkul\Core\Repositories\CurrencyRepository
     */
    protected $currencyRepository;

    /**
     * ExchangeRateRepository class
     *
     * @var \Webkul\Core\Repositories\ExchangeRateRepository
     */
    protected $exchangeRateRepository;

    /**
     * CountryRepository class
     *
     * @var \Webkul\Core\Repositories\CountryRepository
     */
    protected $countryRepository;

    /**
     * CountryStateRepository class
     *
     * @var \Webkul\Core\Repositories\CountryStateRepository
     */
    protected $countryStateRepository;

    /**
     * LocaleRepository class
     *
     * @var \Webkul\Core\Repositories\LocaleRepository
     */
    protected $localeRepository;

    /**
     * CustomerGroupRepository class
     *
     * @var CustomerGroupRepository
     */
    protected $customerGroupRepository;

    /**
     * CoreConfigRepository class
     *
     * @var \Webkul\Core\Repositories\CoreConfigRepository
     */
    protected $coreConfigRepository;

    /** @var Channel */
    private static $channel;

    /**
     * Create a new instance.
     *
     * @param \Webkul\Core\Repositories\ChannelRepository       $channelRepository
     * @param \Webkul\Core\Repositories\CurrencyRepository      $currencyRepository
     * @param \Webkul\Core\Repositories\ExchangeRateRepository  $exchangeRateRepository
     * @param \Webkul\Core\Repositories\CountryRepository       $countryRepository
     * @param \Webkul\Core\Repositories\CountryStateRepository  $countryStateRepository
     * @param \Webkul\Core\Repositories\LocaleRepository        $localeRepository
     * @param \Webkul\Core\Repositories\CustomerGroupRepository $customerGroupRepository
     * @param \Webkul\Core\Repositories\CoreConfigRepository    $coreConfigRepository
     *
     * @return void
     */
    public function __construct(
        ChannelRepository $channelRepository,
        CurrencyRepository $currencyRepository,
        ExchangeRateRepository $exchangeRateRepository,
        CountryRepository $countryRepository,
        CountryStateRepository $countryStateRepository,
        LocaleRepository $localeRepository,
        CustomerGroupRepository $customerGroupRepository,
        CoreConfigRepository $coreConfigRepository
    )
    {
        $this->channelRepository = $channelRepository;

        $this->currencyRepository = $currencyRepository;

        $this->exchangeRateRepository = $exchangeRateRepository;

        $this->countryRepository = $countryRepository;

        $this->countryStateRepository = $countryStateRepository;

        $this->localeRepository = $localeRepository;

        $this->customerGroupRepository = $customerGroupRepository;

        $this->coreConfigRepository = $coreConfigRepository;
    }

    /**
     * Returns all channels
     *
     * @return \Illuminate\Support\Collection
     */
    public function getAllChannels()
    {
        static $channels;

        if ($channels) {
            return $channels;
        }

        return $channels = $this->channelRepository->all();
    }

    /**
     * Returns currenct channel models
     *
     * @return \Webkul\Core\Contracts\Channel
     */
    public function getCurrentChannel()
    {
        if (self::$channel) {
            return self::$channel;
        }

        self::$channel = $this->channelRepository->findWhereIn('hostname', [
            request()->getHttpHost(),
            'http://' . request()->getHttpHost(),
            'https://' . request()->getHttpHost(),
        ])->first();

        if (! self::$channel) {
            self::$channel = $this->channelRepository->first();
        }

        return self::$channel;
    }

    /**
     * Set the current channel
     *
     * @param Channel $channel
     */
    public function setCurrentChannel(Channel $channel): void
    {
        self::$channel = $channel;
    }

    /**
     * Returns currenct channel code
     *
     * @return \Webkul\Core\Contracts\Channel
     */
    public function getCurrentChannelCode(): string
    {
        static $channelCode;

        if ($channelCode) {
            return $channelCode;
        }

        return ($channel = $this->getCurrentChannel()) ? $channelCode = $channel->code : '';
    }

    /**
     * Returns default channel models
     *
     * @return \Webkul\Core\Contracts\Channel
     */
    public function getDefaultChannel(): ?Channel
    {
        static $channel;

        if ($channel) {
            return $channel;
        }

        $channel = $this->channelRepository->findOneByField('code', config('app.channel'));

        if ($channel) {
            return $channel;
        }

        return $channel = $this->channelRepository->first();
    }

    /**
     * Returns the default channel code configured in config/app.php
     *
     * @return string
     */
    public function getDefaultChannelCode(): string
    {
        static $channelCode;

        if ($channelCode) {
            return $channelCode;
        }

        return ($channel = $this->getDefaultChannel()) ? $channelCode = $channel->code : '';
    }

    /**
     * Returns all locales
     *
     * @return \Illuminate\Support\Collection
     */
    public function getAllLocales()
    {
        static $locales;

        if ($locales) {
            return $locales;
        }

        return $locales = $this->localeRepository->all();
    }

    /**
     * Returns current locale
     *
     * @return \Webkul\Core\Contracts\Locale
     */
    public function getCurrentLocale()
    {
        static $locale;

        if ($locale) {
            return $locale;
        }

        $locale = $this->localeRepository->findOneByField('code', app()->getLocale());

        if (! $locale) {
            $locale = $this->localeRepository->findOneByField('code', config('app.fallback_locale'));
        }

        return $locale;
    }

    /**
     * Returns all Customer Groups
     *
     * @return \Illuminate\Support\Collection
     */
    public function getAllCustomerGroups()
    {
        static $customerGroups;

        if ($customerGroups) {
            return $customerGroups;
        }

        return $customerGroups = $this->customerGroupRepository->all();
    }

    /**
     * Returns all currencies
     *
     * @return \Illuminate\Support\Collection
     */
    public function getAllCurrencies()
    {
        static $currencies;

        if ($currencies) {
            return $currencies;
        }

        return $currencies = $this->currencyRepository->all();
    }

    /**
     * Returns base channel's currency model
     *
     * @return \Webkul\Core\Contracts\Currency
     */
    public function getBaseCurrency()
    {
        static $currency;

        if ($currency) {
            return $currency;
        }

        $baseCurrency = $this->currencyRepository->findOneByField('code', config('app.currency'));

        if (! $baseCurrency) {
            $baseCurrency = $this->currencyRepository->first();
        }

        return $currency = $baseCurrency;
    }

    /**
     * Returns base channel's currency code
     *
     * @return string
     */
    public function getBaseCurrencyCode()
    {
        static $currencyCode;

        if ($currencyCode) {
            return $currencyCode;
        }

        return ($currency = $this->getBaseCurrency()) ? $currencyCode = $currency->code : '';
    }

    /**
     * Returns base channel's currency model
     *
     * @return \Webkul\Core\Contracts\Currency
     */
    public function getChannelBaseCurrency()
    {
        static $currency;

        if ($currency) {
            return $currency;
        }

        $currenctChannel = $this->getCurrentChannel();

        return $currency = $currenctChannel->base_currency;
    }

    /**
     * Returns base channel's currency code
     *
     * @return string
     */
    public function getChannelBaseCurrencyCode()
    {
        static $currencyCode;

        if ($currencyCode) {
            return $currencyCode;
        }

        return ($currency = $this->getChannelBaseCurrency()) ? $currencyCode = $currency->code : '';
    }

    /**
     * Returns current channel's currency model
     *
     * @return \Webkul\Core\Contracts\Currency
     */
    public function getCurrentCurrency()
    {
        static $currency;

        if ($currency) {
            return $currency;
        }

        if ($currencyCode = session()->get('currency')) {
            if ($currency = $this->currencyRepository->findOneByField('code', $currencyCode)) {
                return $currency;
            }
        }

        return $currency = $this->getChannelBaseCurrency();
    }

    /**
     * Returns current channel's currency code
     *
     * @return string
     */
    public function getCurrentCurrencyCode()
    {
        static $currencyCode;

        if ($currencyCode) {
            return $currencyCode;
        }

        return ($currency = $this->getCurrentCurrency()) ? $currencyCode = $currency->code : '';
    }

    /**
     * Converts price
     *
     * @param float  $amount
     * @param string $targetCurrencyCode
     * @param string $orderCurrencyCode
     *
     * @return string
     */
    public function convertPrice($amount, $targetCurrencyCode = null, $orderCurrencyCode = null)
    {
        if (! isset($this->lastCurrencyCode)) {
            $this->lastCurrencyCode = $this->getBaseCurrency()->code;
        }

        if ($orderCurrencyCode) {
            if (! isset($this->lastOrderCode)) {
                $this->lastOrderCode = $orderCurrencyCode;
            }

            if (($targetCurrencyCode != $this->lastOrderCode)
                && ($targetCurrencyCode != $orderCurrencyCode)
                && ($orderCurrencyCode != $this->getBaseCurrencyCode())
                && ($orderCurrencyCode != $this->lastCurrencyCode)
            ) {
                $amount = $this->convertToBasePrice($amount, $orderCurrencyCode);
            }
        }

        $targetCurrency = ! $targetCurrencyCode
            ? $this->getCurrentCurrency()
            : $this->currencyRepository->findOneByField('code', $targetCurrencyCode);

        if (! $targetCurrency) {
            return $amount;
        }

        $exchangeRate = $this->exchangeRateRepository->findOneWhere([
            'target_currency' => $targetCurrency->id,
        ]);

        if (null === $exchangeRate || ! $exchangeRate->rate) {
            return $amount;
        }

        $result = (float)$amount * (float)($this->lastCurrencyCode == $targetCurrency->code ? 1.0 : $exchangeRate->rate);

        if ($this->lastCurrencyCode != $targetCurrency->code) {
            $this->lastCurrencyCode = $targetCurrency->code;
        }

        return $result;
    }

    /**
     * Converts to base price
     *
     * @param float  $amount
     * @param string $targetCurrencyCode
     *
     * @return string
     */
    public function convertToBasePrice($amount, $targetCurrencyCode = null)
    {
        $targetCurrency = ! $targetCurrencyCode
            ? $this->getCurrentCurrency()
            : $this->currencyRepository->findOneByField('code', $targetCurrencyCode);

        if (! $targetCurrency) {
            return $amount;
        }

        $exchangeRate = $this->exchangeRateRepository->findOneWhere([
            'target_currency' => $targetCurrency->id,
        ]);

        if (null === $exchangeRate || ! $exchangeRate->rate) {
            return $amount;
        }

        return (float)$amount / $exchangeRate->rate;
    }

    /**
     * Format and convert price with currency symbol
     *
     * @param float $price
     *
     * @return string
     */
    public function currency($amount = 0)
    {
        if (is_null($amount)) {
            $amount = 0;
        }

        return $this->formatPrice($this->convertPrice($amount), $this->getCurrentCurrency()->code);
    }

    /**
     * Return currency symbol from currency code
     *
     * @param float $price
     *
     * @return string
     */
    public function currencySymbol($code)
    {
        $formatter = new \NumberFormatter(app()->getLocale() . '@currency=' . $code, \NumberFormatter::CURRENCY);

        return $formatter->getSymbol(\NumberFormatter::CURRENCY_SYMBOL);
    }

    /**
     * Format and convert price with currency symbol
     *
     * @param float $price
     *
     * @return string
     */
    public function formatPrice($price, $currencyCode)
    {
        if (is_null($price))
            $price = 0;

        $formatter = new \NumberFormatter(app()->getLocale(), \NumberFormatter::CURRENCY);

        return $formatter->formatCurrency($price, $currencyCode);
    }

    /**
     * Format and convert price with currency symbol
     *
     * @return array
     */
    public function getAccountJsSymbols()
    {
        $formater = new \NumberFormatter(app()->getLocale(), \NumberFormatter::CURRENCY);

        $pattern = $formater->getPattern();

        $pattern = str_replace("¤", "%s", $pattern);

        $pattern = str_replace("#,##0.00", "%v", $pattern);

        return [
            'symbol'  => core()->currencySymbol(core()->getCurrentCurrencyCode()),
            'decimal' => $formater->getSymbol(\NumberFormatter::DECIMAL_SEPARATOR_SYMBOL),
            'format'  => $pattern,
        ];
    }

    /**
     * Format price with base currency symbol
     *
     * @param float $price
     *
     * @return string
     */
    public function formatBasePrice($price)
    {
        if (is_null($price)) {
            $price = 0;
        }

        $formater = new \NumberFormatter(app()->getLocale(), \NumberFormatter::CURRENCY);

        if ($symbol = $this->getBaseCurrency()->symbol) {
            if ($this->currencySymbol($this->getBaseCurrencyCode()) == $symbol) {
                return $formater->formatCurrency($price, $this->getBaseCurrencyCode());
            } else {
                $formater->setSymbol(\NumberFormatter::CURRENCY_SYMBOL, $symbol);

                return $formater->format($this->convertPrice($price));
            }
        } else {
            return $formater->formatCurrency($price, $this->getBaseCurrencyCode());
        }
    }

    /**
     * Checks if current date of the given channel (in the channel timezone) is within the range
     *
     * @param int|string|\Webkul\Core\Contracts\Channel $channel
     * @param string|null                               $dateFrom
     * @param string|null                               $dateTo
     *
     * @return bool
     */
    public function isChannelDateInInterval($dateFrom = null, $dateTo = null)
    {
        $channel = $this->getCurrentChannel();

        $channelTimeStamp = $this->channelTimeStamp($channel);

        $fromTimeStamp = strtotime($dateFrom);

        $toTimeStamp = strtotime($dateTo);

        if ($dateTo) {
            $toTimeStamp += 86400;
        }

        if (! $this->is_empty_date($dateFrom) && $channelTimeStamp < $fromTimeStamp) {
            $result = false;
        } elseif (! $this->is_empty_date($dateTo) && $channelTimeStamp > $toTimeStamp) {
            $result = false;
        } else {
            $result = true;
        }

        return $result;
    }

    /**
     * Get channel timestamp, timstamp will be builded with channel timezone settings
     *
     * @param \Webkul\Core\Contracts\Channel $channel
     *
     * @return  int
     */
    public function channelTimeStamp($channel)
    {
        $timezone = $channel->timezone;

        $currentTimezone = @date_default_timezone_get();

        @date_default_timezone_set($timezone);

        $date = date('Y-m-d H:i:s');

        @date_default_timezone_set($currentTimezone);

        return strtotime($date);
    }

    /**
     * Check whether sql date is empty
     *
     * @param string $date
     *
     * @return bool
     */
    function is_empty_date($date)
    {
        return preg_replace('#[ 0:-]#', '', $date) === '';
    }

    /**
     * Format date using current channel.
     *
     * @param \Illuminate\Support\Carbon|null $date
     * @param string                          $format
     *
     * @return  string
     */
    public function formatDate($date = null, $format = 'd-m-Y H:i:s')
    {
        $channel = $this->getCurrentChannel();

        if (is_null($date)) {
            $date = Carbon::now();
        }

        $date->setTimezone($channel->timezone);

        return $date->format($format);
    }

    /**
     * Retrieve information from payment configuration
     *
     * @param string          $field
     * @param int|string|null $channelId
     * @param string|null     $locale
     *
     * @return mixed
     */
    public function getConfigData($field, $channel = null, $locale = null)
    {
        if (null === $channel) {
            $channel = request()->get('channel') ?: ($this->getCurrentChannelCode() ?: $this->getDefaultChannelCode());
        }

        if (null === $locale) {
            $locale = request()->get('locale') ?: app()->getLocale();
        }

        $fields = $this->getConfigField($field);

        $channel_based = false;
        $locale_based = false;

        if (isset($fields['channel_based']) && $fields['channel_based']) {
            $channel_based = true;
        }

        if (isset($fields['locale_based']) && $fields['locale_based']) {
            $locale_based = true;
        }

        if (isset($fields['channel_based']) && $fields['channel_based']) {
            if (isset($fields['locale_based']) && $fields['locale_based']) {
                $coreConfigValue = $this->coreConfigRepository->findOneWhere([
                    'code'         => $field,
                    'channel_code' => $channel,
                    'locale_code'  => $locale,
                ]);
            } else {
                $coreConfigValue = $this->coreConfigRepository->findOneWhere([
                    'code'         => $field,
                    'channel_code' => $channel,
                ]);
            }
        } else {
            if (isset($fields['locale_based']) && $fields['locale_based']) {
                $coreConfigValue = $this->coreConfigRepository->findOneWhere([
                    'code'        => $field,
                    'locale_code' => $locale,
                ]);
            } else {
                $coreConfigValue = $this->coreConfigRepository->findOneWhere([
                    'code' => $field,
                ]);
            }
        }

        if (! $coreConfigValue) {
            $fields = explode(".", $field);

            array_shift($fields);

            $field = implode(".", $fields);

            return Config::get($field);
        }

        return $coreConfigValue->value;
    }

    /**
     * Retrieve a group of information from the core config table
     *
     * @param mixed $criteria
     *
     * @return mixed
     */
    public function retrieveGroupConfig($criteria)
    {
        return $criteria;
    }

    /**
     * Retrieve all countries
     *
     * @return \Illuminate\Support\Collection
     */
    public function countries()
    {
        return $this->countryRepository->all();
    }

    /**
     * Returns country name by code
     *
     * @param string $code
     *
     * @return string
     */
    public function country_name($code)
    {
        $country = $this->countryRepository->findOneByField('code', $code);

        return $country ? $country->name : '';
    }

    /**
     * Retrieve all country states
     *
     * @param string $countryCode
     *
     * @return \Illuminate\Support\Collection
     */
    public function states($countryCode)
    {
        return $this->countryStateRepository->findByField('country_code', $countryCode);
    }

    /**
     * Retrieve all grouped states by country code
     *
     * @return \Illuminate\Support\Collection
     */
    public function groupedStatesByCountries()
    {
        $collection = [];

        foreach ($this->countryStateRepository->all() as $state) {
            $collection[$state->country_code][] = $state->toArray();
        }

        return $collection;
    }

    /**
     * Retrieve all grouped states by country code
     *
     * @return \Illuminate\Support\Collection
     */
    public function findStateByCountryCode($countryCode = null, $stateCode = null)
    {
        $collection = array();

        $collection = $this->countryStateRepository->findByField(['country_code' => $countryCode, 'code' => $stateCode]);

        if (count($collection)) {
            return $collection->first();
        } else {
            return false;
        }
    }

    /**
     * Returns time intervals
     *
     * @param \Illuminate\Support\Carbon $startDate
     * @param \Illuminate\Support\Carbon $endDate
     *
     * @return array
     */
    public function getTimeInterval($startDate, $endDate)
    {
        $timeIntervals = [];

        $totalDays = $startDate->diffInDays($endDate) + 1;
        $totalMonths = $startDate->diffInMonths($endDate) + 1;

        $startWeekDay = Carbon::createFromTimeString($this->xWeekRange($startDate, 0) . ' 00:00:01');
        $endWeekDay = Carbon::createFromTimeString($this->xWeekRange($endDate, 1) . ' 23:59:59');
        $totalWeeks = $startWeekDay->diffInWeeks($endWeekDay);

        if ($totalMonths > 5) {
            for ($i = 0; $i < $totalMonths; $i++) {
                $date = clone $startDate;
                $date->addMonths($i);

                $start = Carbon::createFromTimeString($date->format('Y-m-d') . ' 00:00:01');
                $end = $totalMonths - 1 == $i
                    ? $endDate
                    : Carbon::createFromTimeString($date->format('Y-m-d') . ' 23:59:59');

                $timeIntervals[] = ['start' => $start, 'end' => $end, 'formatedDate' => $date->format('M')];
            }
        } elseif ($totalWeeks > 6) {
            for ($i = 0; $i < $totalWeeks; $i++) {
                $date = clone $startDate;
                $date->addWeeks($i);

                $start = $i == 0
                    ? $startDate
                    : Carbon::createFromTimeString($this->xWeekRange($date, 0) . ' 00:00:01');
                $end = $totalWeeks - 1 == $i
                    ? $endDate
                    : Carbon::createFromTimeString($this->xWeekRange($date, 1) . ' 23:59:59');

                $timeIntervals[] = ['start' => $start, 'end' => $end, 'formatedDate' => $date->format('d M')];
            }
        } else {
            for ($i = 0; $i < $totalDays; $i++) {
                $date = clone $startDate;
                $date->addDays($i);

                $start = Carbon::createFromTimeString($date->format('Y-m-d') . ' 00:00:01');
                $end = Carbon::createFromTimeString($date->format('Y-m-d') . ' 23:59:59');

                $timeIntervals[] = ['start' => $start, 'end' => $end, 'formatedDate' => $date->format('d M')];
            }
        }

        return $timeIntervals;
    }

    /**
     *
     * @param string $date
     * @param int    $day
     *
     * @return string
     */
    public function xWeekRange($date, $day)
    {
        $ts = strtotime($date);

        if (! $day) {
            $start = (date('D', $ts) == 'Sun') ? $ts : strtotime('last sunday', $ts);

            return date('Y-m-d', $start);
        } else {
            $end = (date('D', $ts) == 'Sat') ? $ts : strtotime('next saturday', $ts);

            return date('Y-m-d', $end);
        }
    }

    /**
     * Method to sort through the acl items and put them in order
     *
     * @param array $items
     *
     * @return array
     */
    public function sortItems($items)
    {
        foreach ($items as &$item) {
            if (count($item['children'])) {
                $item['children'] = $this->sortItems($item['children']);
            }
        }

        usort($items, function ($a, $b) {
            if ($a['sort'] == $b['sort']) {
                return 0;
            }

            return ($a['sort'] < $b['sort']) ? -1 : 1;
        });

        return $this->convertToAssociativeArray($items);
    }

    /**
     * @param string $fieldName
     *
     * @return array
     */
    public function getConfigField($fieldName)
    {
        foreach (config('core') as $coreData) {
            if (isset($coreData['fields'])) {
                foreach ($coreData['fields'] as $field) {
                    $name = $coreData['key'] . '.' . $field['name'];

                    if ($name == $fieldName) {
                        return $field;
                    }
                }
            }
        }
    }

    /**
     * @param array $items
     *
     * @return array
     */
    public function convertToAssociativeArray($items)
    {
        foreach ($items as $key1 => $level1) {
            unset($items[$key1]);
            $items[$level1['key']] = $level1;

            if (count($level1['children'])) {
                foreach ($level1['children'] as $key2 => $level2) {
                    $temp2 = explode('.', $level2['key']);
                    $finalKey2 = end($temp2);
                    unset($items[$level1['key']]['children'][$key2]);
                    $items[$level1['key']]['children'][$finalKey2] = $level2;

                    if (count($level2['children'])) {
                        foreach ($level2['children'] as $key3 => $level3) {
                            $temp3 = explode('.', $level3['key']);
                            $finalKey3 = end($temp3);
                            unset($items[$level1['key']]['children'][$finalKey2]['children'][$key3]);
                            $items[$level1['key']]['children'][$finalKey2]['children'][$finalKey3] = $level3;
                        }
                    }

                }
            }
        }

        return $items;
    }

    /**
     * @param array            $items
     * @param string           $key
     * @param string|int|float $value
     *
     * @return array
     */
    public function array_set(&$array, $key, $value)
    {
        if (is_null($key)) {
            return $array = $value;
        }

        $keys = explode('.', $key);
        $count = count($keys);

        while (count($keys) > 1) {
            $key = array_shift($keys);

            if (! isset($array[$key]) || ! is_array($array[$key])) {
                $array[$key] = [];
            }

            $array = &$array[$key];
        }

        $finalKey = array_shift($keys);

        if (isset($array[$finalKey])) {
            $array[$finalKey] = $this->arrayMerge($array[$finalKey], $value);
        } else {
            $array[$finalKey] = $value;
        }

        return $array;
    }

    /**
     * @param array $array1
     * @param array $array2
     *
     * @return array
     */
    protected function arrayMerge(array &$array1, array &$array2)
    {
        $merged = $array1;

        foreach ($array2 as $key => &$value) {
            if (is_array($value) && isset($merged[$key]) && is_array($merged[$key])) {
                $merged[$key] = $this->arrayMerge($merged[$key], $value);
            } else {
                $merged[$key] = $value;
            }
        }

        return $merged;
    }

    /**
     * @param array $array1
     *
     * @return array
     */
    public function convertEmptyStringsToNull($array)
    {
        foreach ($array as $key => $value) {
            if ($value == "" || $value == "null") {
                $array[$key] = null;
            }
        }

        return $array;
    }

    /**
     * Create singletom object through single facade
     *
     * @param string $className
     *
     * @return object
     */
    public function getSingletonInstance($className)
    {
        static $instance = [];

        if (array_key_exists($className, $instance)) {
            return $instance[$className];
        }

        return $instance[$className] = app($className);
    }

    /**
     * Returns a string as selector part for identifying elements in views
     *
     * @param float $taxRate
     *
     * @return string
     */
    public static function taxRateAsIdentifier(float $taxRate): string
    {
        return str_replace('.', '_', (string)$taxRate);
    }

    /**
     * Get Shop email sender details
     *
     * @return array
     */
    public function getSenderEmailDetails()
    {
        $sender_name = core()->getConfigData('general.general.email_settings.sender_name') ? core()->getConfigData('general.general.email_settings.sender_name') : config('mail.from.name');

        $sender_email = core()->getConfigData('general.general.email_settings.shop_email_from') ? core()->getConfigData('general.general.email_settings.shop_email_from') : config('mail.from.address');

        return [
            'name'  => $sender_name,
            'email' => $sender_email,
        ];
    }

    /**
     * Get Admin email details
     *
     * @return array
     */
    public function getAdminEmailDetails()
    {
        $admin_name = core()->getConfigData('general.general.email_settings.admin_name') ? core()->getConfigData('general.general.email_settings.admin_name') : config('mail.admin.name');

        $admin_email = core()->getConfigData('general.general.email_settings.admin_email') ? core()->getConfigData('general.general.email_settings.admin_email') : config('mail.admin.address');

        return [
            'name'  => $admin_name,
            'email' => $admin_email,
        ];
    }
}