<?php

namespace App\Models\Contact;

use App\Models\Account\Place;
use App\Models\Account\Account;
use App\Interfaces\LabelInterface;
use App\Models\ModelBindingWithContact as Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * An Address is where the contact lives (or lived).
 * The actual address (street name etc…) is represented with a Place object.
 */
class Address extends Model implements LabelInterface
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * All of the relationships to be touched.
     *
     * @var array
     */
    protected $touches = ['contact'];

    protected $table = 'addresses';

    /**
     * Get the account record associated with the address.
     *
     * @return BelongsTo
     */
    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Get the contact record associated with the address.
     *
     * @return BelongsTo
     */
    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    /**
     * Get the place record associated with the address.
     *
     * @return BelongsTo
     */
    public function place()
    {
        return $this->belongsTo(Place::class);
    }

    /**
     * Get the label associated with the contact.
     *
     * @return BelongsToMany
     */
    public function labels()
    {
        return $this->belongsToMany(ContactFieldLabel::class);
    }
}
