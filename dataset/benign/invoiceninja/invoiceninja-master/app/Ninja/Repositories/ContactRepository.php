<?php

namespace App\Ninja\Repositories;

use App\Models\Contact;

class ContactRepository extends BaseRepository
{
    public function all()
    {
        return Contact::scope()
                ->withTrashed()
                ->get();
    }

    public function save($data, $contact = false)
    {
        $publicId = isset($data['public_id']) ? $data['public_id'] : false;

        if ($contact) {
            // do nothing
        } elseif (! $publicId || intval($publicId) < 0) {
            $contact = Contact::createNew();
            $contact->send_invoice = true;
            $contact->client_id = $data['client_id'];
            $contact->is_primary = Contact::scope()->where('client_id', '=', $contact->client_id)->count() == 0;
            $contact->contact_key = strtolower(str_random(RANDOM_KEY_LENGTH));
        } else {
            $contact = Contact::scope($publicId)->firstOrFail();
        }

        $contact->fill($data);
        $contact->save();

        return $contact;
    }
}
