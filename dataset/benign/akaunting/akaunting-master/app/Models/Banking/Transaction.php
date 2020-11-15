<?php

namespace App\Models\Banking;

use App\Abstracts\Model;
use App\Models\Setting\Category;
use App\Models\Setting\Currency;
use App\Traits\Currencies;
use App\Traits\DateTime;
use App\Traits\Media;
use App\Traits\Recurring;
use Bkwld\Cloner\Cloneable;

class Transaction extends Model
{
    use Cloneable, Currencies, DateTime, Media, Recurring;

    protected $table = 'transactions';

    protected $dates = ['deleted_at', 'paid_at'];

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['company_id', 'type', 'account_id', 'paid_at', 'amount', 'currency_code', 'currency_rate', 'document_id', 'contact_id', 'description', 'category_id', 'payment_method', 'reference', 'parent_id'];

    /**
     * Sortable columns.
     *
     * @var array
     */
    public $sortable = ['paid_at', 'amount','category.name', 'account.name'];

    /**
     * Clonable relationships.
     *
     * @var array
     */
    public $cloneable_relations = ['recurring'];

    public function account()
    {
        return $this->belongsTo('App\Models\Banking\Account')->withDefault(['name' => trans('general.na')]);
    }

    public function bill()
    {
        return $this->belongsTo('App\Models\Purchase\Bill', 'document_id');
    }

    public function category()
    {
        return $this->belongsTo('App\Models\Setting\Category')->withDefault(['name' => trans('general.na')]);
    }

    public function contact()
    {
        return $this->belongsTo('App\Models\Common\Contact')->withDefault(['name' => trans('general.na')]);
    }

    public function currency()
    {
        return $this->belongsTo('App\Models\Setting\Currency', 'currency_code', 'code');
    }

    public function invoice()
    {
        return $this->belongsTo('App\Models\Sale\Invoice', 'document_id');
    }

    public function recurring()
    {
        return $this->morphOne('App\Models\Common\Recurring', 'recurable');
    }

    public function user()
    {
        return $this->belongsTo('App\Models\Auth\User', 'contact_id', 'id');
    }

    /**
     * Scope to only include contacts of a given type.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param mixed $types
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeType($query, $types)
    {
        if (empty($types)) {
            return $query;
        }

        return $query->whereIn($this->table . '.type', (array) $types);
    }

    /**
     * Scope to include only income.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeIncome($query)
    {
        return $query->where($this->table . '.type', '=', 'income');
    }

    /**
     * Scope to include only expense.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeExpense($query)
    {
        return $query->where($this->table . '.type', '=', 'expense');
    }

    /**
     * Get only transfers.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeIsTransfer($query)
    {
        return $query->where('category_id', '=', Category::transfer());
    }

    /**
     * Skip transfers.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeIsNotTransfer($query)
    {
        return $query->where('category_id', '<>', Category::transfer());
    }

    /**
     * Get only documents (invoice/bill).
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeIsDocument($query)
    {
        return $query->whereNotNull('document_id');
    }

    /**
     * Get only transactions (revenue/payment).
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeIsNotDocument($query)
    {
        return $query->whereNull('document_id');
    }

    /**
     * Get by document (invoice/bill).
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param  integer $document_id
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDocument($query, $document_id)
    {
        return $query->where('document_id', '=', $document_id);
    }

    /**
     * Order by paid date.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('paid_at', 'desc');
    }

    /**
     * Scope paid invoice.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePaid($query)
    {
        return $query->sum('amount');
    }

    /**
     * Get only reconciled.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeIsReconciled($query)
    {
        return $query->where('reconciled', 1);
    }

    /**
     * Get only not reconciled.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeIsNotReconciled($query)
    {
        return $query->where('reconciled', 0);
    }

    public function onCloning($src, $child = null)
    {
        $this->document_id = null;
    }

    /**
     * Convert amount to double.
     *
     * @param  string  $value
     * @return void
     */
    public function setAmountAttribute($value)
    {
        $this->attributes['amount'] = (double) $value;
    }

    /**
     * Convert currency rate to double.
     *
     * @param  string  $value
     * @return void
     */
    public function setCurrencyRateAttribute($value)
    {
        $this->attributes['currency_rate'] = (double) $value;
    }

    /**
     * Convert amount to double.
     *
     * @return float
     */
    public function getPriceAttribute()
    {
        static $currencies;

        $amount = $this->amount;

        // Convert amount if not same currency
        if ($this->account->currency_code != $this->currency_code) {
            if (empty($currencies)) {
                $currencies = Currency::enabled()->pluck('rate', 'code')->toArray();
            }

            $default_currency = setting('default.currency', 'USD');

            $default_amount = $this->amount;

            if ($default_currency != $this->currency_code) {
                $default_amount_model = new Transaction();

                $default_amount_model->default_currency_code = $default_currency;
                $default_amount_model->amount = $this->amount;
                $default_amount_model->currency_code = $this->currency_code;
                $default_amount_model->currency_rate = $this->currency_rate;

                $default_amount = $default_amount_model->getAmountConvertedToDefault();
            }

            $transfer_amount = new Transaction();

            $transfer_amount->default_currency_code = $this->currency_code;
            $transfer_amount->amount = $default_amount;
            $transfer_amount->currency_code = $this->account->currency_code;
            $transfer_amount->currency_rate = $currencies[$this->account->currency_code];

            $amount = $transfer_amount->getAmountConvertedFromDefault();
        }

        return $amount;
    }

    /**
     * Get the current balance.
     *
     * @return string
     */
    public function getAttachmentAttribute($value)
    {
        if (!empty($value) && !$this->hasMedia('attachment')) {
            return $value;
        } elseif (!$this->hasMedia('attachment')) {
            return false;
        }

        return $this->getMedia('attachment')->last();
    }
}
